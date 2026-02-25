@extends('layouts.app')

@section('title', 'Создание проекта')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Создание нового проекта</h1>

        <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data">
            @csrf

            <!-- Основная информация -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Основная информация</h2>
                
                <div class="mb-4">
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">Название проекта *</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('name') border-red-500 @enderror">
                    @error('name')
                        <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
                </div>
            </div>

            <!-- Участники проекта -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Участники проекта</h2>
                
                <div id="participants-container">
                    <div class="participant-row grid grid-cols-2 gap-4 mb-2">
                        <div>
                            <select name="participants[0][user_id]" class="w-full border rounded-md px-3 py-2">
                                <option value="">Выберите пользователя</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex">
                            <select name="participants[0][role]" class="w-full border rounded-md px-3 py-2">
                                <option value="pto">ПТО</option>
                                <option value="supply">Снабжение</option>
                                <option value="project_manager">Руководитель проекта</option>
                                <option value="site_manager">Прораб</option>
                                <option value="accountant">Бухгалтер</option>
                            </select>
                            <button type="button" onclick="removeParticipant(this)" class="ml-2 text-red-600 hover:text-red-800 hidden">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="button" onclick="addParticipant()" class="mt-2 text-blue-600 hover:text-blue-800 text-sm">
                    + Добавить участника
                </button>
            </div>

            <!-- Файлы -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Файлы</h2>
                
                <div class="border-2 border-dashed border-gray-300 rounded-lg p-6 text-center">
                    <input type="file" name="files[]" id="files" multiple class="hidden" onchange="updateFileList()">
                    <label for="files" class="cursor-pointer">
                        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path>
                        </svg>
                        <p class="mt-1 text-sm text-gray-600">Нажмите для загрузки файлов</p>
                    </label>
                    <div id="file-list" class="mt-2 text-sm text-gray-500"></div>
                </div>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-2">
                <a href="{{ route('projects.index') }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Отмена
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Создать проект
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let participantCount = 1;

function addParticipant() {
    const container = document.getElementById('participants-container');
    const newRow = document.createElement('div');
    newRow.className = 'participant-row grid grid-cols-2 gap-4 mb-2';
    newRow.innerHTML = `
        <div>
            <select name="participants[${participantCount}][user_id]" class="w-full border rounded-md px-3 py-2">
                <option value="">Выберите пользователя</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role }})</option>
                @endforeach
            </select>
        </div>
        <div class="flex">
            <select name="participants[${participantCount}][role]" class="w-full border rounded-md px-3 py-2">
                <option value="pto">ПТО</option>
                <option value="supply">Снабжение</option>
                <option value="project_manager">Руководитель проекта</option>
                <option value="site_manager">Прораб</option>
                <option value="accountant">Бухгалтер</option>
            </select>
            <button type="button" onclick="removeParticipant(this)" class="ml-2 text-red-600 hover:text-red-800">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                </svg>
            </button>
        </div>
    `;
    container.appendChild(newRow);
    participantCount++;
}

function removeParticipant(button) {
    button.closest('.participant-row').remove();
}

function updateFileList() {
    const files = document.getElementById('files').files;
    const fileList = document.getElementById('file-list');
    fileList.innerHTML = '';
    
    if (files.length > 0) {
        const list = document.createElement('ul');
        list.className = 'list-disc list-inside';
        for (let i = 0; i < files.length; i++) {
            const li = document.createElement('li');
            li.textContent = files[i].name;
            list.appendChild(li);
        }
        fileList.appendChild(list);
    }
}
</script>
@endsection