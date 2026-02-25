{{-- resources/views/projects/partials/files-list.blade.php --}}
@forelse($filesByUser as $userId => $userFiles)
    @php
        $user = $userFiles->first()->user;
        $totalSize = $userFiles->sum('file_size');
        $fileCount = $userFiles->count();
        
        // Определяем иконку для пользователя
        $userIcon = match($user->role) {
            'director' => '👑',
            'deputy_director' => '⭐',
            'pto' => '📐',
            'supply' => '📦',
            'project_manager' => '📋',
            'site_manager' => '🔧',
            'accountant' => '💰',
            default => '👤'
        };
        
        // Цвет для роли
        $roleColor = match($user->role) {
            'director' => 'bg-purple-100 text-purple-800',
            'deputy_director' => 'bg-indigo-100 text-indigo-800',
            'pto' => 'bg-blue-100 text-blue-800',
            'supply' => 'bg-green-100 text-green-800',
            'project_manager' => 'bg-yellow-100 text-yellow-800',
            'site_manager' => 'bg-orange-100 text-orange-800',
            'accountant' => 'bg-emerald-100 text-emerald-800',
            default => 'bg-gray-100 text-gray-800'
        };
    @endphp
    
    <div class="border rounded-xl overflow-hidden transition-all hover:shadow-lg bg-white file-group" data-user="{{ $user->id }}">
        <!-- Заголовок пользователя -->
        <div class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-2xl shadow-md">
                    <span class="text-white">{{ $userIcon }}</span>
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h4 class="font-bold text-gray-800">{{ $user->name }}</h4>
                        <span class="px-2 py-0.5 text-xs rounded-full {{ $roleColor }}">{{ $user->role }}</span>
                    </div>
                    <div class="flex items-center space-x-3 text-xs text-gray-500 mt-1">
                        <span class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                            </svg>
                            {{ $fileCount }} {{ $fileCount == 1 ? 'файл' : ($fileCount < 5 ? 'файла' : 'файлов') }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4m16 0a8 8 0 01-8 8m8-8a8 8 0 00-8-8"></path>
                            </svg>
                            {{ $totalSize > 1048576 ? round($totalSize / 1048576, 2) . ' MB' : round($totalSize / 1024, 2) . ' KB' }}
                        </span>
                    </div>
                </div>
            </div>
            
            <!-- Кнопка свернуть/развернуть -->
            <button onclick="toggleUserFiles({{ $user->id }})" class="text-gray-400 hover:text-gray-600 transition">
                <svg class="w-6 h-6 transform transition-transform duration-200" id="arrow-{{ $user->id }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Список файлов пользователя -->
        <div id="files-{{ $user->id }}" class="bg-white divide-y divide-gray-100">
            @foreach($userFiles->sortByDesc('created_at') as $file)
                <div class="p-4 hover:bg-gray-50 transition file-item group" 
                     id="file-{{ $file->id }}"
                     data-filename="{{ strtolower($file->file_name) }}"
                     data-date="{{ $file->created_at ? $file->created_at->timestamp : 0 }}"
                     data-size="{{ $file->file_size }}"
                     data-section="{{ $file->section }}">
                    <div class="flex items-start space-x-4">
                        <!-- Чекбокс для выбора (только для своих файлов) -->
                        @if($file->user_id === Auth::id())
                            <div class="pt-2">
                                <input type="checkbox" 
                                       class="file-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500"
                                       data-file-id="{{ $file->id }}"
                                       onchange="updateSelectedCount()">
                            </div>
                        @endif
                        
                        <!-- Иконка файла -->
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
                                    'dwg', 'dxf' => '📐',
                                    'exe' => '⚙️',
                                    default => '📎'
                                };
                            @endphp
                        </span>
                        
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center flex-wrap gap-2">
                                <a href="{{ Storage::url($file->file_path) }}" 
                                   target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 hover:underline font-medium text-lg">
                                    {{ $file->file_name }}
                                </a>
                                <span class="text-xs px-2 py-1 rounded-full 
                                    @if($file->section === 'pto') bg-blue-100 text-blue-700
                                    @elseif($file->section === 'supply') bg-green-100 text-green-700
                                    @else bg-gray-100 text-gray-700
                                    @endif">
                                    @switch($file->section)
                                        @case('general') 📁 Общий @break
                                        @case('pto') 📐 ПТО @break
                                        @case('supply') 📦 Снабжение @break
                                        @default {{ $file->section }}
                                    @endswitch
                                </span>
                            </div>
                            
                            <div class="flex items-center space-x-6 text-xs text-gray-500 mt-2">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $file->created_at ? \Carbon\Carbon::parse($file->created_at)->format('d.m.Y H:i') : '' }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"></path>
                                    </svg>
                                    {{ $file->file_size > 1048576 ? round($file->file_size / 1048576, 2) . ' MB' : round($file->file_size / 1024, 2) . ' KB' }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                                    </svg>
                                    {{ $file->user->name }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-2">
                            <!-- Кнопка просмотра -->
                            <a href="{{ Storage::url($file->file_path) }}" 
                               target="_blank"
                               class="text-gray-400 hover:text-blue-600 p-2 hover:bg-blue-50 rounded-lg transition"
                               title="Просмотр">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path>
                                </svg>
                            </a>
                            
                            <!-- Кнопка скачивания -->
                            <a href="{{ Storage::url($file->file_path) }}" 
                               download="{{ $file->file_name }}"
                               class="text-gray-400 hover:text-green-600 p-2 hover:bg-green-50 rounded-lg transition"
                               title="Скачать">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div class="text-center py-16 bg-gradient-to-b from-gray-50 to-white rounded-2xl border-2 border-dashed border-gray-300">
        <div class="text-7xl mb-4 animate-bounce">📁</div>
        <h3 class="text-2xl font-medium text-gray-900 mb-2">Нет файлов</h3>
        <p class="text-gray-500 text-lg">В этом разделе пока нет файлов</p>
        <button onclick="document.getElementById('file-upload-input').click()" 
                class="mt-4 bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg text-sm font-medium transition shadow-md">
            Загрузить первый файл
        </button>
    </div>
@endforelse