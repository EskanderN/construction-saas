{{-- resources/views/projects/partials/files-list.blade.php --}}
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
                <div class="p-4 hover:bg-gray-50 transition" id="file-{{ $file->id }}">
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
                                <button onclick="deleteFile({{ $file->id }})" 
                                        class="text-red-600 hover:text-red-800 p-2 hover:bg-red-50 rounded-full" 
                                        title="Удалить">
                                    🗑️
                                </button>
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