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
            
            // Проверяем статус проекта
            $isInProgress = $project->status === 'in_progress';
            $isOnRevision = $project->status === 'on_revision';
            $isApproved = $project->status === 'approved';
            $isOnApproval = $project->status === 'on_approval';
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
                <!-- ПТО секция для директора -->
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
                
                <!-- Снабжение секция для директора -->
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

    <!-- Специальная секция для ПТО -->
    @can('uploadPTOFiles', $project)
        @php
            $userFiles = $project->files->where('section', 'pto')->where('user_id', Auth::id());
            $isSubmitted = !is_null($project->pto_submitted_at);
            $isApproved = $project->pto_approved === true;
            $isRejected = $project->pto_approved === false;
            $canUpload = !$isSubmitted || $isRejected;
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
            
            <!-- Форма загрузки файлов -->
            @if($canUpload)
                <div class="mb-6 bg-gradient-to-r from-blue-50 to-indigo-50 p-5 rounded-xl border-2 border-blue-200">
                    <h3 class="font-semibold text-blue-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                        </svg>
                        {{ $isRejected ? 'Загрузить исправленные файлы' : 'Загрузить файлы расчетов' }}
                    </h3>
                    
                    <!-- Форма с множественным выбором файлов -->
                    <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="pto">
                        
                        <div class="border-2 border-dashed border-blue-200 rounded-lg p-6 text-center hover:border-blue-400 transition">
                            <input type="file" 
                                name="files[]" 
                                id="pto-files" 
                                multiple 
                                class="hidden" 
                                onchange="updatePTOFileList(this)">
                            
                            <label for="pto-files" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mt-2 text-sm text-blue-600">Нажмите для выбора файлов</p>
                                <p class="text-xs text-gray-500">или перетащите их сюда</p>
                            </label>
                            
                            <!-- Список выбранных файлов -->
                            <div id="pto-file-list" class="mt-3 text-sm text-left max-h-32 overflow-y-auto"></div>
                        </div>
                        
                        <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                            </svg>
                            Загрузить {{ $isRejected ? 'исправленные файлы' : 'файлы' }} (можно несколько)
                        </button>
                    </form>
                    
                    <p class="text-xs text-gray-500 mt-2">Поддерживаются любые форматы файлов. Максимальный размер одного файла: 20MB</p>
                </div>
            @endif
            
            <!-- Список загруженных файлов с возможностью множественного выбора -->
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Мои файлы
                </h3>
                
                @if($userFiles->count() > 0 && $canUpload)
                    <div class="flex items-center space-x-2">
                        <!-- Кнопка выбора всех файлов -->
                        <button onclick="toggleSelectAll('pto')" class="text-sm text-blue-600 hover:text-blue-800 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Выбрать все
                        </button>
                        
                        <!-- Кнопка удаления выбранных -->
                        <button onclick="deleteSelectedFiles('pto')" 
                                class="text-sm text-red-600 hover:text-red-800 flex items-center px-3 py-1 bg-red-50 rounded-lg">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Удалить выбранные
                        </button>
                    </div>
                @endif
            </div>
            
            @if($userFiles->count() > 0)
                <div class="space-y-3 mb-6" id="pto-files-container">
                    @foreach($userFiles->sortByDesc('created_at') as $file)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:shadow-md transition group" id="file-{{ $file->id }}">
                            <div class="flex items-center space-x-4 flex-1">
                                <!-- Чекбокс для выбора -->
                                @if($canUpload)
                                    <input type="checkbox" 
                                        class="file-checkbox-pto w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                        data-file-id="{{ $file->id }}"
                                        data-section="pto">
                                @endif
                                
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
                                <button onclick="deleteSingleFile({{ $file->id }})" 
                                        class="text-red-600 hover:text-red-800 p-2 hover:bg-red-100 rounded-lg transition opacity-0 group-hover:opacity-100"
                                        title="Удалить">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
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
            @if($userFiles->count() > 0)
                @if(!$isSubmitted)
                    <!-- Если не отправлено -->
                    <div class="mt-6 p-5 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-2 border-yellow-200">
                        <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Отправить на проверку
                        </h3>
                        <form method="POST" action="{{ route('projects.submit-pto', $project) }}">
                            @csrf
                            <div class="space-y-4">
                                <textarea name="comment" rows="3" required 
                                        class="w-full border-2 border-yellow-200 rounded-lg p-3 focus:border-yellow-400 focus:ring focus:ring-yellow-200" 
                                        placeholder="Опишите что за расчеты, на что обратить внимание..."></textarea>
                                <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Отправить на проверку
                                </button>
                            </div>
                        </form>
                        <p class="text-xs text-gray-500 mt-3 text-center">
                            После отправки вы не сможете изменять файлы до решения директора
                        </p>
                    </div>
                @elseif($isRejected)
                    <!-- Если на доработке -->
                    <div class="mt-6 p-5 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-2 border-yellow-200">
                        <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Отправить исправленные расчеты
                        </h3>
                        <form method="POST" action="{{ route('projects.submit-pto', $project) }}">
                            @csrf
                            <div class="space-y-4">
                                <textarea name="comment" rows="3" required 
                                        class="w-full border-2 border-yellow-200 rounded-lg p-3 focus:border-yellow-400 focus:ring focus:ring-yellow-200" 
                                        placeholder="Опишите что исправили..."></textarea>
                                <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Отправить исправленные расчеты
                                </button>
                            </div>
                        </form>
                        <p class="text-xs text-gray-500 mt-3 text-center">
                            После отправки вы не сможете изменять файлы до решения директора
                        </p>
                    </div>
                @endif
            @endif
            
            <!-- Сообщение что на проверке -->
            @if($isSubmitted && !$isApproved && !$isRejected)
                <div class="mt-4 p-4 bg-yellow-100 rounded-lg flex items-center text-yellow-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ⏳ Расчеты отправлены на проверку. Ожидайте решения директора.
                </div>
            @endif
            
            <!-- Сообщение что утверждено -->
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

    <!-- Специальная секция для Снабжения -->
    @can('uploadSupplyFiles', $project)
        @php
            $userFiles = $project->files->where('section', 'supply')->where('user_id', Auth::id());
            $isSubmitted = !is_null($project->supply_submitted_at);
            $isApproved = $project->supply_approved === true;
            $isRejected = $project->supply_approved === false;
            $canUpload = !$isSubmitted || $isRejected;
        @endphp
        
        <div class="bg-white rounded-xl shadow-lg p-6">
            <div class="flex items-center justify-between mb-6">
                <div class="flex items-center space-x-3">
                    <div class="w-12 h-12 rounded-full bg-green-100 flex items-center justify-center text-2xl">
                        📦
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800">Снабжение - Мои сметы</h2>
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
            @if($isRejected && $project->supply_comment)
                <div class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-xl">
                    <div class="flex items-start space-x-3">
                        <div class="text-red-500 text-xl">⚠️</div>
                        <div>
                            <p class="font-medium text-red-700 mb-1">Комментарий директора к доработке:</p>
                            <p class="text-red-600">"{{ $project->supply_comment }}"</p>
                            <p class="text-sm text-red-500 mt-2">Загрузите исправленные файлы и отправьте заново.</p>
                        </div>
                    </div>
                </div>
            @endif
            
            <!-- Форма загрузки файлов -->
            @if($canUpload)
                <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 p-5 rounded-xl border-2 border-green-200">
                    <h3 class="font-semibold text-green-800 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                        </svg>
                        {{ $isRejected ? 'Загрузить исправленные файлы' : 'Загрузить файлы смет' }}
                    </h3>
                    
                    <!-- Форма с множественным выбором файлов -->
                    <form method="POST" action="{{ route('projects.files.upload', $project) }}" enctype="multipart/form-data" class="space-y-4">
                        @csrf
                        <input type="hidden" name="section" value="supply">
                        
                        <div class="border-2 border-dashed border-green-200 rounded-lg p-6 text-center hover:border-green-400 transition">
                            <input type="file" 
                                name="files[]" 
                                id="supply-files" 
                                multiple 
                                class="hidden" 
                                onchange="updateSupplyFileList(this)">
                            
                            <label for="supply-files" class="cursor-pointer">
                                <svg class="mx-auto h-12 w-12 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                                </svg>
                                <p class="mt-2 text-sm text-green-600">Нажмите для выбора файлов</p>
                                <p class="text-xs text-gray-500">или перетащите их сюда</p>
                            </label>
                            
                            <!-- Список выбранных файлов -->
                            <div id="supply-file-list" class="mt-3 text-sm text-left max-h-32 overflow-y-auto"></div>
                        </div>
                        
                        <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white py-2 px-4 rounded-lg transition flex items-center justify-center">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
                            </svg>
                            Загрузить {{ $isRejected ? 'исправленные файлы' : 'файлы' }} (можно несколько)
                        </button>
                    </form>
                    
                    <p class="text-xs text-gray-500 mt-2">Поддерживаются любые форматы файлов. Максимальный размер одного файла: 20MB</p>
                </div>
            @endif
            
            <!-- Список загруженных файлов с возможностью множественного выбора -->
            <div class="flex items-center justify-between mb-3">
                <h3 class="font-semibold text-gray-700 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                    </svg>
                    Мои файлы
                </h3>
                
                @if($userFiles->count() > 0 && $canUpload)
                    <div class="flex items-center space-x-2">
                        <!-- Кнопка выбора всех файлов -->
                        <button onclick="toggleSelectAll('supply')" class="text-sm text-green-600 hover:text-green-800 flex items-center">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                            </svg>
                            Выбрать все
                        </button>
                        
                        <!-- Кнопка удаления выбранных -->
                        <button onclick="deleteSelectedFiles('supply')" 
                                class="text-sm text-red-600 hover:text-red-800 flex items-center px-3 py-1 bg-red-50 rounded-lg">
                            <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                            </svg>
                            Удалить выбранные
                        </button>
                    </div>
                @endif
            </div>
            
            @if($userFiles->count() > 0)
                <div class="space-y-3 mb-6" id="supply-files-container">
                    @foreach($userFiles->sortByDesc('created_at') as $file)
                        <div class="flex items-center justify-between p-4 bg-gray-50 rounded-lg border hover:shadow-md transition group" id="file-{{ $file->id }}">
                            <div class="flex items-center space-x-4 flex-1">
                                <!-- Чекбокс для выбора -->
                                @if($canUpload)
                                    <input type="checkbox" 
                                        class="file-checkbox-supply w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500"
                                        data-file-id="{{ $file->id }}"
                                        data-section="supply">
                                @endif
                                
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
                                <button onclick="deleteSingleFile({{ $file->id }})" 
                                        class="text-red-600 hover:text-red-800 p-2 hover:bg-red-100 rounded-lg transition opacity-0 group-hover:opacity-100"
                                        title="Удалить">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                    </svg>
                                </button>
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
            @if($userFiles->count() > 0)
                @if(!$isSubmitted)
                    <!-- Если не отправлено -->
                    <div class="mt-6 p-5 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-2 border-yellow-200">
                        <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Отправить на проверку
                        </h3>
                        <form method="POST" action="{{ route('projects.submit-supply', $project) }}">
                            @csrf
                            <div class="space-y-4">
                                <textarea name="comment" rows="3" required 
                                        class="w-full border-2 border-yellow-200 rounded-lg p-3 focus:border-yellow-400 focus:ring focus:ring-yellow-200" 
                                        placeholder="Опишите что за сметы, на что обратить внимание..."></textarea>
                                <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Отправить на проверку
                                </button>
                            </div>
                        </form>
                        <p class="text-xs text-gray-500 mt-3 text-center">
                            После отправки вы не сможете изменять файлы до решения директора
                        </p>
                    </div>
                @elseif($isRejected)
                    <!-- Если на доработке -->
                    <div class="mt-6 p-5 bg-gradient-to-r from-yellow-50 to-amber-50 rounded-xl border-2 border-yellow-200">
                        <h3 class="font-semibold text-yellow-800 mb-3 flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                            </svg>
                            Отправить исправленные сметы
                        </h3>
                        <form method="POST" action="{{ route('projects.submit-supply', $project) }}">
                            @csrf
                            <div class="space-y-4">
                                <textarea name="comment" rows="3" required 
                                        class="w-full border-2 border-yellow-200 rounded-lg p-3 focus:border-yellow-400 focus:ring focus:ring-yellow-200" 
                                        placeholder="Опишите что исправили..."></textarea>
                                <button type="submit" class="w-full bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 text-white py-3 px-4 rounded-lg font-medium transition flex items-center justify-center">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"></path>
                                    </svg>
                                    Отправить исправленные сметы
                                </button>
                            </div>
                        </form>
                        <p class="text-xs text-gray-500 mt-3 text-center">
                            После отправки вы не сможете изменять файлы до решения директора
                        </p>
                    </div>
                @endif
            @endif
            
            <!-- Сообщение что на проверке -->
            @if($isSubmitted && !$isApproved && !$isRejected)
                <div class="mt-4 p-4 bg-yellow-100 rounded-lg flex items-center text-yellow-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ⏳ Сметы отправлены на проверку. Ожидайте решения директора.
                </div>
            @endif
            
            <!-- Сообщение что утверждено -->
            @if($isApproved)
                <div class="mt-4 p-4 bg-green-100 rounded-lg flex items-center text-green-800">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    ✅ Сметы утверждены директором!
                </div>
            @endif
        </div>
    @endcan

    <!-- Вкладки для всех пользователей -->
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

        <!-- Файлы -->
        <div id="files-tab" class="tab-content p-6">
            <!-- Панель инструментов -->
            <div class="mb-6 bg-white rounded-lg shadow-sm p-4 border border-gray-200">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center space-x-4">
                        <h3 class="text-lg font-semibold text-gray-800">📁 Файлы проекта</h3>
                        
                        <!-- Фильтры в виде красивых кнопок -->
                        <div class="flex bg-gray-100 rounded-lg p-1">
                            <button onclick="filterFiles('all')" 
                                    class="filter-btn px-4 py-2 rounded-md text-sm font-medium transition-all duration-200 
                                        @if(request('filter', 'all') == 'all') bg-blue-500 text-white shadow-md @else text-gray-700 hover:bg-gray-200 @endif">
                                Все
                            </button>
                            <button onclick="filterFiles('general')" 
                                    class="filter-btn px-4 py-2 rounded-md text-sm font-medium transition-all duration-200
                                        @if(request('filter') == 'general') bg-blue-500 text-white shadow-md @else text-gray-700 hover:bg-gray-200 @endif">
                                Общие
                            </button>
                            <button onclick="filterFiles('pto')" 
                                    class="filter-btn px-4 py-2 rounded-md text-sm font-medium transition-all duration-200
                                        @if(request('filter') == 'pto') bg-blue-500 text-white shadow-md @else text-gray-700 hover:bg-gray-200 @endif">
                                ПТО
                            </button>
                            <button onclick="filterFiles('supply')" 
                                    class="filter-btn px-4 py-2 rounded-md text-sm font-medium transition-all duration-200
                                        @if(request('filter') == 'supply') bg-blue-500 text-white shadow-md @else text-gray-700 hover:bg-gray-200 @endif">
                                Снабжение
                            </button>
                        </div>
                    </div>
                    
                    <!-- Поиск и сортировка -->
                    <div class="flex items-center space-x-2">
                        <div class="relative">
                            <input type="text" 
                                id="file-search" 
                                placeholder="Поиск файлов..." 
                                class="pl-8 pr-4 py-2 border rounded-lg text-sm focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                            <svg class="absolute left-2 top-2.5 h-4 w-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                            </svg>
                        </div>
                        
                        <select id="sort-files" class="border rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-blue-500">
                            <option value="date_desc">Сначала новые</option>
                            <option value="date_asc">Сначала старые</option>
                            <option value="name_asc">По имени (А-Я)</option>
                            <option value="name_desc">По имени (Я-А)</option>
                            <option value="size_desc">По размеру (сначала большие)</option>
                            <option value="size_asc">По размеру (сначала маленькие)</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Список файлов -->
            <div id="files-list" class="space-y-4">
                @include('projects.partials.files-list', [
                    'filesByUser' => $project->files->groupBy('user_id'), 
                    'project' => $project
                ])
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

                <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const userSelect = document.querySelector('select[name="user_id"]');
                    const roleSelect = document.querySelector('select[name="role"]');
                    
                    if (userSelect && roleSelect) {
                        userSelect.addEventListener('change', function() {
                            const selected = this.selectedOptions[0];
                            if (selected && selected.dataset.role) {
                                // Находим опцию с соответствующей ролью и выбираем её
                                Array.from(roleSelect.options).forEach(option => {
                                    if (option.value === selected.dataset.role) {
                                        option.selected = true;
                                    }
                                });
                            }
                        });
                    }
                });
                </script>
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

<script>
// Функции для отображения выбранных файлов в ПТО секции
function updatePTOFileList(input) {
    const fileList = document.getElementById('pto-file-list');
    displaySelectedFiles(input, fileList);
}

// Функции для отображения выбранных файлов в Снабжении
function updateSupplyFileList(input) {
    const fileList = document.getElementById('supply-file-list');
    displaySelectedFiles(input, fileList);
}

// Общая функция для отображения списка файлов
function displaySelectedFiles(input, fileListElement) {
    fileListElement.innerHTML = '';
    
    if (input.files.length > 0) {
        const list = document.createElement('ul');
        list.className = 'list-disc list-inside space-y-1';
        
        for (let i = 0; i < input.files.length; i++) {
            const file = input.files[i];
            const li = document.createElement('li');
            li.className = 'text-gray-600 text-xs';
            
            // Форматируем размер файла
            let fileSize = file.size;
            let sizeText = '';
            if (fileSize < 1024) {
                sizeText = fileSize + ' B';
            } else if (fileSize < 1024 * 1024) {
                sizeText = (fileSize / 1024).toFixed(1) + ' KB';
            } else {
                sizeText = (fileSize / (1024 * 1024)).toFixed(1) + ' MB';
            }
            
            li.textContent = `${file.name} (${sizeText})`;
            list.appendChild(li);
        }
        
        fileListElement.appendChild(list);
        
        // Добавляем информацию о количестве файлов
        const countInfo = document.createElement('p');
        countInfo.className = 'text-xs text-blue-600 mt-2 font-medium';
        countInfo.textContent = `Выбрано файлов: ${input.files.length}`;
        fileListElement.appendChild(countInfo);
    } else {
        fileListElement.innerHTML = '<p class="text-xs text-gray-400">Файлы не выбраны</p>';
    }
}

// Поддержка drag & drop
document.addEventListener('DOMContentLoaded', function() {
    // Для ПТО
    const ptoDropZone = document.querySelector('#pto-files')?.closest('.border-2');
    if (ptoDropZone) {
        setupDragAndDrop(ptoDropZone, 'pto-files');
    }
    
    // Для Снабжения
    const supplyDropZone = document.querySelector('#supply-files')?.closest('.border-2');
    if (supplyDropZone) {
        setupDragAndDrop(supplyDropZone, 'supply-files');
    }
});

function setupDragAndDrop(dropZone, inputId) {
    const input = document.getElementById(inputId);
    
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
        e.preventDefault();
        e.stopPropagation();
    }
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
        dropZone.classList.add('border-blue-400', 'bg-blue-50');
    }
    
    function unhighlight() {
        dropZone.classList.remove('border-blue-400', 'bg-blue-50');
    }
    
    dropZone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
        const dt = e.dataTransfer;
        const files = dt.files;
        input.files = files;
        
        // Обновляем список файлов
        const event = new Event('change', { bubbles: true });
        input.dispatchEvent(event);
    }
}
</script>

<script>
    // Переменные для хранения всех файловых элементов
    let allFileItems = [];
    let currentFilter = 'all';

    // Функция для фильтрации файлов
    function filterFiles(filter) {
        currentFilter = filter;
        
        // Обновляем стили кнопок
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.classList.remove('bg-blue-500', 'text-white', 'shadow-md');
            btn.classList.add('text-gray-700', 'hover:bg-gray-200');
        });
        event.target.classList.remove('text-gray-700', 'hover:bg-gray-200');
        event.target.classList.add('bg-blue-500', 'text-white', 'shadow-md');
        
        // Применяем фильтр
        applyFilters();
    }

    // Функция поиска
    document.getElementById('file-search')?.addEventListener('input', function(e) {
        applyFilters();
    });

    // Функция сортировки
    document.getElementById('sort-files')?.addEventListener('change', function(e) {
        applyFilters();
    });

    // Основная функция применения всех фильтров
    function applyFilters() {
        const searchTerm = document.getElementById('file-search')?.value.toLowerCase() || '';
        const sortBy = document.getElementById('sort-files')?.value || 'date_desc';
        
        // Получаем все группы файлов
        const groups = document.querySelectorAll('.file-group');
        
        groups.forEach(group => {
            const files = group.querySelectorAll('.file-item');
            let visibleCount = 0;
            
            files.forEach(file => {
                const fileName = file.dataset.filename || '';
                const fileSection = file.querySelector('.text-xs.px-2.py-1').textContent.includes('ПТО') ? 'pto' : 
                                (file.querySelector('.text-xs.px-2.py-1').textContent.includes('Снабжение') ? 'supply' : 'general');
                
                // Проверяем фильтр
                const matchesFilter = currentFilter === 'all' || fileSection === currentFilter;
                
                // Проверяем поиск
                const matchesSearch = fileName.includes(searchTerm);
                
                if (matchesFilter && matchesSearch) {
                    file.style.display = '';
                    visibleCount++;
                } else {
                    file.style.display = 'none';
                }
            });
            
            // Скрываем группу, если в ней нет видимых файлов
            if (visibleCount === 0) {
                group.style.display = 'none';
            } else {
                group.style.display = '';
            }
        });
        
        // Применяем сортировку
        sortFiles(sortBy);
    }

    // Функция сортировки файлов
    function sortFiles(sortBy) {
        const groups = document.querySelectorAll('.file-group');
        
        groups.forEach(group => {
            const filesContainer = group.querySelector('[id^="files-"]');
            const files = Array.from(filesContainer.querySelectorAll('.file-item'));
            
            files.sort((a, b) => {
                const aVal = a.dataset[sortBy.split('_')[0]];
                const bVal = b.dataset[sortBy.split('_')[0]];
                const order = sortBy.split('_')[1] === 'asc' ? 1 : -1;
                
                if (sortBy.startsWith('name')) {
                    return order * aVal.localeCompare(bVal);
                } else {
                    return order * (parseInt(aVal) - parseInt(bVal));
                }
            });
            
            // Переставляем элементы
            files.forEach(file => filesContainer.appendChild(file));
        });
    }

    // Функция для сворачивания/разворачивания файлов пользователя
    function toggleUserFiles(userId) {
        const filesDiv = document.getElementById(`files-${userId}`);
        const arrow = document.getElementById(`arrow-${userId}`);
        
        if (filesDiv.style.display === 'none') {
            filesDiv.style.display = '';
            arrow.style.transform = 'rotate(0deg)';
        } else {
            filesDiv.style.display = 'none';
            arrow.style.transform = 'rotate(-90deg)';
        }
    }

    // Функция удаления файла
    function deleteFile(fileId) {
        if (!confirm('Удалить этот файл?')) return;
        
        const projectId = {{ $project->id }};
        
        fetch(`/projects/${projectId}/files/${fileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fileElement = document.getElementById(`file-${fileId}`);
                if (fileElement) {
                    const group = fileElement.closest('.file-group');
                    fileElement.remove();
                    
                    // Если в группе не осталось файлов, обновляем счетчик или удаляем группу
                    const remainingFiles = group.querySelectorAll('.file-item').length;
                    if (remainingFiles === 0) {
                        group.remove();
                    }
                }
            }
        });
    }

    // Инициализация при загрузке
    document.addEventListener('DOMContentLoaded', function() {
        // Собираем данные о файлах
        document.querySelectorAll('.file-item').forEach(file => {
            allFileItems.push({
                element: file,
                filename: file.dataset.filename || '',
                date: parseInt(file.dataset.date) || 0,
                size: parseInt(file.dataset.size) || 0
            });
        });
        
        // Добавляем поддержку Enter в поиске
        const searchInput = document.getElementById('file-search');
        if (searchInput) {
            searchInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    applyFilters();
                }
            });
        }
    });
</script>
<script>
    // Функции для работы с файлами ПТО
    let selectedPTOFiles = new Set();
    let selectedSupplyFiles = new Set();

    // Функция для обновления списка выбранных файлов
    function toggleFileSelection(checkbox, section) {
        const fileId = checkbox.dataset.fileId;
        if (checkbox.checked) {
            if (section === 'pto') {
                selectedPTOFiles.add(fileId);
            } else {
                selectedSupplyFiles.add(fileId);
            }
        } else {
            if (section === 'pto') {
                selectedPTOFiles.delete(fileId);
            } else {
                selectedSupplyFiles.delete(fileId);
            }
        }
        
        // Обновляем кнопку удаления
        updateDeleteButton(section);
    }

    // Функция для выбора всех файлов
    function toggleSelectAll(section) {
        const checkboxes = document.querySelectorAll(`.file-checkbox-${section}`);
        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
        const selectedSet = section === 'pto' ? selectedPTOFiles : selectedSupplyFiles;
        
        // Очищаем текущий набор
        selectedSet.clear();
        
        checkboxes.forEach(cb => {
            cb.checked = !allChecked;
            const fileId = cb.dataset.fileId;
            
            if (!allChecked) {
                selectedSet.add(fileId);
            }
        });
        
        updateDeleteButton(section);
    }

    // Функция для обновления состояния кнопки удаления
    function updateDeleteButton(section) {
        const selectedCount = section === 'pto' ? selectedPTOFiles.size : selectedSupplyFiles.size;
        const deleteBtn = section === 'pto' 
            ? document.querySelector('button[onclick="deleteSelectedFiles(\'pto\')"]')
            : document.querySelector('button[onclick="deleteSelectedFiles(\'supply\')"]');
        
        if (deleteBtn) {
            if (selectedCount > 0) {
                deleteBtn.classList.remove('opacity-50', 'pointer-events-none');
                deleteBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Удалить выбранные (${selectedCount})
                `;
            } else {
                deleteBtn.classList.add('opacity-50', 'pointer-events-none');
                deleteBtn.innerHTML = `
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Удалить выбранные
                `;
            }
        }
    }

    // Функция для удаления выбранных файлов
    function deleteSelectedFiles(section) {
        const selectedSet = section === 'pto' ? selectedPTOFiles : selectedSupplyFiles;
        const fileIds = Array.from(selectedSet);
        
        if (fileIds.length === 0) return;
        
        if (!confirm(`Удалить ${fileIds.length} выбранных файлов?`)) return;
        
        const projectId = {{ $project->id }};
        let deletedCount = 0;
        
        // Удаляем файлы по одному
        fileIds.forEach(fileId => {
            fetch(`/projects/${projectId}/files/${fileId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Удаляем элемент из DOM
                    const fileElement = document.getElementById(`file-${fileId}`);
                    if (fileElement) {
                        fileElement.remove();
                    }
                    
                    deletedCount++;
                    selectedSet.delete(fileId);
                    
                    // Если все файлы удалены, обновляем интерфейс
                    if (deletedCount === fileIds.length) {
                        updateDeleteButton(section);
                        
                        // Проверяем, остались ли файлы в контейнере
                        const container = document.getElementById(`${section}-files-container`);
                        if (container && container.children.length === 0) {
                            // Показываем сообщение о пустом списке
                            location.reload(); // Перезагружаем для обновления всего блока
                        } else {
                            alert('Файлы успешно удалены');
                        }
                    }
                }
            })
            .catch(error => {
                console.error('Ошибка при удалении файла:', error);
            });
        });
    }

    // Функция для удаления одного файла
    function deleteSingleFile(fileId) {
        if (!confirm('Удалить этот файл?')) return;
        
        const projectId = {{ $project->id }};
        
        fetch(`/projects/${projectId}/files/${fileId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const fileElement = document.getElementById(`file-${fileId}`);
                if (fileElement) {
                    fileElement.remove();
                    
                    // Удаляем из набора выбранных, если был выбран
                    selectedPTOFiles.delete(fileId.toString());
                    selectedSupplyFiles.delete(fileId.toString());
                    updateDeleteButton('pto');
                    updateDeleteButton('supply');
                    
                    // Проверяем, остались ли файлы
                    const ptoContainer = document.getElementById('pto-files-container');
                    const supplyContainer = document.getElementById('supply-files-container');
                    
                    if ((ptoContainer && ptoContainer.children.length === 0) || 
                        (supplyContainer && supplyContainer.children.length === 0)) {
                        // Перезагружаем для обновления UI
                        location.reload();
                    }
                }
            } else {
                alert('Ошибка при удалении файла');
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
            alert('Произошла ошибка при удалении файла');
        });
    }

    // Функции для отображения выбранных файлов при загрузке
    function updatePTOFileList(input) {
        const fileList = document.getElementById('pto-file-list');
        displaySelectedFiles(input, fileList);
    }

    function updateSupplyFileList(input) {
        const fileList = document.getElementById('supply-file-list');
        displaySelectedFiles(input, fileList);
    }

    function displaySelectedFiles(input, fileListElement) {
        fileListElement.innerHTML = '';
        
        if (input.files.length > 0) {
            const list = document.createElement('ul');
            list.className = 'list-disc list-inside space-y-1';
            
            for (let i = 0; i < input.files.length; i++) {
                const file = input.files[i];
                const li = document.createElement('li');
                li.className = 'text-gray-600 text-xs';
                
                let fileSize = file.size;
                let sizeText = '';
                if (fileSize < 1024) {
                    sizeText = fileSize + ' B';
                } else if (fileSize < 1024 * 1024) {
                    sizeText = (fileSize / 1024).toFixed(1) + ' KB';
                } else {
                    sizeText = (fileSize / (1024 * 1024)).toFixed(1) + ' MB';
                }
                
                li.textContent = `${file.name} (${sizeText})`;
                list.appendChild(li);
            }
            
            fileListElement.appendChild(list);
            
            const countInfo = document.createElement('p');
            countInfo.className = 'text-xs text-blue-600 mt-2 font-medium';
            countInfo.textContent = `Выбрано файлов: ${input.files.length}`;
            fileListElement.appendChild(countInfo);
        } else {
            fileListElement.innerHTML = '<p class="text-xs text-gray-400">Файлы не выбраны</p>';
        }
    }

    // Инициализация при загрузке страницы
    document.addEventListener('DOMContentLoaded', function() {
        console.log('DOM loaded, initializing file handlers');
        
        // Добавляем обработчики для чекбоксов ПТО
        document.querySelectorAll('.file-checkbox-pto').forEach(cb => {
            cb.addEventListener('change', function() {
                toggleFileSelection(this, 'pto');
            });
        });
        
        // Добавляем обработчики для чекбоксов Снабжения
        document.querySelectorAll('.file-checkbox-supply').forEach(cb => {
            cb.addEventListener('change', function() {
                toggleFileSelection(this, 'supply');
            });
        });
        
        // Инициализация кнопок удаления
        updateDeleteButton('pto');
        updateDeleteButton('supply');
        
        // Добавляем обработчики для кнопок удаления одного файла
        document.querySelectorAll('[onclick^="deleteSingleFile"]').forEach(btn => {
            const originalClick = btn.onclick;
            btn.onclick = function(e) {
                e.preventDefault();
                const fileId = this.getAttribute('onclick').match(/\d+/)[0];
                deleteSingleFile(fileId);
            };
        });
    });

    // Для отладки - выводим в консоль
    window.deleteSingleFile = deleteSingleFile;
    window.deleteSelectedFiles = deleteSelectedFiles;
</script>
@endsection