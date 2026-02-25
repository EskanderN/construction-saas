@extends('layouts.app')

@section('title', $project->name)

@section('content')
<div class="space-y-6">
    <!-- Заголовок и действия - только для директора и замдиректора -->
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold">{{ $project->name }}</h1>
            <p class="text-gray-600 mt-2">{{ $project->description }}</p>
        </div>
        
        <div class="flex space-x-2">
            @can('update', $project)
                <a href="{{ route('projects.edit', $project) }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                    Редактировать
                </a>
            @endcan
        </div>
    </div>

    <!-- Статус проекта -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-lg font-semibold mb-2">Статус проекта</h2>
                <span class="px-3 py-1 text-sm rounded-full 
                    @if($project->status === 'created') bg-gray-100 text-gray-800
                    @elseif($project->status === 'in_calculation') bg-blue-100 text-blue-800
                    @elseif($project->status === 'on_approval') bg-yellow-100 text-yellow-800
                    @elseif($project->status === 'on_revision') bg-orange-100 text-orange-800
                    @elseif($project->status === 'approved') bg-green-100 text-green-800
                    @elseif($project->status === 'in_progress') bg-purple-100 text-purple-800
                    @else bg-green-100 text-green-800
                    @endif">
                    @switch($project->status)
                        @case('created') Создан @break
                        @case('in_calculation') В расчете @break
                        @case('on_approval') На согласовании @break
                        @case('on_revision') На доработке @break
                        @case('approved') Утвержден @break
                        @case('in_progress') В реализации @break
                        @case('completed') Завершен @break
                        @default {{ $project->status }}
                    @endswitch
                </span>
            </div>

            <div class="flex space-x-2">
                @can('update', $project)
                    @if($project->status === 'created')
                        <form method="POST" action="{{ route('projects.send-to-calculation', $project) }}">
                            @csrf
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                Отправить в расчет
                            </button>
                        </form>
                    @endif

                    @if($project->status === 'approved')
                        <form method="POST" action="{{ route('projects.start-implementation', $project) }}">
                            @csrf
                            <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                                Начать реализацию
                            </button>
                        </form>
                    @endif
                @endcan

                @can('approve', $project)
                    @if($project->status === 'on_approval')
                        <button onclick="openApproveModal()" class="bg-green-500 text-white px-4 py-2 rounded-md hover:bg-green-600">
                            Утвердить
                        </button>
                        <button onclick="openRejectModal()" class="bg-red-500 text-white px-4 py-2 rounded-md hover:bg-red-600">
                            Отклонить
                        </button>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    <!-- Прогресс-бар для директора с раздельным утверждением -->
    @can('manageParticipants', $project)
        @php
            $ptoReady = !is_null($project->pto_submitted_at);
            $supplyReady = !is_null($project->supply_submitted_at);
            $ptoApproved = $project->pto_approved === true;
            $supplyApproved = $project->supply_approved === true;
            $ptoRejected = $project->pto_approved === false;
            $supplyRejected = $project->supply_approved === false;
            
            $ptoFilesCount = $project->files->where('section', 'pto')->count();
            $supplyFilesCount = $project->files->where('section', 'supply')->count();
            
            // Определяем общий статус
            $bothApproved = $ptoApproved && $supplyApproved;
            $anyRejected = $ptoRejected || $supplyRejected;
            $allSubmitted = $ptoReady && $supplyReady;
        @endphp
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-xl font-bold text-gray-800">📊 Прогресс подготовки расчетов</h2>
                
                @if($bothApproved)
                    <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-sm font-medium">
                        ✅ Все отделы утверждены
                    </span>
                @elseif($anyRejected)
                    <span class="px-3 py-1 bg-orange-100 text-orange-700 rounded-full text-sm font-medium">
                        ⚠️ Есть отделы на доработке
                    </span>
                @elseif($allSubmitted)
                    <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-sm font-medium">
                        ⏳ Ожидают проверки
                    </span>
                @else
                    <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-sm font-medium">
                        📝 В процессе
                    </span>
                @endif
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- ПТО секция -->
                <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md
                    @if($ptoApproved) border-green-300 bg-green-50/30
                    @elseif($ptoRejected) border-red-300 bg-red-50/30
                    @elseif($ptoReady) border-yellow-300 bg-yellow-50/30
                    @else border-gray-200 bg-gray-50/30
                    @endif">
                    
                    <div class="px-5 py-4 border-b flex items-center justify-between
                        @if($ptoApproved) bg-green-100 border-green-200
                        @elseif($ptoRejected) bg-red-100 border-red-200
                        @elseif($ptoReady) bg-yellow-100 border-yellow-200
                        @else bg-gray-100 border-gray-200
                        @endif">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-lg font-bold shadow-sm">
                                📐
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">ПТО</h3>
                                <p class="text-xs text-gray-600">{{ $ptoFilesCount }} файлов</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($ptoApproved)
                                <span class="px-3 py-1 bg-green-600 text-white rounded-full text-xs font-medium">✓ Утвержден</span>
                            @elseif($ptoRejected)
                                <span class="px-3 py-1 bg-red-600 text-white rounded-full text-xs font-medium">✗ На доработке</span>
                            @elseif($ptoReady)
                                <span class="px-3 py-1 bg-yellow-600 text-white rounded-full text-xs font-medium">⏳ На проверке</span>
                            @else
                                <span class="px-3 py-1 bg-gray-400 text-white rounded-full text-xs font-medium">⏳ Ожидание</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-4">
                        @if($project->pto_comment)
                            <div class="bg-white rounded-lg p-3 border-l-4 border-blue-400 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">Комментарий ПТО:</p>
                                <p class="text-sm text-gray-700">"{{ $project->pto_comment }}"</p>
                            </div>
                        @endif
                        
                        @if($project->pto_submitted_at)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Отправлено: {{ \Carbon\Carbon::parse($project->pto_submitted_at)->format('d.m.Y H:i') }}
                            </div>
                        @endif
                        
                        @if($ptoReady && !$ptoApproved && !$ptoRejected)
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <form method="POST" action="{{ route('projects.approve-pto', $project) }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-3 rounded-lg text-sm font-medium transition flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Утвердить
                                    </button>
                                </form>
                                <button onclick="openRejectPtoModal()" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg text-sm font-medium transition flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Доработка
                                </button>
                            </div>
                        @endif
                        
                        @if($ptoApproved)
                            <div class="bg-green-100 text-green-700 p-3 rounded-lg text-sm flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Расчеты утверждены
                            </div>
                        @endif
                        
                        @if($ptoRejected)
                            <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm">
                                <p class="font-medium mb-1">Причина доработки:</p>
                                <p>"{{ $project->pto_comment }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Снабжение секция -->
                <div class="border rounded-xl overflow-hidden transition-all hover:shadow-md
                    @if($supplyApproved) border-green-300 bg-green-50/30
                    @elseif($supplyRejected) border-red-300 bg-red-50/30
                    @elseif($supplyReady) border-yellow-300 bg-yellow-50/30
                    @else border-gray-200 bg-gray-50/30
                    @endif">
                    
                    <div class="px-5 py-4 border-b flex items-center justify-between
                        @if($supplyApproved) bg-green-100 border-green-200
                        @elseif($supplyRejected) bg-red-100 border-red-200
                        @elseif($supplyReady) bg-yellow-100 border-yellow-200
                        @else bg-gray-100 border-gray-200
                        @endif">
                        <div class="flex items-center space-x-3">
                            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-lg font-bold shadow-sm">
                                📦
                            </div>
                            <div>
                                <h3 class="font-bold text-gray-800">Снабжение</h3>
                                <p class="text-xs text-gray-600">{{ $supplyFilesCount }} файлов</p>
                            </div>
                        </div>
                        <div class="text-right">
                            @if($supplyApproved)
                                <span class="px-3 py-1 bg-green-600 text-white rounded-full text-xs font-medium">✓ Утвержден</span>
                            @elseif($supplyRejected)
                                <span class="px-3 py-1 bg-red-600 text-white rounded-full text-xs font-medium">✗ На доработке</span>
                            @elseif($supplyReady)
                                <span class="px-3 py-1 bg-yellow-600 text-white rounded-full text-xs font-medium">⏳ На проверке</span>
                            @else
                                <span class="px-3 py-1 bg-gray-400 text-white rounded-full text-xs font-medium">⏳ Ожидание</span>
                            @endif
                        </div>
                    </div>
                    
                    <div class="p-5 space-y-4">
                        @if($project->supply_comment)
                            <div class="bg-white rounded-lg p-3 border-l-4 border-blue-400 shadow-sm">
                                <p class="text-xs text-gray-500 mb-1">Комментарий снабжения:</p>
                                <p class="text-sm text-gray-700">"{{ $project->supply_comment }}"</p>
                            </div>
                        @endif
                        
                        @if($project->supply_submitted_at)
                            <div class="flex items-center text-xs text-gray-500">
                                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Отправлено: {{ \Carbon\Carbon::parse($project->supply_submitted_at)->format('d.m.Y H:i') }}
                            </div>
                        @endif
                        
                        @if($supplyReady && !$supplyApproved && !$supplyRejected)
                            <div class="grid grid-cols-2 gap-3 pt-2">
                                <form method="POST" action="{{ route('projects.approve-supply', $project) }}">
                                    @csrf
                                    <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-3 rounded-lg text-sm font-medium transition flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        Утвердить
                                    </button>
                                </form>
                                <button onclick="openRejectSupplyModal()" class="w-full bg-red-500 hover:bg-red-600 text-white py-2 px-3 rounded-lg text-sm font-medium transition flex items-center justify-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                    Доработка
                                </button>
                            </div>
                        @endif
                        
                        @if($supplyApproved)
                            <div class="bg-green-100 text-green-700 p-3 rounded-lg text-sm flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                Расчеты утверждены
                            </div>
                        @endif
                        
                        @if($supplyRejected)
                            <div class="bg-red-100 text-red-700 p-3 rounded-lg text-sm">
                                <p class="font-medium mb-1">Причина доработки:</p>
                                <p>"{{ $project->supply_comment }}"</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Специальная секция для ПТО -->
            @can('uploadPTOFiles', $project)
                @php
                    $userFiles = $project->files->where('section', 'pto')->where('user_id', Auth::id());
                    $isSubmitted = !is_null($project->pto_submitted_at);
                    $isApproved = $project->pto_approved === true;
                    $isRejected = $project->pto_approved === false;
                    $canUpload = !$isSubmitted || $isRejected; // Можно загружать если не отправлено или на доработке
                @endphp
                
                <div class="bg-white rounded-xl shadow-lg p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div class="flex items-center space-x-3">
                            <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-2xl">
                                📐
                            </div>
                            <h2 class="text-2xl font-bold text-gray-800">ПТО - Мои расчеты</h2>
                        </div>
                        
                        <!-- Статус отдела -->
                        <div class="px-4 py-2 rounded-lg text-sm font-medium
                            @if($isApproved) bg-green-100 text-green-800 border border-green-300
                            @elseif($isRejected) bg-red-100 text-red-800 border border-red-300
                            @elseif($isSubmitted) bg-yellow-100 text-yellow-800 border border-yellow-300
                            @else bg-gray-100 text-gray-800 border border-gray-300
                            @endif">
                            @if($isApproved)
                                ✅ Утверждено
                            @elseif($isRejected)
                                🔄 Требуется доработка
                            @elseif($isSubmitted)
                                ⏳ На проверке
                            @else
                                📝 Черновик
                            @endif
                        </div>
                    </div>
                    
                    <!-- Комментарий при доработке -->
                    @if($isRejected && $project->pto_comment)
                        <div class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-xl">
                            <div class="flex items-start space-x-3">
                                <div class="text-red-500 text-xl">⚠️</div>
                                <div>
                                    <p class="font-medium text-red-700 mb-1">Комментарий директора к доработке:</p>
                                    <p class="text-red-600">"{{ $project->pto_comment }}"</p>
                                    <p class="text-sm text-red-500 mt-2">Загрузите исправленные файлы и отправьте заново.</p>
                                </div>
                            </div>
                        </div>
                    @endif
                    
                    <!-- Форма загрузки файлов (только если можно загружать) -->
                    @if($canUpload)
                        <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl border-2 border-blue-200">
                            <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                                </svg>
                                {{ $isRejected ? 'Загрузить исправленные файлы' : 'Загрузить файлы расчетов' }}
                            </h3>
                            <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data">
                                @csrf
                                <input type="hidden" name="section" value="pto">
                                <div class="flex flex-col space-y-3">
                                    <input type="file" name="file" required class="w-full border-2 border-blue-200 rounded-lg p-2 bg-white">
                                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center">
                                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                                        </svg>
                                        Загрузить файл
                                    </button>
                                </div>
                            </form>
                        </div>
                    @endif
                    
                    <!-- Список загруженных файлов -->
                    <h3 class="font-semibold text-gray-700 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Мои файлы
                    </h3>
                    
                    @if($userFiles->count() > 0)
                        <div class="space-y-3 mb-6">
                            @foreach($userFiles->sortByDesc('created_at') as $file)
                                <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:shadow-md transition" id="file-{{ $file->id }}">
                                    <div class="flex items-center space-x-4 flex-1">
                                        <span class="text-3xl">
                                            @php
                                                $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                                echo match($ext) {
                                                    'pdf' => '📕',
                                                    'doc', 'docx' => '📘',
                                                    'xls', 'xlsx' => '📊',
                                                    'jpg', 'jpeg', 'png', 'gif' => '🖼️',
                                                    default => '📄'
                                                };
                                            @endphp
                                        </span>
                                        <div class="flex-1">
                                            <a href="{{ Storage::url($file->file_path) }}" target="_blank" 
                                            class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                                {{ $file->file_name }}
                                            </a>
                                            <div class="flex items-center space-x-4 text-xs text-gray-500 mt-1">
                                                <span>📅 {{ \Carbon\Carbon::parse($file->created_at)->format('d.m.Y H:i') }}</span>
                                                <span>📦 {{ round($file->file_size / 1024, 2) }} KB</span>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    @if($canUpload)
                                        <form method="POST" action="{{ route('projects.files.delete', [$project, $file]) }}" 
                                            onsubmit="return confirm('Удалить этот файл?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-100 rounded-lg transition" title="Удалить">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                                </svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-10 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 mb-6">
                            <div class="text-5xl mb-3">📁</div>
                            <p class="text-gray-500">Нет загруженных файлов</p>
                            @if($canUpload)
                                <p class="text-sm text-gray-400 mt-2">Загрузите файлы используя форму выше</p>
                            @endif
                        </div>
                    @endif
                    
                    <!-- Кнопка отправки на проверку -->
                    @can('submitPTO', $project)
                        @if($userFiles->count() > 0 && !$isSubmitted)
                            <div class="mt-6 p-5 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-2 border-yellow-200">
                                <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    {{ $isRejected ? 'Отправить исправленные расчеты' : 'Отправить на проверку' }}
                                </h3>
                                <form method="POST" action="{{ route('projects.submit-pto', $project) }}">
                                    @csrf
                                    <div class="space-y-4">
                                        <textarea name="comment" rows="3" required 
                                                class="w-full border-2 border-yellow-200 rounded-lg p-3 focus:border-yellow-400 focus:ring focus:ring-yellow-200" 
                                                placeholder="{{ $isRejected ? 'Опишите что исправили...' : 'Опишите что за расчеты, на что обратить внимание...' }}"></textarea>
                                        <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                            </svg>
                                            {{ $isRejected ? 'Отправить исправленные расчеты' : 'Отправить на проверку' }}
                                        </button>
                                    </div>
                                </form>
                                <p class="text-xs text-gray-500 mt-3 text-center">
                                    После отправки вы не сможете изменять файлы до решения директора
                                </p>
                            </div>
                        @endif
                    @endcan
                    
                    <!-- Статусные сообщения -->
                    @if($isSubmitted && !$isApproved && !$isRejected)
                        <div class="mt-4 p-4 bg-yellow-100 rounded-lg flex items-center text-yellow-800">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ⏳ Расчеты отправлены на проверку. Ожидайте решения директора.
                        </div>
                    @endif
                    
                    @if($isApproved)
                        <div class="mt-4 p-4 bg-green-100 rounded-lg flex items-center text-green-800">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            ✅ Расчеты утверждены директором!
                        </div>
                    @endif
                </div>
            @endcan
            
            <!-- Кнопка отправки на общее согласование -->
            @if($ptoApproved && $supplyApproved && $project->status !== 'on_approval')
                <div class="mt-6 pt-4 border-t">
                    <form method="POST" action="{{ route('projects.send-to-approval', $project) }}">
                        @csrf
                        <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-4 px-6 rounded-xl font-semibold text-lg shadow-lg transition flex items-center justify-center">
                            <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Оба отдела утверждены → Отправить на общее согласование
                        </button>
                    </form>
                </div>
            @endif
            
            <!-- Если проект уже на согласовании -->
            @if($project->status === 'on_approval')
                <div class="mt-6 p-5 bg-yellow-50 rounded-xl border border-yellow-200">
                    <p class="text-yellow-700 font-medium text-lg mb-3 flex items-center">
                        <svg class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        Проект на общем согласовании
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <button onclick="openApproveModal()" class="bg-green-500 hover:bg-green-600 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Утвердить проект
                        </button>
                        <button onclick="openRejectModal()" class="bg-red-500 hover:bg-red-600 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            Отклонить проект
                        </button>
                    </div>
                </div>
            @endif
        </div>
    @endcan

    <!-- Участники проекта -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Участники проекта</h2>
            
            @can('manageParticipants', $project)
                <button onclick="openAddParticipantModal()" class="bg-blue-500 text-white px-3 py-1 rounded-md text-sm hover:bg-blue-600">
                    Добавить участника
                </button>
            @endcan
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            @foreach($project->participants as $participant)
                <div class="flex justify-between items-center p-3 bg-gray-50 rounded">
                    <div>
                        <p class="font-medium">{{ $participant->name }}</p>
                        <p class="text-sm text-gray-600">{{ $participant->pivot->role }}</p>
                    </div>
                    
                    @can('manageParticipants', $project)
                        @if(!$participant->isDeputyDirector())
                            <form method="POST" action="{{ route('projects.participants.remove', [$project, $participant]) }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-800 text-sm">
                                    Удалить
                                </button>
                            </form>
                        @endif
                    @endcan
                </div>
            @endforeach
        </div>
    </div>

    <!-- Специальные секции для ПТО и Снабжения -->
    @if(in_array(Auth::user()->role, ['pto', 'supply']))
        @php
            $userSection = Auth::user()->role === 'pto' ? 'pto' : 'supply';
            $sectionName = Auth::user()->role === 'pto' ? 'ПТО' : 'Снабжение';
            $userFiles = $project->files->where('section', $userSection)->where('user_id', Auth::id());
            
            // Проверяем статусы
            $isSubmitted = $userSection === 'pto' ? !is_null($project->pto_submitted_at) : !is_null($project->supply_submitted_at);
            $isApproved = $userSection === 'pto' ? $project->pto_approved === true : $project->supply_approved === true;
            $isRejected = $userSection === 'pto' ? $project->pto_approved === false : $project->supply_approved === false;
            
            // Можно работать (загружать/удалять файлы) если:
            // 1. Проект в расчете И (не отправляли ИЛИ на доработке)
            // 2. ИЛИ проект на доработке И этот отдел на доработке
            $canWork = ($project->status === 'in_calculation' && (!$isSubmitted || $isRejected)) || 
                    ($project->status === 'on_revision' && $isRejected);
            
            // Можно отправлять если:
            // 1. Есть файлы
            // 2. Не утверждено
            // 3. (Проект в расчете) ИЛИ (проект на доработке И этот отдел на доработке)
            $canSubmit = $userFiles->count() > 0 && 
                        !$isApproved && 
                        (($project->status === 'in_calculation') || 
                        ($project->status === 'on_revision' && $isRejected));
        @endphp
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full bg-blue-100 flex items-center justify-center text-2xl">
                        {{ $userSection === 'pto' ? '📐' : '📦' }}
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">{{ $sectionName }} - Расчеты</h2>
                </div>
                
                <!-- Статус отдела -->
                <div class="px-4 py-2 rounded-lg text-sm font-medium
                    @if($isApproved) bg-green-100 text-green-800 border border-green-300
                    @elseif($isRejected) bg-red-100 text-red-800 border border-red-300
                    @elseif($isSubmitted) bg-yellow-100 text-yellow-800 border border-yellow-300
                    @else bg-gray-100 text-gray-800 border border-gray-300
                    @endif">
                    @if($isApproved)
                        ✅ Утверждено
                    @elseif($isRejected)
                        🔄 Требуется доработка
                    @elseif($isSubmitted)
                        ⏳ На проверке
                    @else
                        📝 Черновик
                    @endif
                </div>
            </div>
            
            <!-- Комментарий при доработке -->
            @if($isRejected)
                <div class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <div class="text-red-500 text-xl">⚠️</div>
                        <div>
                            <p class="font-medium text-red-700 mb-1">Комментарий директора к доработке:</p>
                            <p class="text-red-600">"{{ $userSection === 'pto' ? $project->pto_comment : $project->supply_comment }}"</p>
                            <p class="text-sm text-red-500 mt-2">Загрузите исправленные файлы и отправьте заново.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Форма загрузки файлов -->
            @if($canWork)
                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl border-2 border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                        </svg>
                        {{ $isRejected ? 'Загрузить исправленные файлы' : 'Загрузить файлы расчетов' }}
                    </h3>
                    <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="section" value="{{ $userSection }}">
                        <div class="flex flex-col space-y-3">
                            <input type="file" name="file" required class="w-full border-2 border-blue-200 rounded-lg p-2 bg-white">
                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                                </svg>
                                Загрузить файл
                            </button>
                        </div>
                    </form>
                </div>
            @endif
            
            <!-- Список загруженных файлов -->
            <h3 class="font-semibold text-gray-700 mb-3 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Загруженные файлы
            </h3>
            
            @if($userFiles->count() > 0)
                <div class="space-y-3 mb-6">
                    @foreach($userFiles->sortByDesc('created_at') as $file)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:shadow-md transition">
                            <div class="flex items-center space-x-4 flex-1">
                                <span class="text-3xl">
                                    @php
                                        $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                        echo match($ext) {
                                            'pdf' => '📕',
                                            'doc', 'docx' => '📘',
                                            'xls', 'xlsx' => '📊',
                                            'jpg', 'jpeg', 'png', 'gif' => '🖼️',
                                            default => '📄'
                                        };
                                    @endphp
                                </span>
                                <div class="flex-1">
                                    <a href="{{ Storage::url($file->file_path) }}" target="_blank" 
                                    class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                        {{ $file->file_name }}
                                    </a>
                                    <div class="flex items-center space-x-4 text-xs text-gray-500 mt-1">
                                        <span>📅 {{ \Carbon\Carbon::parse($file->created_at)->format('d.m.Y H:i') }}</span>
                                        <span>📦 {{ round($file->file_size / 1024, 2) }} KB</span>
                                    </div>
                                </div>
                            </div>
                            
                            @if($canWork && !$isApproved)
                                <form method="POST" action="{{ route('projects.files.delete', [$project, $file]) }}" 
                                    onsubmit="return confirm('Удалить этот файл?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-100 rounded-lg transition" title="Удалить">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-10 bg-gray-50 rounded-xl border-2 border-dashed border-gray-300 mb-6">
                    <div class="text-5xl mb-3">📁</div>
                    <p class="text-gray-500">Нет загруженных файлов</p>
                    @if($canWork)
                        <p class="text-sm text-gray-400 mt-2">Загрузите файлы используя форму выше</p>
                    @endif
                </div>
            @endif
            
            <!-- Кнопка отправки на проверку -->
            @if($canSubmit)
                <div class="mt-6 p-5 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-2 border-yellow-200">
                    <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                        </svg>
                        {{ $isRejected ? 'Отправить исправленные расчеты' : 'Отправить на проверку' }}
                    </h3>
                    <form method="POST" action="{{ $userSection === 'pto' ? route('projects.submit-pto', $project) : route('projects.submit-supply', $project) }}">
                        @csrf
                        <div class="space-y-4">
                            <textarea name="comment" rows="3" required 
                                    class="w-full border-2 border-yellow-200 rounded-lg p-3 focus:border-yellow-400 focus:ring focus:ring-yellow-200" 
                                    placeholder="{{ $isRejected ? 'Опишите что исправили...' : 'Опишите что за расчеты, на что обратить внимание...' }}"></textarea>
                            <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                </svg>
                                {{ $isRejected ? 'Отправить исправленные расчеты' : 'Отправить на проверку' }}
                            </button>
                        </div>
                    </form>
                    <p class="text-xs text-gray-500 mt-3 text-center">
                        После отправки вы не сможете изменять файлы до решения директора
                    </p>
                </div>
            @endif
            
            <!-- Статусные сообщения -->
            @if($isSubmitted && !$isApproved && !$isRejected)
                <div class="mt-4 p-4 bg-yellow-100 rounded-lg flex items-center text-yellow-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ⏳ Расчеты отправлены на проверку. Ожидайте решения директора.
                </div>
            @endif
            
            @if($isApproved)
                <div class="mt-4 p-4 bg-green-100 rounded-lg flex items-center text-green-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ✅ Расчеты утверждены директором!
                </div>
            @endif
            
            @if($isRejected && $isSubmitted)
                <div class="mt-4 p-4 bg-orange-100 rounded-lg flex items-center text-orange-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ⏳ Исправленные расчеты отправлены. Ожидайте проверки.
                </div>
            @endif
        </div>
    @endif

    <!-- Вкладки для остальных пользователей -->
    @if(!in_array(Auth::user()->role, ['pto', 'supply']))
    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <div class="border-b border-gray-200">
            <nav class="flex -mb-px">
                <button onclick="showTab('files')" class="tab-button active px-6 py-3 text-sm font-medium text-blue-600 border-b-2 border-blue-600">
                    Файлы
                </button>
                <button onclick="showTab('comments')" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Комментарии
                </button>
                <button onclick="showTab('tasks')" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Задачи
                </button>
                <button onclick="showTab('materials')" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Материалы
                </button>
                <button onclick="showTab('financial')" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    Финансы
                </button>
                <button onclick="showTab('history')" class="tab-button px-6 py-3 text-sm font-medium text-gray-500 hover:text-gray-700">
                    История
                </button>
            </nav>
        </div>

        <!-- Файлы (для директора и других) -->
        <div id="files-tab" class="tab-content p-6">
            <!-- Фильтры -->
            <div class="mb-6 flex flex-wrap gap-2 border-b pb-4">
                <a href="{{ route('projects.show', ['project' => $project, 'filter' => 'all']) }}" 
                   class="px-4 py-2 rounded-md text-sm font-medium {{ request('filter', 'all') == 'all' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Все файлы
                </a>
                <a href="{{ route('projects.show', ['project' => $project, 'filter' => 'general']) }}" 
                   class="px-4 py-2 rounded-md text-sm font-medium {{ request('filter') == 'general' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Общий
                </a>
                <a href="{{ route('projects.show', ['project' => $project, 'filter' => 'pto']) }}" 
                   class="px-4 py-2 rounded-md text-sm font-medium {{ request('filter') == 'pto' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    ПТО
                </a>
                <a href="{{ route('projects.show', ['project' => $project, 'filter' => 'supply']) }}" 
                   class="px-4 py-2 rounded-md text-sm font-medium {{ request('filter') == 'supply' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700 hover:bg-gray-300' }}">
                    Снабжение
                </a>
            </div>

            <!-- Список файлов -->
            <div class="space-y-4">
                @php
                    $filter = request('filter', 'all');
                    $files = $project->files;
                    if ($filter != 'all') {
                        $files = $files->where('section', $filter);
                    }
                    $sortedFiles = $files->sortByDesc('created_at');
                    $filesByUser = $sortedFiles->groupBy('user_id');
                @endphp

                @forelse($filesByUser as $userId => $userFiles)
                    @php
                        $user = $userFiles->first()->user;
                        $totalSize = $userFiles->sum('file_size');
                        $fileCount = $userFiles->count();
                    @endphp
                    
                    <div class="border rounded-lg overflow-hidden">
                        <!-- Заголовок пользователя -->
                        <div class="bg-gray-100 px-4 py-3 flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                                <div>
                                    <h4 class="font-semibold">{{ $user->name }}</h4>
                                    <p class="text-xs text-gray-600">
                                        {{ $user->role }} • 
                                        {{ $fileCount }} {{ $fileCount == 1 ? 'файл' : ($fileCount < 5 ? 'файла' : 'файлов') }} • 
                                        {{ $totalSize > 1048576 ? round($totalSize / 1048576, 2) . ' MB' : round($totalSize / 1024, 2) . ' KB' }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Список файлов пользователя -->
                        <div class="bg-white divide-y divide-gray-200">
                            @foreach($userFiles as $file)
                                <div class="p-4 hover:bg-gray-50 transition">
                                    <div class="flex items-start space-x-4">
                                        <!-- Иконка -->
                                        <span class="text-3xl">
                                            @php
                                                $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                                                echo match($ext) {
                                                    'pdf' => '📕',
                                                    'doc', 'docx' => '📘',
                                                    'xls', 'xlsx', 'csv' => '📊',
                                                    'jpg', 'jpeg', 'png', 'gif', 'bmp' => '🖼️',
                                                    'zip', 'rar', '7z' => '🗜️',
                                                    'txt', 'md' => '📄',
                                                    default => '📎'
                                                };
                                            @endphp
                                        </span>
                                        
                                        <div class="flex-1">
                                            <div class="flex items-center flex-wrap gap-2">
                                                <a href="{{ Storage::url($file->file_path) }}" 
                                                   target="_blank" 
                                                   class="text-blue-600 hover:text-blue-800 hover:underline font-medium">
                                                    {{ $file->file_name }}
                                                </a>
                                                <span class="text-xs px-2 py-1 bg-gray-200 rounded-full">
                                                    @switch($file->section)
                                                        @case('general') Общий @break
                                                        @case('pto') ПТО @break
                                                        @case('supply') Снабжение @break
                                                        @default {{ $file->section }}
                                                    @endswitch
                                                </span>
                                            </div>
                                            <div class="flex items-center space-x-4 text-xs text-gray-500 mt-2">
                                                <span>Загружен: {{ $file->created_at ? \Carbon\Carbon::parse($file->created_at)->format('d.m.Y H:i') : '' }}</span>
                                                <span>Размер: {{ round($file->file_size / 1024, 2) }} KB</span>
                                            </div>
                                        </div>

                                        <div class="flex items-center space-x-2">
                                            <a href="{{ Storage::url($file->file_path) }}" 
                                               download="{{ $file->file_name }}"
                                               class="text-gray-600 hover:text-gray-800 p-2 hover:bg-gray-100 rounded-full"
                                               title="Скачать">
                                                ⬇️
                                            </a>
                                            
                                            @can('manageParticipants', $project)
                                                <form method="POST" action="{{ route('projects.files.delete', [$project, $file]) }}"
                                                      onsubmit="return confirm('Удалить этот файл?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-full" title="Удалить">
                                                        🗑️
                                                    </button>
                                                </form>
                                            @endcan
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @empty
                    <div class="text-center py-12 bg-gray-50 rounded-lg">
                        <div class="text-6xl mb-4">📁</div>
                        <h3 class="text-lg font-medium text-gray-900">Нет файлов</h3>
                        <p class="text-gray-500">В этом разделе нет файлов</p>
                    </div>
                @endforelse
            </div>
        </div>

        <!-- Комментарии -->
        <div id="comments-tab" class="tab-content p-6 hidden">
            <div class="mb-4">
                <form method="POST" action="{{ route('projects.comments', $project) }}">
                    @csrf
                    <div class="space-y-2">
                        <textarea name="content" rows="3" required class="w-full border rounded-md px-3 py-2" placeholder="Ваш комментарий..."></textarea>
                        <select name="section" class="border rounded px-3 py-2">
                            <option value="general">Общий</option>
                            <option value="pto">ПТО</option>
                            <option value="supply">Снабжение</option>
                        </select>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                            Отправить
                        </button>
                    </div>
                </form>
            </div>

            <div class="space-y-4">
                @foreach($project->comments as $comment)
                    <div class="border-b last:border-0 pb-4 last:pb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $comment->user->name }}</p>
                                <p class="text-sm text-gray-600">{{ $comment->content }}</p>
                            </div>
                            <div class="text-right">
                                <span class="text-xs px-2 py-1 bg-gray-200 rounded">{{ $comment->section }}</span>
                                <p class="text-xs text-gray-500 mt-1">{{ $comment->created_at ? \Carbon\Carbon::parse($comment->created_at)->format('d.m.Y H:i') : '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Задачи -->
        <div id="tasks-tab" class="tab-content p-6 hidden">
            @can('createTask', $project)
                <div class="mb-4">
                    <a href="{{ route('projects.tasks.create', $project) }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Создать задачу
                    </a>
                </div>
            @endcan

            <div class="space-y-4">
                @foreach($project->tasks as $task)
                    <div class="border rounded p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <a href="{{ route('projects.tasks.show', [$project, $task]) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                    {{ $task->title }}
                                </a>
                                <p class="text-sm text-gray-600 mt-1">{{ $task->description }}</p>
                                <p class="text-xs text-gray-500 mt-2">
                                    Исполнитель: {{ $task->assignee->name }}
                                </p>
                            </div>
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($task->status === 'sent') bg-yellow-100 text-yellow-800
                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ $task->status }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Материалы -->
        <div id="materials-tab" class="tab-content p-6 hidden">
            @can('createMaterial', $project)
                <div class="mb-4">
                    <a href="{{ route('projects.materials.create', $project) }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                        Добавить поставку
                    </a>
                </div>
            @endcan

            <div class="space-y-4">
                @foreach($project->materialDeliveries as $delivery)
                    <div class="border rounded p-4">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-semibold">{{ $delivery->material_name }}</p>
                                <p class="text-sm">Количество: {{ $delivery->quantity }} {{ $delivery->unit }}</p>
                                <p class="text-xs text-gray-500">
                                    Снабженец: {{ $delivery->supplyUser->name }}
                                </p>
                                @if($delivery->confirmed_date)
                                    <p class="text-xs text-gray-500">
                                        Подтверждено: {{ $delivery->confirmed_date ? \Carbon\Carbon::parse($delivery->confirmed_date)->format('d.m.Y') : '' }}
                                        @if($delivery->siteManagerUser)
                                            ({{ $delivery->siteManagerUser->name }})
                                        @endif
                                    </p>
                                @endif
                            </div>
                            <div class="text-right">
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($delivery->status === 'pending') bg-yellow-100 text-yellow-800
                                    @else bg-green-100 text-green-800
                                    @endif">
                                    {{ $delivery->status }}
                                </span>
                                @if($delivery->status === 'pending' && Auth::user()->isSiteManager())
                                    <form method="POST" action="{{ route('materials.confirm', $delivery) }}" enctype="multipart/form-data" class="mt-2">
                                        @csrf
                                        <input type="file" name="photo" accept="image/*" class="text-sm">
                                        <button type="submit" class="bg-green-500 text-white px-3 py-1 rounded text-sm hover:bg-green-600">
                                            Подтвердить
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Финансы -->
        <div id="financial-tab" class="tab-content p-6 hidden">
            @can('updateFinancial', $project)
                <div class="mb-4">
                    <form method="POST" action="{{ route('projects.financial.update', $project) }}">
                        @csrf
                        <div class="flex items-center space-x-2">
                            <select name="financial_status" required class="border rounded px-3 py-2">
                                <option value="pending_payment">На оплате</option>
                                <option value="paid">Оплачено</option>
                                <option value="not_paid">Не оплачено</option>
                            </select>
                            <input type="text" name="comment" placeholder="Комментарий" class="border rounded px-3 py-2 flex-1">
                            <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                                Обновить статус
                            </button>
                        </div>
                    </form>
                </div>
            @endcan

            <div class="space-y-2">
                @foreach($project->financialStatusLogs as $log)
                    <div class="p-3 bg-gray-50 rounded">
                        <div class="flex justify-between items-start">
                            <div>
                                <span class="px-2 py-1 text-xs rounded-full 
                                    @if($log->financial_status === 'pending_payment') bg-yellow-100 text-yellow-800
                                    @elseif($log->financial_status === 'paid') bg-green-100 text-green-800
                                    @else bg-red-100 text-red-800
                                    @endif">
                                    {{ $log->financial_status }}
                                </span>
                                @if($log->comment)
                                    <p class="text-sm mt-1">{{ $log->comment }}</p>
                                @endif
                            </div>
                            <div class="text-right">
                                <p class="text-xs">{{ $log->user->name }}</p>
                                <p class="text-xs text-gray-500">{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i') : '' }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- История -->
        <div id="history-tab" class="tab-content p-6 hidden">
            <div class="space-y-2">
                @foreach($project->statusLogs as $log)
                    <div class="p-3 bg-gray-50 rounded">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm">
                                    <span class="font-medium">{{ $log->user->name }}</span>
                                    изменил статус с 
                                    <span class="font-medium">{{ $log->old_status }}</span>
                                    на 
                                    <span class="font-medium">{{ $log->new_status }}</span>
                                </p>
                                @if($log->comment)
                                    <p class="text-sm text-gray-600 mt-1">Комментарий: {{ $log->comment }}</p>
                                @endif
                            </div>
                            <p class="text-xs text-gray-500">{{ $log->created_at ? \Carbon\Carbon::parse($log->created_at)->format('d.m.Y H:i') : '' }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Модальное окно для утверждения проекта -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Утверждение проекта</h3>
        <form method="POST" action="{{ route('projects.approve', $project) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий</label>
                <textarea name="comment" rows="3" required class="w-full border rounded-md px-3 py-2"></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeApproveModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600">
                    Утвердить
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для отклонения проекта -->
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Отклонение проекта</h3>
        <form method="POST" action="{{ route('projects.reject', $project) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Комментарий (обязательно)</label>
                <textarea name="comment" rows="3" required class="w-full border rounded-md px-3 py-2" placeholder="Укажите что нужно исправить..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeRejectModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                    Отклонить
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для отклонения ПТО -->
<div id="rejectPtoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Отправить ПТО на доработку</h3>
        <form method="POST" action="{{ route('projects.reject-pto', $project) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Что нужно исправить?</label>
                <textarea name="comment" rows="4" required class="w-full border rounded-md px-3 py-2" 
                          placeholder="Укажите что именно нужно доработать в расчетах ПТО..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeRejectPtoModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                    Отправить на доработку
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для отклонения Снабжения -->
<div id="rejectSupplyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Отправить снабжение на доработку</h3>
        <form method="POST" action="{{ route('projects.reject-supply', $project) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Что нужно исправить?</label>
                <textarea name="comment" rows="4" required class="w-full border rounded-md px-3 py-2" 
                          placeholder="Укажите что именно нужно доработать в расчетах снабжения..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeRejectSupplyModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600">
                    Отправить на доработку
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Модальное окно для добавления участника -->
<div id="addParticipantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Добавить участника</h3>
        <form method="POST" action="{{ route('projects.participants.add', $project) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Пользователь</label>
                <select name="user_id" required class="w-full border rounded-md px-3 py-2">
                    <option value="">Выберите пользователя</option>
                    @foreach($availableUsers as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                    @endforeach
                </select>
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Роль в проекте</label>
                <select name="role" required class="w-full border rounded-md px-3 py-2">
                    <option value="pto">ПТО</option>
                    <option value="supply">Снабжение</option>
                    <option value="project_manager">Руководитель проекта</option>
                    <option value="site_manager">Прораб</option>
                    <option value="accountant">Бухгалтер</option>
                </select>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeAddParticipantModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Добавить
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    function showTab(tabName) {
        // Скрываем все табы
        document.querySelectorAll('.tab-content').forEach(tab => {
            tab.classList.add('hidden');
        });
        
        // Показываем выбранный таб
        document.getElementById(tabName + '-tab').classList.remove('hidden');
        
        // Обновляем активную кнопку
        document.querySelectorAll('.tab-button').forEach(button => {
            button.classList.remove('active', 'text-blue-600', 'border-blue-600');
            button.classList.add('text-gray-500');
        });
        
        event.target.classList.add('active', 'text-blue-600', 'border-blue-600');
        event.target.classList.remove('text-gray-500');
    }

    function openApproveModal() {
        document.getElementById('approveModal').classList.remove('hidden');
        document.getElementById('approveModal').classList.add('flex');
    }

    function closeApproveModal() {
        document.getElementById('approveModal').classList.add('hidden');
        document.getElementById('approveModal').classList.remove('flex');
    }

    function openRejectModal() {
        document.getElementById('rejectModal').classList.remove('hidden');
        document.getElementById('rejectModal').classList.add('flex');
    }

    function closeRejectModal() {
        document.getElementById('rejectModal').classList.add('hidden');
        document.getElementById('rejectModal').classList.remove('flex');
    }

    function openRejectPtoModal() {
        document.getElementById('rejectPtoModal').classList.remove('hidden');
        document.getElementById('rejectPtoModal').classList.add('flex');
    }

    function closeRejectPtoModal() {
        document.getElementById('rejectPtoModal').classList.add('hidden');
        document.getElementById('rejectPtoModal').classList.remove('flex');
    }

    function openRejectSupplyModal() {
        document.getElementById('rejectSupplyModal').classList.remove('hidden');
        document.getElementById('rejectSupplyModal').classList.add('flex');
    }

    function closeRejectSupplyModal() {
        document.getElementById('rejectSupplyModal').classList.add('hidden');
        document.getElementById('rejectSupplyModal').classList.remove('flex');
    }

    function openAddParticipantModal() {
        document.getElementById('addParticipantModal').classList.remove('hidden');
        document.getElementById('addParticipantModal').classList.add('flex');
    }

    function closeAddParticipantModal() {
        document.getElementById('addParticipantModal').classList.add('hidden');
        document.getElementById('addParticipantModal').classList.remove('flex');
    }
</script>
@endsection
