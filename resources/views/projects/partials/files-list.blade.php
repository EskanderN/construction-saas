{{-- resources/views/projects/partials/files-list.blade.php --}}
@forelse($filesByUser as $userId => $userFiles)
    @php
        $user = $userFiles->first()->user;
        $totalSize = $userFiles->sum('file_size');
        $fileCount = $userFiles->count();

        // Определяем иконку для пользователя (SVG вместо эмодзи)
        $userIcon = match ($user->role) {
            'director'
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.5 5.5L21 8l-4.5 3.5L19 16l-5-2.5L9 16l2.5-4.5L7 8l5.5-1.5L15 3z"></path></svg>',
            'deputy_director'
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>',
            'pto'
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
            'supply'
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>',
            'project_manager'
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>',
            'site_manager'
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"></path></svg>',
            'accountant'
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>',
            default
                => '<svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>',
        };

        // Цвет для роли
        $roleColor = match ($user->role) {
            'director' => 'bg-purple-100 text-purple-700',
            'deputy_director' => 'bg-indigo-100 text-indigo-700',
            'pto' => 'bg-blue-100 text-blue-700',
            'supply' => 'bg-green-100 text-green-700',
            'project_manager' => 'bg-yellow-100 text-yellow-700',
            'site_manager' => 'bg-orange-100 text-orange-700',
            'accountant' => 'bg-emerald-100 text-emerald-700',
            default => 'bg-gray-100 text-gray-700',
        };

        // Русские названия ролей
        $roleName = match ($user->role) {
            'director' => 'Директор',
            'deputy_director' => 'Зам. директора',
            'pto' => 'ПТО',
            'supply' => 'Снабжение',
            'project_manager' => 'Руководитель проекта',
            'site_manager' => 'Прораб',
            'accountant' => 'Бухгалтер',
            default => $user->role,
        };
    @endphp

    <div class="border border-gray-200 rounded-xl overflow-hidden transition-all hover:shadow-lg bg-white file-group"
        data-user="{{ $user->id }}">
        <!-- Заголовок пользователя -->
        <div
            class="bg-gradient-to-r from-gray-50 to-white px-5 py-4 border-b border-gray-200 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <div
                    class="w-12 h-12 rounded-full bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center text-white shadow-md">
                    {!! $userIcon !!}
                </div>
                <div>
                    <div class="flex items-center space-x-2">
                        <h4 class="font-semibold text-gray-900">{{ $user->name }}</h4>
                        <span
                            class="px-2 py-0.5 text-xs font-medium rounded-full {{ $roleColor }}">{{ $roleName }}</span>
                    </div>
                    <div class="flex items-center space-x-4 text-xs text-gray-500 mt-1">
                        <span class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                </path>
                            </svg>
                            {{ $fileCount }} {{ $fileCount == 1 ? 'файл' : ($fileCount < 5 ? 'файла' : 'файлов') }}
                        </span>
                        <span class="flex items-center">
                            <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M20 12H4m16 0a8 8 0 01-8 8m8-8a8 8 0 00-8-8"></path>
                            </svg>
                            {{ $totalSize > 1048576 ? round($totalSize / 1048576, 2) . ' МБ' : round($totalSize / 1024, 2) . ' КБ' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Кнопка свернуть/развернуть -->
            <button onclick="toggleUserFiles({{ $user->id }})"
                class="text-gray-400 hover:text-gray-600 transition p-1 hover:bg-gray-100 rounded-lg">
                <svg class="w-5 h-5 transform transition-transform duration-200" id="arrow-{{ $user->id }}"
                    fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>

        <!-- Список файлов пользователя -->
        <div id="files-{{ $user->id }}" class="bg-white divide-y divide-gray-100">
            @foreach ($userFiles->sortByDesc('created_at') as $file)
                @php
                    // Определяем иконку для типа файла
                    $ext = strtolower(pathinfo($file->file_name, PATHINFO_EXTENSION));
                    $fileIcon = match ($ext) {
                        'pdf'
                            => '<svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                        'doc',
                        'docx'
                            => '<svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
                        'xls',
                        'xlsx',
                        'csv'
                            => '<svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
                        'jpg',
                        'jpeg',
                        'png',
                        'gif',
                        'bmp'
                            => '<svg class="w-8 h-8 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>',
                        'zip',
                        'rar',
                        '7z'
                            => '<svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"></path></svg>',
                        'txt',
                        'md'
                            => '<svg class="w-8 h-8 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>',
                        'dwg',
                        'dxf'
                            => '<svg class="w-8 h-8 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>',
                        default
                            => '<svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>',
                    };

                    // Цвет для секции
                    $sectionColor = match ($file->section) {
                        'pto' => 'bg-blue-100 text-blue-700',
                        'supply' => 'bg-green-100 text-green-700',
                        default => 'bg-gray-100 text-gray-700',
                    };

                    $sectionName = match ($file->section) {
                        'pto' => 'ПТО',
                        'supply' => 'Снабжение',
                        default => 'Общий',
                    };
                @endphp

                <div class="p-4 hover:bg-gray-50 transition file-item group" id="file-{{ $file->id }}"
                    data-filename="{{ strtolower($file->file_name) }}"
                    data-date="{{ $file->created_at ? $file->created_at->timestamp : 0 }}"
                    data-size="{{ $file->file_size }}" data-section="{{ $file->section }}">
                    <div class="flex items-start space-x-4">
                        <!-- Чекбокс для выбора (только для своих файлов) -->
                        @if ($file->user_id === Auth::id())
                            <div class="pt-2">
                                <input type="checkbox"
                                    class="file-checkbox w-4 h-4 text-blue-600 rounded border-gray-300 focus:ring-blue-500 focus:ring-2"
                                    data-file-id="{{ $file->id }}" onchange="updateSelectedCount()">
                            </div>
                        @endif

                        <!-- Иконка файла -->
                        <div class="flex-shrink-0">
                            {!! $fileIcon !!}
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-center flex-wrap gap-2">
                                <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                    class="text-gray-900 hover:text-blue-600 hover:underline font-medium text-base truncate max-w-md">
                                    {{ $file->file_name }}
                                </a>
                                <span class="text-xs px-2 py-1 rounded-full font-medium {{ $sectionColor }}">
                                    {{ $sectionName }}
                                </span>
                            </div>

                            <div class="flex items-center flex-wrap gap-4 text-xs text-gray-500 mt-2">
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    {{ $file->created_at ? \Carbon\Carbon::parse($file->created_at)->format('d.m.Y H:i') : '' }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M4 7v10c0 2 1 3 3 3h10c2 0 3-1 3-3V7c0-2-1-3-3-3H7c-2 0-3 1-3 3z"></path>
                                    </svg>
                                    {{ $file->file_size > 1048576 ? round($file->file_size / 1048576, 2) . ' МБ' : round($file->file_size / 1024, 2) . ' КБ' }}
                                </span>
                                <span class="flex items-center">
                                    <svg class="w-3.5 h-3.5 mr-1 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z">
                                        </path>
                                    </svg>
                                    {{ $file->user->name }}
                                </span>
                            </div>
                        </div>

                        <div class="flex items-center space-x-1 opacity-0 group-hover:opacity-100 transition-opacity">
                            <!-- Кнопка просмотра -->
                            <a href="{{ Storage::url($file->file_path) }}" target="_blank"
                                class="text-gray-400 hover:text-blue-600 p-2 hover:bg-blue-50 rounded-lg transition"
                                title="Просмотр">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z">
                                    </path>
                                </svg>
                            </a>

                            <!-- Кнопка скачивания -->
                            <a href="{{ Storage::url($file->file_path) }}" download="{{ $file->file_name }}"
                                class="text-gray-400 hover:text-green-600 p-2 hover:bg-green-50 rounded-lg transition"
                                title="Скачать">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@empty
    <div
        class="text-center py-16 bg-gradient-to-b from-gray-50 to-white rounded-2xl border-2 border-dashed border-gray-300">
        <div class="flex justify-center mb-4">
            <svg class="w-20 h-20 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                    d="M5 19a2 2 0 01-2-2V7a2 2 0 012-2h4l2 2h4a2 2 0 012 2v1M5 19h14a2 2 0 002-2v-5a2 2 0 00-2-2H9a2 2 0 00-2 2v5a2 2 0 01-2 2z">
                </path>
            </svg>
        </div>
        <h3 class="text-xl font-medium text-gray-900 mb-2">Нет файлов</h3>
        <p class="text-gray-500 mb-6">В этом разделе пока нет файлов</p>
        <button onclick="document.getElementById('file-upload-input').click()"
            class="inline-flex items-center px-4 py-2 bg-blue-500 hover:bg-blue-600 text-white rounded-lg text-sm font-medium transition shadow-md">
            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0l-4 4m4-4v12"></path>
            </svg>
            Загрузить первый файл
        </button>
    </div>
@endforelse
