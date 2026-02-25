<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use App\Models\ProjectFile;
use App\Models\ProjectComment;
use App\Enums\ProjectStatus;
use App\Enums\UserRole;
use App\Services\ProjectService;
use App\Http\Requests\ProjectRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ProjectController extends Controller
{
    protected $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index()
    {
        $user = Auth::user();
        $companyId = $user->company_id;

        $projects = Project::where('company_id', $companyId)
            ->with(['creator', 'participants'])
            ->when(!$user->canManageProjects(), function ($query) use ($user) {
                return $query->whereHas('participants', function ($q) use ($user) {
                    $q->where('user_id', $user->id);
                });
            })
            ->latest()
            ->paginate(15);

        return view('projects.index', compact('projects'));
    }

    public function create()
    {
        $this->authorize('create', Project::class);

        $users = User::where('company_id', Auth::user()->company_id)->get();

        return view('projects.create', compact('users'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Project::class);

        try {
            // Проверка загрузки файлов
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $key => $file) {
                    if (!$file->isValid()) {
                        $error = $file->getError();
                        $errorMessage = match($error) {
                            UPLOAD_ERR_INI_SIZE => 'Файл превышает максимальный размер в php.ini',
                            UPLOAD_ERR_FORM_SIZE => 'Файл превышает максимальный размер в форме',
                            UPLOAD_ERR_PARTIAL => 'Файл был загружен только частично',
                            UPLOAD_ERR_NO_FILE => 'Файл не был загружен',
                            UPLOAD_ERR_NO_TMP_DIR => 'Отсутствует временная папка',
                            UPLOAD_ERR_CANT_WRITE => 'Не удалось записать файл на диск',
                            UPLOAD_ERR_EXTENSION => 'PHP расширение остановило загрузку файла',
                            default => 'Неизвестная ошибка загрузки'
                        };
                        
                        return back()
                            ->withInput()
                            ->with('error', "Ошибка загрузки файла: $errorMessage");
                    }
                    
                    // Проверка размера (20MB)
                    if ($file->getSize() > 20 * 1024 * 1024) {
                        return back()
                            ->withInput()
                            ->with('error', 'Файл слишком большой. Максимальный размер 20MB');
                    }
                }
            }

            // Валидация с сообщениями на русском
            $validator = validator($request->all(), [
                'name' => 'required|string|max:255',
                'description' => 'nullable|string',
                'participants' => 'nullable|array',
                'participants.*.user_id' => 'required_with:participants|exists:users,id',
                'files.*' => 'nullable|file|max:20480', // 20MB
            ], [
                'name.required' => 'Название проекта обязательно',
                'files.*.max' => 'Файл слишком большой (макс. 20MB)',
                'files.*.uploaded' => 'Ошибка загрузки файла',
            ]);

            if ($validator->fails()) {
                return back()
                    ->withErrors($validator)
                    ->withInput();
            }

            // Создание проекта
            $project = Project::create([
                'company_id' => Auth::user()->company_id,
                'name' => $request->name,
                'description' => $request->description,
                'status' => ProjectStatus::CREATED->value,
                'created_by' => Auth::id(),
            ]);

            // Добавляем создателя как участника
            $this->projectService->addParticipant($project, Auth::user(), Auth::user()->role);

            // Автоматически добавляем ключевых сотрудников компании (директор, замдиректора, ПТО, снабжение)
            $companyUsers = User::where('company_id', Auth::user()->company_id)->get();
            
            foreach ($companyUsers as $user) {
                // Пропускаем создателя (он уже добавлен)
                if ($user->id === Auth::id()) {
                    continue;
                }
                
                // Добавляем по ролям
                if (in_array($user->role, ['director', 'deputy_director', 'pto', 'supply'])) {
                    if (!$this->isUserInProject($project, $user->id)) {
                        $this->projectService->addParticipant($project, $user, $user->role);
                    }
                }
            }

            // Добавляем дополнительных участников из формы
            if ($request->has('participants') && is_array($request->participants)) {
                foreach ($request->participants as $participantData) {
                    if (isset($participantData['user_id']) && !empty($participantData['user_id'])) {
                        $user = User::find($participantData['user_id']);
                        if ($user && !$this->isUserInProject($project, $user->id)) {
                            // Используем роль пользователя из БД, а не из формы
                            $this->projectService->addParticipant($project, $user, $user->role);
                        }
                    }
                }
            }

            // Загрузка файлов
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    if ($file->isValid()) {
                        try {
                            $originalName = $file->getClientOriginalName();
                            $fileName = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
                            
                            $path = $file->storeAs('projects/' . $project->id, $fileName, 'public');
                            
                            ProjectFile::create([
                                'company_id' => $project->company_id,
                                'project_id' => $project->id,
                                'user_id' => Auth::id(),
                                'file_path' => $path,
                                'file_name' => $originalName,
                                'file_type' => $file->getMimeType(),
                                'file_size' => $file->getSize(),
                                'section' => 'general',
                            ]);
                            
                        } catch (\Exception $e) {
                            Log::error('Ошибка при сохранении файла', [
                                'error' => $e->getMessage(),
                                'file' => $file->getClientOriginalName()
                            ]);
                        }
                    }
                }
            }

            return redirect()->route('projects.show', $project)
                ->with('success', 'Проект успешно создан');

        } catch (\Exception $e) {
            Log::error('Ошибка при создании проекта', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return back()
                ->withInput()
                ->with('error', 'Ошибка при создании проекта: ' . $e->getMessage());
        }
    }

    /**
     * Проверка, есть ли пользователь уже в проекте
     */
    private function isUserInProject(Project $project, $userId): bool
    {
        return $project->participants()->where('user_id', $userId)->exists();
    }

    public function show(Project $project)
    {
        $this->authorize('view', $project);

        $project->load([
            'creator',
            'participants',
            'files' => fn($q) => $q->latest(),
            'comments' => fn($q) => $q->latest()->with('user'),
            'statusLogs' => fn($q) => $q->latest()->with('user'),
            'tasks' => fn($q) => $q->latest()->with(['assignee', 'creator']),
            'materialDeliveries' => fn($q) => $q->latest()->with(['supplyUser', 'siteManagerUser']),
        ]);

        $availableUsers = User::where('company_id', $project->company_id)
            ->whereNotIn('id', $project->participants->pluck('id'))
            ->get();

        return view('projects.show', compact('project', 'availableUsers'));
    }

    public function edit(Project $project)
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(Request $request, Project $project)
    {
        $this->authorize('update', $project);

        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $project->update($request->only(['name', 'description']));

        return redirect()->route('projects.show', $project)
            ->with('success', 'Проект обновлен');
    }

    public function addParticipant(Request $request, Project $project)
    {
        $this->authorize('manageParticipants', $project);

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'role' => 'required|string|in:' . implode(',', UserRole::values()),
        ]);

        $user = User::find($request->user_id);
        
        // Проверяем, не добавлен ли пользователь уже
        if ($this->isUserInProject($project, $user->id)) {
            return back()->with('error', 'Этот пользователь уже является участником проекта');
        }

        try {
            $this->projectService->addParticipant($project, $user, $request->role);
            return back()->with('success', 'Участник добавлен');
        } catch (\Exception $e) {
            Log::error('Ошибка при добавлении участника', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $request->user_id
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function removeParticipant(Project $project, User $user)
    {
        $this->authorize('manageParticipants', $project);

        try {
            $this->projectService->removeParticipant($project, $user);
            return back()->with('success', 'Участник удален');
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении участника', [
                'error' => $e->getMessage(),
                'project_id' => $project->id,
                'user_id' => $user->id
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function addComment(Request $request, Project $project)
    {
        $request->validate([
            'content' => 'required|string',
            'section' => 'nullable|string|in:general,pto,supply',
        ]);

        ProjectComment::create([
            'company_id' => $project->company_id,
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'content' => $request->content,
            'section' => $request->section ?? 'general',
        ]);

        return back()->with('success', 'Комментарий добавлен');
    }

    public function uploadFile(Request $request, Project $project)
    {
        $request->validate([
            'files.*' => 'required|file|max:20480', // 20MB каждый
            'section' => 'nullable|string|in:general,pto,supply',
        ]);

        $uploadedCount = 0;
        $section = $request->section ?? 'general';

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                if ($file->isValid()) {
                    try {
                        $originalName = $file->getClientOriginalName();
                        $fileName = time() . '_' . uniqid() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', $originalName);
                        
                        // Определяем подпапку в зависимости от отдела
                        $path = $file->storeAs('projects/' . $project->id . '/' . $section, $fileName, 'public');

                        ProjectFile::create([
                            'company_id' => $project->company_id,
                            'project_id' => $project->id,
                            'user_id' => Auth::id(),
                            'file_path' => $path,
                            'file_name' => $originalName,
                            'file_type' => $file->getMimeType(),
                            'file_size' => $file->getSize(),
                            'section' => $section,
                        ]);
                        
                        $uploadedCount++;
                        
                    } catch (\Exception $e) {
                        Log::error('Ошибка при сохранении файла', [
                            'error' => $e->getMessage(),
                            'file' => $file->getClientOriginalName()
                        ]);
                    }
                }
            }
        }

        if ($uploadedCount > 0) {
            $message = $uploadedCount === 1 
                ? 'Файл успешно загружен' 
                : "Загружено {$uploadedCount} файлов";
                
            return back()->with('success', $message);
        }

        return back()->with('error', 'Не удалось загрузить файлы');
    }

    public function sendToCalculation(Project $project)
    {
        $this->authorize('update', $project);

        $project = $this->projectService->changeStatus(
            $project, 
            ProjectStatus::IN_CALCULATION->value,
            'Проект отправлен в расчет'
        );

        return back()->with('success', 'Проект отправлен в расчет');
    }

    public function sendToApproval(Project $project)
    {
        $this->authorize('update', $project);

        try {
            $project = $this->projectService->sendToApproval($project);
            return back()->with('success', 'Проект отправлен на согласование');
        } catch (\Exception $e) {
            Log::error('Ошибка при отправке на согласование', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function approve(Request $request, Project $project)
    {
        $this->authorize('approve', $project);

        $request->validate([
            'comment' => 'required|string',
        ]);

        try {
            $project = $this->projectService->approve($project, $request->comment);
            return back()->with('success', 'Проект утвержден');
        } catch (\Exception $e) {
            Log::error('Ошибка при утверждении проекта', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function reject(Request $request, Project $project)
    {
        $this->authorize('approve', $project);

        $request->validate([
            'comment' => 'required|string',
        ]);

        try {
            $project = $this->projectService->reject($project, $request->comment);
            return back()->with('success', 'Проект отклонен');
        } catch (\Exception $e) {
            Log::error('Ошибка при отклонении проекта', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);
            return back()->with('error', $e->getMessage());
        }
    }

    public function startImplementation(Project $project)
    {
        $this->authorize('update', $project);

        try {
            $project = $this->projectService->startImplementation($project);
            return back()->with('success', 'Проект переведен в реализацию');
        } catch (\Exception $e) {
            Log::error('Ошибка при переводе в реализацию', [
                'error' => $e->getMessage(),
                'project_id' => $project->id
            ]);
            return back()->with('error', $e->getMessage());
        }
    }
    
        /**
     * Удаление файла
     */
    public function deleteFile(Project $project, ProjectFile $file)
    {
        try {
            $user = Auth::user();
            
            // Проверяем что файл принадлежит этому проекту
            if ($file->project_id !== $project->id) {
                abort(404, 'Файл не найден в этом проекте');
            }
            
            // Проверяем права на удаление:
            // 1. Директор может удалять любые файлы
            // 2. ПТО может удалять свои файлы если проект в расчете или на доработке
            // 3. Снабжение может удалять свои файлы если проект в расчете или на доработке
            
            $isDirector = in_array($user->role, ['director', 'deputy_director']);
            $isOwner = $file->user_id === $user->id;
            
            // Для ПТО/Снабжения проверяем статус проекта
            $canDeleteForWorker = false;
            if (!$isDirector && $isOwner) {
                // Проверяем статус проекта
                if (in_array($project->status, ['in_calculation', 'on_revision'])) {
                    // Проверяем что файл из их отдела
                    $userSection = $user->role === 'pto' ? 'pto' : 'supply';
                    if ($file->section === $userSection) {
                        // Проверяем что отдел не утвержден
                        $isApproved = $user->role === 'pto' ? $project->pto_approved === true : $project->supply_approved === true;
                        if (!$isApproved) {
                            $canDeleteForWorker = true;
                        }
                    }
                }
            }
            
            if (!$isDirector && !$canDeleteForWorker) {
                Log::warning('Попытка несанкционированного удаления файла', [
                    'user_id' => $user->id,
                    'user_role' => $user->role,
                    'file_id' => $file->id,
                    'project_status' => $project->status,
                    'is_owner' => $isOwner
                ]);
                
                if (request()->expectsJson()) {
                    return response()->json(['error' => 'У вас нет прав на удаление этого файла'], 403);
                }
                return back()->with('error', 'У вас нет прав на удаление этого файла');
            }
            
            // Удаляем файл из хранилища
            if (Storage::disk('public')->exists($file->file_path)) {
                Storage::disk('public')->delete($file->file_path);
            }
            
            // Удаляем запись из БД
            $file->delete();
            
            Log::info('Файл удален', [
                'user_id' => $user->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);
            
            if (request()->expectsJson()) {
                return response()->json(['success' => true]);
            }
            
            return redirect()->back()->with('success', 'Файл успешно удален');
            
        } catch (\Exception $e) {
            Log::error('Ошибка при удалении файла', [
                'error' => $e->getMessage(),
                'file_id' => $file->id ?? null
            ]);
            
            if (request()->expectsJson()) {
                return response()->json(['error' => 'Ошибка при удалении файла'], 500);
            }
            
            return redirect()->back()->with('error', 'Ошибка при удалении файла');
        }
    }

        /**
     * Отправка расчетов ПТО на проверку
     */
    public function submitPTO(Request $request, Project $project)
    {
        // Проверяем что пользователь является ПТО и участвует в проекте
        $user = Auth::user();
        
        // Проверяем что пользователь имеет роль PTO
        if ($user->role !== 'pto') {
            abort(403, 'Только ПТО может отправлять расчеты');
        }
        
        // Проверяем что пользователь является участником проекта
        $isParticipant = $project->participants()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'pto')
            ->exists();
            
        if (!$isParticipant) {
            abort(403, 'Вы не являетесь участником проекта в роли ПТО');
        }
        
        // Проверяем статус проекта (можно отправлять только в расчете или на доработке)
        if (!in_array($project->status, ['in_calculation', 'on_revision'])) {
            return back()->with('error', 'Сейчас нельзя отправить расчеты');
        }
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        $project->update([
            'pto_submitted_at' => now(),
            'pto_comment' => $request->comment,
            'pto_approved' => null, // Сбрасываем статус утверждения/отклонения
        ]);
        
        Log::info('ПТО отправил расчеты', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'comment' => $request->comment
        ]);
        
        return redirect()->back()->with('success', 'Расчеты отправлены на проверку');
    }

    /**
     * Отправка расчетов Снабжения на проверку
     */
    public function submitSupply(Request $request, Project $project)
    {
        // Проверяем что пользователь является Снабжением и участвует в проекте
        $user = Auth::user();
        
        // Проверяем что пользователь имеет роль supply
        if ($user->role !== 'supply') {
            abort(403, 'Только снабжение может отправлять расчеты');
        }
        
        // Проверяем что пользователь является участником проекта
        $isParticipant = $project->participants()
            ->where('user_id', $user->id)
            ->wherePivot('role', 'supply')
            ->exists();
            
        if (!$isParticipant) {
            abort(403, 'Вы не являетесь участником проекта в роли снабжения');
        }
        
        // Проверяем статус проекта (можно отправлять только в расчете или на доработке)
        if (!in_array($project->status, ['in_calculation', 'on_revision'])) {
            return back()->with('error', 'Сейчас нельзя отправить расчеты');
        }
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        $project->update([
            'supply_submitted_at' => now(),
            'supply_comment' => $request->comment,
            'supply_approved' => null, // Сбрасываем статус утверждения/отклонения
        ]);
        
        Log::info('Снабжение отправило расчеты', [
            'project_id' => $project->id,
            'user_id' => $user->id,
            'comment' => $request->comment
        ]);
        
        return redirect()->back()->with('success', 'Расчеты отправлены на проверку');
    }

        /**
     * Утверждение ПТО директором
     */
    public function approvePTO(Project $project)
    {
        $this->authorize('manageParticipants', $project);
        
        $project->update([
            'pto_approved' => true,
        ]);
        
        Log::info('Директор утвердил ПТО', ['project_id' => $project->id]);
        
        return redirect()->back()->with('success', 'Расчеты ПТО утверждены');
    }

    /**
     * Утверждение Снабжения директором
     */
    public function approveSupply(Project $project)
    {
        $this->authorize('manageParticipants', $project);
        
        $project->update([
            'supply_approved' => true,
        ]);
        
        Log::info('Директор утвердил Снабжение', ['project_id' => $project->id]);
        
        return redirect()->back()->with('success', 'Расчеты снабжения утверждены');
    }

    /**
     * Отправка ПТО на доработку директором
     */
    public function rejectPTO(Request $request, Project $project)
    {
        $this->authorize('manageParticipants', $project);
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        $project->update([
            'pto_approved' => false,
            'pto_comment' => $request->comment,
        ]);
        
        // Если другой отдел уже утвержден, оставляем статус on_revision
        // Если нет - тоже on_revision
        $project->status = 'on_revision';
        $project->save();
        
        Log::info('Директор отправил ПТО на доработку', [
            'project_id' => $project->id,
            'comment' => $request->comment
        ]);
        
        return redirect()->back()->with('success', 'ПТО отправлен на доработку');
    }

    /**
     * Отправка Снабжения на доработку директором
     */
    public function rejectSupply(Request $request, Project $project)
    {
        $this->authorize('manageParticipants', $project);
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        $project->update([
            'supply_approved' => false,
            'supply_comment' => $request->comment,
        ]);
        
        // Если другой отдел уже утвержден, оставляем статус on_revision
        // Если нет - тоже on_revision
        $project->status = 'on_revision';
        $project->save();
        
        Log::info('Директор отправил снабжение на доработку', [
            'project_id' => $project->id,
            'comment' => $request->comment
        ]);
        
        return redirect()->back()->with('success', 'Снабжение отправлено на доработку');
    }

        /**
     * Возврат проекта из реализации на доработку
     */
    public function rework(Request $request, Project $project)
    {
        $this->authorize('manageParticipants', $project);
        
        $request->validate([
            'comment' => 'required|string|max:1000',
        ]);
        
        // Меняем статус проекта
        $project->status = 'on_revision';
        
        // Сбрасываем статусы утверждения отделов
        $project->pto_approved = null;
        $project->supply_approved = null;
        $project->pto_submitted_at = null;
        $project->supply_submitted_at = null;
        $project->save();
        
        // Добавляем комментарий в историю
        $project->statusLogs()->create([
            'user_id' => Auth::id(),
            'old_status' => 'in_progress',
            'new_status' => 'on_revision',
            'comment' => 'Проект возвращен на доработку: ' . $request->comment
        ]);
        
        Log::info('Проект возвращен на доработку', [
            'project_id' => $project->id,
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);
        
        return redirect()->route('projects.show', $project)
            ->with('success', 'Проект возвращен на доработку');
    }
}