@extends('layouts.app')

@section('title', $project->name)

@section('content')
@php
    use Carbon\Carbon;

    $statusMap = [
        'created'        => ['label' => 'Создан',            'dot' => 'bg-gray-400',   'badge' => 'bg-gray-100 text-gray-700'],
        'in_calculation' => ['label' => 'В расчёте',         'dot' => 'bg-blue-500',   'badge' => 'bg-blue-50 text-blue-700'],
        'on_approval'    => ['label' => 'На согласовании',   'dot' => 'bg-amber-500',  'badge' => 'bg-amber-50 text-amber-700'],
        'on_revision'    => ['label' => 'На доработке',      'dot' => 'bg-orange-500', 'badge' => 'bg-orange-50 text-orange-700'],
        'approved'       => ['label' => 'Утверждён',         'dot' => 'bg-green-500',  'badge' => 'bg-green-50 text-green-700'],
        'in_progress'    => ['label' => 'В реализации',      'dot' => 'bg-violet-500', 'badge' => 'bg-violet-50 text-violet-700'],
        'completed'      => ['label' => 'Завершён',          'dot' => 'bg-cyan-500',   'badge' => 'bg-cyan-50 text-cyan-700'],
    ];
    $st = $statusMap[$project->status] ?? ['label' => $project->status, 'dot' => 'bg-gray-400', 'badge' => 'bg-gray-100 text-gray-700'];

    $roleLabels = ['director'=>'Директор','deputy_director'=>'Зам. директора','pto'=>'ПТО','supply'=>'Снабжение','project_manager'=>'Рук. проекта','site_manager'=>'Прораб','accountant'=>'Бухгалтер'];
    $roleBadge  = ['director'=>'bg-violet-100 text-violet-700','deputy_director'=>'bg-indigo-100 text-indigo-700','pto'=>'bg-blue-100 text-blue-700','supply'=>'bg-green-100 text-green-700','project_manager'=>'bg-amber-100 text-amber-700','site_manager'=>'bg-orange-100 text-orange-700','accountant'=>'bg-emerald-100 text-emerald-700'];
    $roleAvatar = $roleBadge;
@endphp

<div class="max-w-4xl mx-auto space-y-5">

    {{-- HEADER --}}
    <div class="flex items-start justify-between gap-4 flex-wrap">
        <div>
            <div class="flex items-center gap-2 mb-2 text-sm text-gray-400">
                <a href="{{ route('projects.index') }}" class="hover:text-gray-600 flex items-center gap-1 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M19 12H5M12 5l-7 7 7 7"/></svg>
                    Проекты
                </a>
                <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 18l6-6-6-6"/></svg>
                <span class="text-gray-500">{{ Str::limit($project->name, 40) }}</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 leading-tight">{{ $project->name }}</h1>
            @if($project->description)
                <p class="mt-1 text-sm text-gray-500">{{ $project->description }}</p>
            @endif
        </div>
        <div class="flex items-center gap-2 flex-shrink-0">
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold {{ $st['badge'] }}">
                <span class="w-1.5 h-1.5 rounded-full {{ $st['dot'] }}"></span>
                {{ $st['label'] }}
            </span>
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}"
                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-600 hover:bg-gray-50 hover:text-gray-900 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                    Редактировать
                </a>
            @endcan
        </div>
    </div>

    {{-- STATUS ACTION BANNERS --}}
    @can('update', $project)
        @if($project->status === 'created')
            <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <p class="font-bold text-gray-900 text-sm">Проект создан</p>
                    <p class="text-xs text-gray-500 mt-0.5">Отправьте в расчёт, чтобы начать подготовку документации.</p>
                </div>
                <form method="POST" action="{{ route('projects.send-to-calculation', $project) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Отправить в расчёт
                    </button>
                </form>
            </div>
        @endif
        @if($project->status === 'approved')
            <div class="bg-green-50 rounded-xl border border-green-200 p-4 flex items-center justify-between gap-4 flex-wrap">
                <div>
                    <p class="font-bold text-green-800 text-sm">Проект утверждён</p>
                    <p class="text-xs text-green-600 mt-0.5">Всё готово для начала реализации.</p>
                </div>
                <form method="POST" action="{{ route('projects.start-implementation', $project) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-colors">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Начать реализацию
                    </button>
                </form>
            </div>
        @endif
    @endcan

    {{-- APPROVAL PROGRESS (director) --}}
    @can('manageParticipants', $project)
        @php
            $ptoReady      = !is_null($project->pto_submitted_at);
            $supplyReady   = !is_null($project->supply_submitted_at);
            $ptoApproved   = $project->pto_approved === true;
            $supplyApproved= $project->supply_approved === true;
            $ptoRejected   = $project->pto_approved === false;
            $supplyRejected= $project->supply_approved === false;
            $ptoFiles      = $project->files->where('section','pto')->count();
            $supplyFiles   = $project->files->where('section','supply')->count();
            $bothApproved  = $ptoApproved && $supplyApproved;

            $ds = function($approved, $rejected, $ready) {
                if ($approved) return ['border-green-200 bg-green-50', 'bg-green-100 border-b border-green-200', 'bg-green-100 text-green-700', 'Утверждён'];
                if ($rejected) return ['border-red-200 bg-red-50',     'bg-red-100 border-b border-red-200',   'bg-red-100 text-red-700',   'На доработке'];
                if ($ready)    return ['border-amber-200 bg-amber-50', 'bg-amber-100 border-b border-amber-200','bg-amber-100 text-amber-700','На проверке'];
                return ['border-gray-200 bg-white','bg-gray-50 border-b border-gray-100','bg-gray-100 text-gray-500','Ожидание'];
            };
            [$ptoCls,$ptoH,$ptoBadge,$ptoLbl]         = $ds($ptoApproved, $ptoRejected, $ptoReady);
            [$supplyCls,$supplyH,$supplyBadge,$supplyLbl] = $ds($supplyApproved, $supplyRejected, $supplyReady);
        @endphp

        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div>
                    <p class="font-bold text-gray-900 text-sm">Подготовка расчётов</p>
                    <p class="text-xs text-gray-400 mt-0.5">Утверждение по отделам перед общим согласованием</p>
                </div>
                @if($bothApproved)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-green-100 text-green-700">Все утверждены</span>
                @elseif($ptoRejected || $supplyRejected)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-orange-100 text-orange-700">Есть замечания</span>
                @elseif($ptoReady && $supplyReady)
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-amber-100 text-amber-700">Ожидают проверки</span>
                @else
                    <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-gray-100 text-gray-500">В процессе</span>
                @endif
            </div>
            <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-4">
                {{-- PTO --}}
                <div class="rounded-lg border {{ $ptoCls }}">
                    <div class="{{ $ptoH }} px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 20h.01M7 20v-4M12 20v-8M17 20V8M22 4l-5 5-4-4-7 7"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">ПТО</p>
                                <p class="text-xs text-gray-400">{{ $ptoFiles }} файлов</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $ptoBadge }}">{{ $ptoLbl }}</span>
                    </div>
                    <div class="p-4 space-y-3">
                        @if($project->pto_submitted_at)
                            <p class="text-xs text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                {{ Carbon::parse($project->pto_submitted_at)->format('d.m.Y H:i') }}
                            </p>
                        @endif
                        @if($project->pto_comment)
                            <div class="text-xs text-gray-700 bg-white border-l-2 {{ $ptoRejected ? 'border-red-400' : 'border-blue-400' }} px-3 py-2 rounded-r-lg">{{ $project->pto_comment }}</div>
                        @endif
                        @if($ptoApproved)
                            <p class="text-xs text-green-700 font-semibold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Расчёты утверждены
                            </p>
                        @endif
                        @if($ptoReady && !$ptoApproved && !$ptoRejected)
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <form method="POST" action="{{ route('projects.approve-pto', $project) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                        Утвердить
                                    </button>
                                </form>
                                <button onclick="showModal('rejectPtoModal')" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    Доработка
                                </button>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Supply --}}
                <div class="rounded-lg border {{ $supplyCls }}">
                    <div class="{{ $supplyH }} px-4 py-3 flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-lg bg-white border border-gray-200 flex items-center justify-center">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-800">Снабжение</p>
                                <p class="text-xs text-gray-400">{{ $supplyFiles }} файлов</p>
                            </div>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $supplyBadge }}">{{ $supplyLbl }}</span>
                    </div>
                    <div class="p-4 space-y-3">
                        @if($project->supply_submitted_at)
                            <p class="text-xs text-gray-400 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                {{ Carbon::parse($project->supply_submitted_at)->format('d.m.Y H:i') }}
                            </p>
                        @endif
                        @if($project->supply_comment)
                            <div class="text-xs text-gray-700 bg-white border-l-2 {{ $supplyRejected ? 'border-red-400' : 'border-green-400' }} px-3 py-2 rounded-r-lg">{{ $project->supply_comment }}</div>
                        @endif
                        @if($supplyApproved)
                            <p class="text-xs text-green-700 font-semibold flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Расчёты утверждены
                            </p>
                        @endif
                        @if($supplyReady && !$supplyApproved && !$supplyRejected)
                            <div class="grid grid-cols-2 gap-2 pt-1">
                                <form method="POST" action="{{ route('projects.approve-supply', $project) }}">
                                    @csrf
                                    <button type="submit" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                        Утвердить
                                    </button>
                                </form>
                                <button onclick="showModal('rejectSupplyModal')" class="w-full inline-flex items-center justify-center gap-1.5 px-3 py-2 rounded-lg bg-red-600 text-white text-xs font-semibold hover:bg-red-700 transition-colors">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                                    Доработка
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            @if($bothApproved && !in_array($project->status, ['on_approval','approved','in_progress','completed']))
                <div class="px-5 pb-5">
                    <form method="POST" action="{{ route('projects.send-to-approval', $project) }}">
                        @csrf
                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-lg bg-green-600 text-white text-sm font-bold hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Отправить на общее согласование
                        </button>
                    </form>
                </div>
            @endif

            @if($project->status === 'on_approval')
                <div class="mx-5 mb-5 rounded-lg bg-amber-50 border border-amber-200 p-4">
                    <p class="text-sm font-bold text-amber-800 mb-3 flex items-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        Проект на согласовании
                    </p>
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="showModal('approveModal')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                            Утвердить проект
                        </button>
                        <button onclick="showModal('rejectModal')" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-red-600 text-white text-sm font-semibold hover:bg-red-700 transition-colors">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
                            Отклонить
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endcan

    {{-- PTO SECTION --}}
    @can('uploadPTOFiles', $project)
        @php
            $userFiles   = $project->files->where('section','pto')->where('user_id', Auth::id());
            $isSubmitted = !is_null($project->pto_submitted_at);
            $isApproved  = $project->pto_approved === true;
            $isRejected  = $project->pto_approved === false;
            $canUpload   = !$isSubmitted || $isRejected;
        @endphp
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-blue-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 20h.01M7 20v-4M12 20v-8M17 20V8M22 4l-5 5-4-4-7 7"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 text-sm">ПТО — Мои расчёты</p>
                        <p class="text-xs text-gray-400">Технический отдел</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $isApproved ? 'bg-green-100 text-green-700' : ($isRejected ? 'bg-red-100 text-red-700' : ($isSubmitted ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500')) }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $isApproved ? 'bg-green-500' : ($isRejected ? 'bg-red-500' : ($isSubmitted ? 'bg-amber-500' : 'bg-gray-400')) }}"></span>
                    {{ $isApproved ? 'Утверждено' : ($isRejected ? 'На доработке' : ($isSubmitted ? 'На проверке' : 'Черновик')) }}
                </span>
            </div>
            <div class="p-5 space-y-4">
                @if($isRejected && $project->pto_comment)
                    <div class="flex gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <p class="text-xs font-bold text-red-700 mb-1">Замечания директора</p>
                            <p class="text-sm text-red-600">{{ $project->pto_comment }}</p>
                        </div>
                    </div>
                @endif

                @if($canUpload)
                    <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="pto">
                        <label for="pto-files" class="flex flex-col items-center justify-center gap-2 p-6 border-2 border-dashed border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span class="text-sm font-semibold text-gray-500">Нажмите или перетащите файлы</span>
                            <span class="text-xs text-gray-400">Макс. 20 МБ на файл</span>
                            <span id="pto-count" class="text-xs text-blue-600 font-semibold" style="display:none;"></span>
                        </label>
                        <input type="file" id="pto-files" name="files[]" multiple class="hidden" onchange="showCount(this,'pto-count'); this.form.submit()">
                    </form>
                @endif

                @if($userFiles->count() > 0)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Мои файлы ({{ $userFiles->count() }})</p>
                            @if($canUpload)
                                <button onclick="deleteSelected('pto')" id="del-pto-btn" class="text-xs text-red-600 font-semibold hover:underline flex items-center gap-1" style="display:none;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                    Удалить выбранные
                                </button>
                            @endif
                        </div>
                        <div class="space-y-1.5">
                            @foreach($userFiles->sortByDesc('created_at') as $file)
                                @php $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)); @endphp
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 hover:bg-white transition-all group" id="file-{{ $file->id }}">
                                    @if($canUpload)
                                        <input type="checkbox" class="file-cb-pto w-3.5 h-3.5 accent-blue-600 flex-shrink-0" data-id="{{ $file->id }}" onchange="syncDel('pto')">
                                    @endif
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ match($ext) { 'pdf' => 'bg-red-50', 'doc','docx' => 'bg-blue-50', 'xls','xlsx' => 'bg-green-50', default => 'bg-gray-100' } }}">
                                        <svg class="w-4 h-4 {{ match($ext) { 'pdf' => 'text-red-500', 'doc','docx' => 'text-blue-500', 'xls','xlsx' => 'text-green-500', default => 'text-gray-400' } }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-sm font-semibold text-blue-600 hover:text-blue-800 truncate block">{{ $file->file_name }}</a>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ Carbon::parse($file->created_at)->format('d.m.Y H:i') }} · {{ round($file->file_size/1024, 1) }} KB</p>
                                    </div>
                                    @if($canUpload)
                                        <button onclick="deleteSingle({{ $file->id }})" class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                        <svg class="w-7 h-7 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                        <p class="text-sm text-gray-400">Нет загруженных файлов</p>
                    </div>
                @endif

                @if($userFiles->count() > 0 && ((!$isSubmitted && $canUpload) || $isRejected))
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-bold text-gray-800 mb-3">{{ $isRejected ? 'Отправить исправленные расчёты' : 'Отправить на проверку' }}</p>
                        <form method="POST" action="{{ route('projects.submit-pto', $project) }}">
                            @csrf
                            <textarea name="comment" rows="2" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-blue-400 resize-none mb-3" placeholder="{{ $isRejected ? 'Опишите что исправили...' : 'Краткое описание расчётов...' }}"></textarea>
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Отправить на проверку
                            </button>
                        </form>
                        <p class="text-xs text-gray-400 text-center mt-2">После отправки файлы заморожены до решения директора</p>
                    </div>
                @endif

                @if($isSubmitted && !$isApproved && !$isRejected)
                    <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        Расчёты отправлены. Ожидайте решения директора.
                    </div>
                @endif
                @if($isApproved)
                    <div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Расчёты утверждены директором.
                    </div>
                @endif
            </div>
        </div>
    @endcan

    {{-- SUPPLY SECTION --}}
    @can('uploadSupplyFiles', $project)
        @php
            $userFiles   = $project->files->where('section','supply')->where('user_id', Auth::id());
            $isSubmitted = !is_null($project->supply_submitted_at);
            $isApproved  = $project->supply_approved === true;
            $isRejected  = $project->supply_approved === false;
            $canUpload   = !$isSubmitted || $isRejected;
        @endphp
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
                <div class="flex items-center gap-3">
                    <div class="w-9 h-9 rounded-lg bg-green-50 flex items-center justify-center">
                        <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 7H4a2 2 0 00-2 2v6a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                    </div>
                    <div>
                        <p class="font-bold text-gray-900 text-sm">Снабжение — Мои сметы</p>
                        <p class="text-xs text-gray-400">Отдел снабжения</p>
                    </div>
                </div>
                <span class="inline-flex items-center gap-1.5 text-xs font-semibold px-2.5 py-1 rounded-full {{ $isApproved ? 'bg-green-100 text-green-700' : ($isRejected ? 'bg-red-100 text-red-700' : ($isSubmitted ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500')) }}">
                    <span class="w-1.5 h-1.5 rounded-full {{ $isApproved ? 'bg-green-500' : ($isRejected ? 'bg-red-500' : ($isSubmitted ? 'bg-amber-500' : 'bg-gray-400')) }}"></span>
                    {{ $isApproved ? 'Утверждено' : ($isRejected ? 'На доработке' : ($isSubmitted ? 'На проверке' : 'Черновик')) }}
                </span>
            </div>
            <div class="p-5 space-y-4">
                @if($isRejected && $project->supply_comment)
                    <div class="flex gap-3 p-4 bg-red-50 border border-red-200 rounded-lg">
                        <svg class="w-4 h-4 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        <div>
                            <p class="text-xs font-bold text-red-700 mb-1">Замечания директора</p>
                            <p class="text-sm text-red-600">{{ $project->supply_comment }}</p>
                        </div>
                    </div>
                @endif

                @if($canUpload)
                    <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="supply">
                        <label for="supply-files" class="flex flex-col items-center justify-center gap-2 p-6 border-2 border-dashed border-gray-200 rounded-lg cursor-pointer hover:border-green-400 hover:bg-green-50 transition-colors">
                            <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                            <span class="text-sm font-semibold text-gray-500">Нажмите или перетащите файлы</span>
                            <span class="text-xs text-gray-400">Макс. 20 МБ на файл</span>
                            <span id="supply-count" class="text-xs text-green-600 font-semibold" style="display:none;"></span>
                        </label>
                        <input type="file" id="supply-files" name="files[]" multiple class="hidden" onchange="showCount(this,'supply-count'); this.form.submit()">
                    </form>
                @endif

                @if($userFiles->count() > 0)
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <p class="text-xs font-bold text-gray-400 uppercase tracking-widest">Мои файлы ({{ $userFiles->count() }})</p>
                            @if($canUpload)
                                <button onclick="deleteSelected('supply')" id="del-supply-btn" class="text-xs text-red-600 font-semibold hover:underline flex items-center gap-1" style="display:none;">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                    Удалить выбранные
                                </button>
                            @endif
                        </div>
                        <div class="space-y-1.5">
                            @foreach($userFiles->sortByDesc('created_at') as $file)
                                @php $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION)); @endphp
                                <div class="flex items-center gap-3 p-3 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 hover:bg-white transition-all group" id="file-{{ $file->id }}">
                                    @if($canUpload)
                                        <input type="checkbox" class="file-cb-supply w-3.5 h-3.5 accent-green-600 flex-shrink-0" data-id="{{ $file->id }}" onchange="syncDel('supply')">
                                    @endif
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 {{ match($ext) { 'pdf' => 'bg-red-50', 'doc','docx' => 'bg-blue-50', 'xls','xlsx' => 'bg-green-50', default => 'bg-gray-100' } }}">
                                        <svg class="w-4 h-4 {{ match($ext) { 'pdf' => 'text-red-500', 'doc','docx' => 'text-blue-500', 'xls','xlsx' => 'text-green-500', default => 'text-gray-400' } }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <a href="{{ Storage::url($file->file_path) }}" target="_blank" class="text-sm font-semibold text-blue-600 hover:text-blue-800 truncate block">{{ $file->file_name }}</a>
                                        <p class="text-xs text-gray-400 mt-0.5">{{ Carbon::parse($file->created_at)->format('d.m.Y H:i') }} · {{ round($file->file_size/1024, 1) }} KB</p>
                                    </div>
                                    @if($canUpload)
                                        <button onclick="deleteSingle({{ $file->id }})" class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all opacity-0 group-hover:opacity-100">
                                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                        </button>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                        <svg class="w-7 h-7 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M22 19a2 2 0 01-2 2H4a2 2 0 01-2-2V5a2 2 0 012-2h5l2 3h9a2 2 0 012 2z"/></svg>
                        <p class="text-sm text-gray-400">Нет загруженных файлов</p>
                    </div>
                @endif

                @if($userFiles->count() > 0 && ((!$isSubmitted && $canUpload) || $isRejected))
                    <div class="bg-gray-50 border border-gray-200 rounded-lg p-4">
                        <p class="text-sm font-bold text-gray-800 mb-3">{{ $isRejected ? 'Отправить исправленные сметы' : 'Отправить на проверку' }}</p>
                        <form method="POST" action="{{ route('projects.submit-supply', $project) }}">
                            @csrf
                            <textarea name="comment" rows="2" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-green-400 resize-none mb-3" placeholder="{{ $isRejected ? 'Опишите что исправили...' : 'Краткое описание смет...' }}"></textarea>
                            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg bg-green-600 text-white text-sm font-semibold hover:bg-green-700 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Отправить на проверку
                            </button>
                        </form>
                    </div>
                @endif

                @if($isSubmitted && !$isApproved && !$isRejected)
                    <div class="flex items-center gap-2 p-3 bg-amber-50 border border-amber-200 rounded-lg text-sm text-amber-700">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        Сметы отправлены. Ожидайте решения директора.
                    </div>
                @endif
                @if($isApproved)
                    <div class="flex items-center gap-2 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        Сметы утверждены директором.
                    </div>
                @endif
            </div>
        </div>
    @endcan

    {{-- PARTICIPANTS --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="flex items-center justify-between px-5 py-4 border-b border-gray-100">
            <p class="font-bold text-gray-900 text-sm">Участники проекта</p>
            @can('manageParticipants', $project)
                <button onclick="showModal('addParticipantModal')" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-gray-200 bg-white text-xs font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Добавить
                </button>
            @endcan
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-2">
            @foreach($project->participants as $participant)
                @php
                    $isKey = in_array($participant->pivot->role, ['director','deputy_director','pto','supply']);
                    $rb  = $roleBadge[$participant->pivot->role]  ?? 'bg-gray-100 text-gray-600';
                    $rav = $roleAvatar[$participant->pivot->role] ?? 'bg-gray-100 text-gray-600';
                @endphp
                <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 hover:bg-white transition-all">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full {{ $rav }} flex items-center justify-center text-xs font-bold flex-shrink-0">
                            {{ strtoupper(substr($participant->name, 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-gray-800">{{ $participant->name }}</p>
                            <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $rb }}">{{ $roleLabels[$participant->pivot->role] ?? $participant->pivot->role }}</span>
                        </div>
                    </div>
                    @can('manageParticipants', $project)
                        @if(!$isKey)
                            <form method="POST" action="{{ route('projects.participants.remove', [$project, $participant]) }}" onsubmit="return confirm('Удалить участника?')">
                                @csrf @method('DELETE')
                                <button type="submit" class="p-1.5 rounded-lg text-gray-300 hover:text-red-500 hover:bg-red-50 transition-all">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/></svg>
                                </button>
                            </form>
                        @else
                            <svg class="w-3.5 h-3.5 text-gray-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" title="Обязательный участник"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        @endif
                    @endcan
                </div>
            @endforeach
        </div>
    </div>

    {{-- TABS --}}
    <div class="bg-white rounded-xl border border-gray-200">
        <div class="px-4 pt-4">
            <div class="flex gap-0.5 bg-gray-100 p-1 rounded-lg overflow-x-auto">
                @foreach([['files','Файлы'],['comments','Комментарии'],['tasks','Задачи'],['materials','Материалы'],['financial','Финансы'],['history','История']] as [$id, $label])
                    <button class="tab-btn flex-shrink-0 px-3 py-2 rounded-md text-xs font-semibold transition-all whitespace-nowrap {{ $id === 'files' ? 'bg-white text-gray-900 shadow-sm' : 'text-gray-500 hover:text-gray-700' }}"
                            onclick="switchTab(this, '{{ $id }}-tab')">{{ $label }}</button>
                @endforeach
            </div>
        </div>

        {{-- Files --}}
        <div id="files-tab" class="tab-pane p-5">
            <div class="flex items-center justify-between mb-4 flex-wrap gap-2">
                <div class="flex items-center gap-1.5">
                    @foreach([['all','Все'],['general','Общие'],['pto','ПТО'],['supply','Снабжение']] as [$f, $l])
                        <button onclick="filterFiles('{{ $f }}', this)" data-filter="{{ $f }}"
                                class="file-filter px-3 py-1.5 rounded-lg text-xs font-semibold transition-colors {{ $f === 'all' ? 'bg-blue-600 text-white' : 'bg-gray-100 text-gray-500 hover:bg-gray-200' }}">{{ $l }}</button>
                    @endforeach
                </div>
                <button onclick="document.getElementById('general-file-input').click()" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    Загрузить
                </button>
                <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data" class="hidden">
                    @csrf
                    <input type="hidden" name="section" value="general">
                    <input type="file" id="general-file-input" name="files[]" multiple onchange="this.form.submit()">
                </form>
            </div>
            <div id="all-files">
                @include('projects.partials.files-list', ['filesByUser' => $project->files->groupBy('user_id'), 'project' => $project])
            </div>
        </div>

        {{-- Comments --}}
        <div id="comments-tab" class="tab-pane p-5 hidden">
            <form method="POST" action="{{ route('projects.comments', $project) }}" class="mb-5">
                @csrf
                <textarea name="content" rows="3" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-blue-400 resize-none mb-2" placeholder="Ваш комментарий..."></textarea>
                <div class="flex items-center gap-2">
                    <select name="section" class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400">
                        <option value="general">Общий</option>
                        <option value="pto">ПТО</option>
                        <option value="supply">Снабжение</option>
                    </select>
                    <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">Отправить</button>
                </div>
            </form>
            <div class="space-y-2">
                @foreach($project->comments as $c)
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="flex items-start justify-between gap-3">
                            <div class="flex-1">
                                <p class="text-sm font-bold text-gray-800 mb-1">{{ $c->user->name }}</p>
                                <p class="text-sm text-gray-600">{{ $c->content }}</p>
                            </div>
                            <div class="text-right flex-shrink-0">
                                <span class="text-xs font-semibold px-2 py-0.5 rounded-full bg-gray-200 text-gray-600">{{ $c->section }}</span>
                                <p class="text-xs text-gray-400 mt-1">{{ $c->created_at ? Carbon::parse($c->created_at)->format('d.m.Y H:i') : '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Tasks --}}
        <div id="tasks-tab" class="tab-pane p-5 hidden">
            @can('createTask', $project)
                <div class="mb-4">
                    <a href="{{ route('projects.tasks.create', $project) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Создать задачу
                    </a>
                </div>
            @endcan
            @php
                $taskBadge = ['sent'=>'bg-amber-100 text-amber-700','in_progress'=>'bg-blue-100 text-blue-700','completed'=>'bg-green-100 text-green-700','cancelled'=>'bg-gray-100 text-gray-500'];
                $taskLabel = ['sent'=>'Назначена','in_progress'=>'В работе','completed'=>'Выполнена','cancelled'=>'Отменена'];
            @endphp
            <div class="space-y-2">
                @foreach($project->tasks as $task)
                    <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="flex items-center justify-between p-4 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 hover:bg-white transition-all group">
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 group-hover:text-blue-600 transition-colors">{{ $task->title }}</p>
                            @if($task->description) <p class="text-xs text-gray-400 mt-0.5 truncate">{{ $task->description }}</p> @endif
                            <p class="text-xs text-gray-400 mt-1">{{ $task->assignee->name }}</p>
                        </div>
                        <span class="text-xs font-semibold px-2.5 py-1 rounded-full ml-3 flex-shrink-0 {{ $taskBadge[$task->status] ?? 'bg-gray-100 text-gray-500' }}">{{ $taskLabel[$task->status] ?? $task->status }}</span>
                    </a>
                @endforeach
            </div>
        </div>

        {{-- Materials --}}
        <div id="materials-tab" class="tab-pane p-5 hidden">
            @can('createMaterial', $project)
                <div class="mb-4">
                    <a href="{{ route('projects.materials.create', $project) }}" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                        Добавить поставку
                    </a>
                </div>
            @endcan
            <div class="space-y-2">
                @foreach($project->materialDeliveries as $d)
                    <div class="p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <div class="flex items-start justify-between gap-3">
                            <div>
                                <p class="text-sm font-bold text-gray-800">{{ $d->material_name }}</p>
                                <p class="text-xs text-gray-500 mt-0.5">{{ $d->quantity }} {{ $d->unit }} · {{ $d->supplyUser->name }}</p>
                                @if($d->confirmed_date) <p class="text-xs text-gray-400 mt-0.5">Подтверждено {{ Carbon::parse($d->confirmed_date)->format('d.m.Y') }}</p> @endif
                            </div>
                            <div class="flex-shrink-0 text-right">
                                <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $d->status === 'pending' ? 'bg-amber-100 text-amber-700' : 'bg-green-100 text-green-700' }}">{{ $d->status === 'pending' ? 'Ожидает' : 'Доставлено' }}</span>
                                @if($d->status === 'pending' && Auth::user()->isSiteManager())
                                    <form method="POST" action="{{ route('materials.confirm', $d) }}" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <input type="file" name="photo" accept="image/*" class="text-xs mb-1.5 block">
                                        <button type="submit" class="px-3 py-1.5 rounded-lg bg-green-600 text-white text-xs font-semibold hover:bg-green-700 transition-colors">Подтвердить</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Financial --}}
        <div id="financial-tab" class="tab-pane p-5 hidden">
            @can('updateFinancial', $project)
                <form method="POST" action="{{ route('projects.financial.update', $project) }}" class="mb-5 p-4 bg-gray-50 rounded-lg border border-gray-200">
                    @csrf
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Обновить статус</p>
                    <div class="flex items-center gap-2 flex-wrap">
                        <select name="financial_status" required class="text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400">
                            <option value="pending_payment">На оплате</option>
                            <option value="paid">Оплачено</option>
                            <option value="not_paid">Не оплачено</option>
                        </select>
                        <input type="text" name="comment" placeholder="Комментарий" class="flex-1 min-w-32 text-sm border border-gray-200 rounded-lg px-3 py-2 focus:outline-none focus:border-blue-400">
                        <button type="submit" class="px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">Обновить</button>
                    </div>
                </form>
            @endcan
            @php
                $finBadge = ['pending_payment'=>'bg-amber-100 text-amber-700','paid'=>'bg-green-100 text-green-700','not_paid'=>'bg-red-100 text-red-700'];
                $finLabel = ['pending_payment'=>'На оплате','paid'=>'Оплачено','not_paid'=>'Не оплачено'];
            @endphp
            <div class="space-y-2">
                @foreach($project->financialStatusLogs as $log)
                    <div class="flex items-start justify-between p-4 rounded-lg bg-gray-50 border border-gray-100">
                        <div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full {{ $finBadge[$log->financial_status] ?? 'bg-gray-100 text-gray-600' }}">{{ $finLabel[$log->financial_status] ?? $log->financial_status }}</span>
                            @if($log->comment) <p class="text-sm text-gray-600 mt-2">{{ $log->comment }}</p> @endif
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-xs font-semibold text-gray-700">{{ $log->user->name }}</p>
                            <p class="text-xs text-gray-400 mt-0.5">{{ $log->created_at ? Carbon::parse($log->created_at)->format('d.m.Y H:i') : '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- History --}}
        <div id="history-tab" class="tab-pane p-5 hidden">
            @php $histLabel = ['created'=>'Создан','in_calculation'=>'В расчёте','on_approval'=>'На согласовании','on_revision'=>'На доработке','approved'=>'Утверждён','in_progress'=>'В реализации','completed'=>'Завершён']; @endphp
            <div class="space-y-3">
                @foreach($project->statusLogs as $log)
                    <div class="relative pl-6">
                        @if(!$loop->last) <div class="absolute left-[5px] top-4 bottom-0 w-px bg-gray-200"></div> @endif
                        <div class="absolute left-0 top-1.5 w-2.5 h-2.5 rounded-full border-2 border-gray-300 bg-white"></div>
                        <div class="p-3 rounded-lg bg-gray-50 border border-gray-100">
                            <div class="flex items-start justify-between gap-3">
                                <p class="text-sm text-gray-700">
                                    <span class="font-bold">{{ $log->user->name }}</span>
                                    <span class="text-gray-400 mx-1">·</span>
                                    <span class="text-gray-400">{{ $histLabel[$log->old_status] ?? $log->old_status }}</span>
                                    <svg class="w-3 h-3 inline mx-1 text-gray-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                                    <span class="font-semibold">{{ $histLabel[$log->new_status] ?? $log->new_status }}</span>
                                </p>
                                <p class="text-xs text-gray-400 whitespace-nowrap">{{ $log->created_at ? Carbon::parse($log->created_at)->format('d.m.Y H:i') : '' }}</p>
                            </div>
                            @if($log->comment) <p class="text-xs text-gray-400 mt-1">{{ $log->comment }}</p> @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
</div>

{{-- MODALS --}}
@foreach([
    ['approveModal',      'Утвердить проект',         'Добавьте комментарий к решению.',    route('projects.approve', $project),       'Утвердить',    'bg-green-600 hover:bg-green-700'],
    ['rejectModal',       'Отклонить проект',          'Укажите, что необходимо исправить.',  route('projects.reject', $project),        'Отклонить',    'bg-red-600 hover:bg-red-700'],
    ['rejectPtoModal',    'ПТО — на доработку',        'Опишите замечания к расчётам.',       route('projects.reject-pto', $project),    'На доработку', 'bg-red-600 hover:bg-red-700'],
    ['rejectSupplyModal', 'Снабжение — на доработку',  'Опишите замечания к сметам.',         route('projects.reject-supply', $project), 'На доработку', 'bg-red-600 hover:bg-red-700'],
] as [$id, $title, $subtitle, $action, $btnLabel, $btnCls])
    <div id="{{ $id }}" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50" style="align-items:center;justify-content:center;">
        <div class="bg-white rounded-xl border border-gray-200 w-full max-w-md shadow-2xl p-6 mx-4">
            <p class="font-extrabold text-gray-900 text-base mb-1">{{ $title }}</p>
            <p class="text-sm text-gray-400 mb-4">{{ $subtitle }}</p>
            <form method="POST" action="{{ $action }}">
                @csrf
                <textarea name="comment" rows="3" required class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none resize-none mb-4"></textarea>
                <div class="flex justify-end gap-2">
                    <button type="button" onclick="hideModal('{{ $id }}')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Отмена</button>
                    <button type="submit" class="px-4 py-2 rounded-lg text-white text-sm font-semibold transition-colors {{ $btnCls }}">{{ $btnLabel }}</button>
                </div>
            </form>
        </div>
    </div>
@endforeach

{{-- Add participant modal --}}
<div id="addParticipantModal" class="fixed inset-0 bg-black bg-opacity-40 hidden z-50" style="align-items:center;justify-content:center;">
    <div class="bg-white rounded-xl border border-gray-200 w-full max-w-lg shadow-2xl p-6 mx-4">
        <div class="flex items-start justify-between mb-4">
            <div>
                <p class="font-extrabold text-gray-900 text-base">Добавить участников</p>
                <p class="text-xs text-gray-400 mt-0.5">Роль определяется автоматически по должности</p>
            </div>
            <button onclick="hideModal('addParticipantModal')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-600 hover:bg-gray-100 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" action="{{ route('projects.participants.add', $project) }}" id="add-participant-form">
            @csrf
            @php $existingRoles = $project->participants->pluck('pivot.role')->toArray(); @endphp
            <div class="max-h-72 overflow-y-auto space-y-1.5 mb-4 pr-1">
                @forelse($availableUsers as $user)
                    @php
                        $exists = in_array($user->role, $existingRoles);
                        $rb2 = $roleBadge[$user->role] ?? 'bg-gray-100 text-gray-600';
                        $ra2 = $roleAvatar[$user->role] ?? 'bg-gray-100 text-gray-600';
                        $rn2 = $roleLabels[$user->role] ?? $user->role;
                    @endphp
                    <label class="flex items-center gap-3 p-3 rounded-lg border transition-all {{ $exists ? 'border-gray-100 bg-gray-50 opacity-60 cursor-default' : 'border-transparent bg-gray-50 hover:border-gray-200 hover:bg-white cursor-pointer' }}">
                        <input type="checkbox" name="users[]" value="{{ $user->id }}" class="user-cb w-4 h-4 accent-blue-600 flex-shrink-0"
                               data-role="{{ $user->role }}" {{ $exists ? 'disabled' : '' }} onchange="syncAddBtn()">
                        <div class="w-7 h-7 rounded-full {{ $ra2 }} flex items-center justify-center text-xs font-bold flex-shrink-0">{{ strtoupper(substr($user->name,0,1)) }}</div>
                        <p class="flex-1 text-sm font-semibold text-gray-800 truncate">{{ $user->name }}</p>
                        <span class="text-xs font-semibold px-2 py-0.5 rounded-full {{ $rb2 }} flex-shrink-0">{{ $rn2 }}</span>
                        @if($exists)
                            <svg class="w-3.5 h-3.5 text-gray-300 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
                        @endif
                    </label>
                @empty
                    <div class="text-center py-8 text-sm text-gray-400">Все пользователи уже добавлены</div>
                @endforelse
            </div>
            <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                <button type="button" onclick="hideModal('addParticipantModal')" class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">Отмена</button>
                <button type="submit" id="add-btn" disabled class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 disabled:opacity-40 disabled:cursor-not-allowed transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Добавить
                </button>
            </div>
        </form>
    </div>
</div>

<script>
// Modal helpers — используем display flex для центрирования
function showModal(id) {
    const m = document.getElementById(id);
    m.style.display = 'flex';
}
function hideModal(id) {
    const m = document.getElementById(id);
    m.style.display = 'none';
}
// Close on backdrop click
document.querySelectorAll('.fixed.inset-0').forEach(m => {
    m.addEventListener('click', e => { if (e.target === m) m.style.display = 'none'; });
});

// Tabs
function switchTab(btn, paneId) {
    document.querySelectorAll('.tab-btn').forEach(b => {
        b.classList.remove('bg-white','text-gray-900','shadow-sm');
        b.classList.add('text-gray-500');
    });
    document.querySelectorAll('.tab-pane').forEach(p => p.classList.add('hidden'));
    btn.classList.add('bg-white','text-gray-900','shadow-sm');
    btn.classList.remove('text-gray-500');
    document.getElementById(paneId).classList.remove('hidden');
}

// File filter
function filterFiles(filter, btn) {
    document.querySelectorAll('.file-filter').forEach(b => {
        b.classList.remove('bg-blue-600','text-white');
        b.classList.add('bg-gray-100','text-gray-500');
    });
    btn.classList.add('bg-blue-600','text-white');
    btn.classList.remove('bg-gray-100','text-gray-500');
    document.querySelectorAll('.file-group').forEach(g => {
        g.style.display = (filter === 'all' || g.dataset.section === filter) ? '' : 'none';
    });
}

// File count
function showCount(input, targetId) {
    const el = document.getElementById(targetId);
    if (el && input.files.length) { el.textContent = input.files.length + ' файлов выбрано'; el.style.display = ''; }
}

// Delete
const PID  = {{ $project->id }};
const CSRF = document.querySelector('meta[name="csrf-token"]')?.content;

function deleteSingle(id) {
    if (!confirm('Удалить файл?')) return;
    fetch('/projects/' + PID + '/files/' + id, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) document.getElementById('file-' + id)?.remove(); });
}

function syncDel(sec) {
    const n = document.querySelectorAll('.file-cb-' + sec + ':checked').length;
    const b = document.getElementById('del-' + sec + '-btn');
    if (b) b.style.display = n ? 'inline-flex' : 'none';
}

function deleteSelected(sec) {
    const ids = [...document.querySelectorAll('.file-cb-' + sec + ':checked')].map(c => c.dataset.id);
    if (!ids.length || !confirm('Удалить ' + ids.length + ' файлов?')) return;
    ids.forEach(id => fetch('/projects/' + PID + '/files/' + id, {
        method: 'DELETE', headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
    }).then(r => r.json()).then(d => { if (d.success) document.getElementById('file-' + id)?.remove(); }));
    syncDel(sec);
}

// Add participant
function syncAddBtn() {
    document.getElementById('add-btn').disabled = !document.querySelectorAll('.user-cb:checked').length;
}
document.getElementById('add-participant-form')?.addEventListener('submit', function(e) {
    e.preventDefault();
    document.querySelectorAll('.user-cb:checked').forEach(cb => {
        [['user_id', cb.value], ['role', cb.dataset.role]].forEach(([k, v]) => {
            const i = document.createElement('input');
            i.type = 'hidden'; i.name = 'participants[' + cb.value + '][' + k + ']'; i.value = v;
            this.appendChild(i);
        });
    });
    this.submit();
});
</script>
@endsection