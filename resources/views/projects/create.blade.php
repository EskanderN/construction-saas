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

            <!-- Автоматические участники -->
            <div class="mb-6 bg-blue-50 p-4 rounded-lg">
                <h2 class="text-lg font-semibold mb-3 text-blue-800">Автоматические участники</h2>
                <p class="text-sm text-blue-600 mb-2">Эти сотрудники будут добавлены в проект автоматически:</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                    @php
                        $autoUsers = $users->filter(function($user) {
                            return in_array($user->role, ['director', 'deputy_director', 'pto', 'supply']);
                        });
                    @endphp
                    
                    @forelse($autoUsers as $user)
                        <div class="flex items-center space-x-2 text-sm text-blue-700">
                            <svg class="w-4 h-4 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            <span>{{ $user->name }} ({{ $user->role }})</span>
                        </div>
                    @empty
                        <p class="text-sm text-gray-500">Нет автоматических участников</p>
                    @endforelse
                </div>
            </div>

            <!-- Дополнительные участники -->
            <div class="mb-6">
                <h2 class="text-lg font-semibold mb-4">Дополнительные участники</h2>
                <p class="text-sm text-gray-500 mb-3">(можно не добавлять)</p>
                
                <div id="participants-container">
                    <!-- Сюда будут добавляться строки -->
                </div>

                <button type="button" onclick="addParticipant()" class="mt-2 bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600 text-sm flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                    </svg>
                    Добавить участника
                </button>
                
                <p class="text-xs text-gray-500 mt-2">При выборе пользователя роль подставляется автоматически</p>
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
let participantCount = 0;

// Функция для получения списка доступных пользователей (исключая автоматических)
function getAvailableUsers() {
    return [
        @foreach($users as $user)
            @if(!in_array($user->role, ['director', 'deputy_director', 'pto', 'supply']))
                {
                    id: {{ $user->id }},
                    name: "{{ $user->name }}",
                    role: "{{ $user->role }}",
                    roleName: "{{ $user->role === 'project_manager' ? 'Руководитель проекта' : ($user->role === 'site_manager' ? 'Прораб' : ($user->role === 'accountant' ? 'Бухгалтер' : $user->role)) }}"
                },
            @endif
        @endforeach
    ];
}

function addParticipant() {
    const container = document.getElementById('participants-container');
    const users = getAvailableUsers();
    
    if (users.length === 0) {
        alert('Нет доступных пользователей для добавления');
        return;
    }
    
    let options = '<option value="">Выберите пользователя</option>';
    users.forEach(user => {
        options += `<option value="${user.id}" data-role="${user.role}">${user.name} (${user.roleName})</option>`;
    });
    
    const newRow = document.createElement('div');
    newRow.className = 'participant-row grid grid-cols-1 md:grid-cols-2 gap-4 mb-2 p-3 bg-gray-50 rounded-lg';
    newRow.innerHTML = `
        <div>
            <select name="participants[${participantCount}][user_id]" 
                    onchange="updateRole(this, ${participantCount})"
                    class="w-full border rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                ${options}
            </select>
        </div>
        <div class="flex items-center space-x-2">
            <input type="text" 
                   name="participants[${participantCount}][role]" 
                   id="role-${participantCount}"
                   readonly
                   placeholder="Роль определится автоматически"
                   class="w-full bg-gray-100 border rounded-md px-3 py-2 text-gray-600">
            <button type="button" onclick="removeParticipant(this)" class="text-red-600 hover:text-red-800 p-2">
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

function updateRole(select, index) {
    const selected = select.options[select.selectedIndex];
    const roleInput = document.getElementById(`role-${index}`);
    
    if (selected && selected.value) {
        const role = selected.dataset.role;
        // Перевод ролей на русский для отображения
        const roleNames = {
            'project_manager': 'Руководитель проекта',
            'site_manager': 'Прораб',
            'accountant': 'Бухгалтер'
        };
        roleInput.value = roleNames[role] || role;
    } else {
        roleInput.value = '';
    }
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

// Добавляем первого участника при загрузке страницы, если есть доступные пользователи
document.addEventListener('DOMContentLoaded', function() {
    const users = getAvailableUsers();
    if (users.length > 0) {
        addParticipant();
    }
});
</script>

<script>
    function addParticipant() {
        const container = document.getElementById('participants-container');
        const users = getAvailableUsers();
        
        if (users.length === 0) {
            alert('Нет доступных пользователей для добавления');
            return;
        }
        
        let options = '<option value="">Выберите пользователя</option>';
        users.forEach(user => {
            options += `<option value="${user.id}" data-role="${user.role}">${user.name} (${user.roleName})</option>`;
        });
        
        const newRow = document.createElement('div');
        newRow.className = 'participant-row grid grid-cols-1 md:grid-cols-2 gap-4 mb-2 p-3 bg-gray-50 rounded-lg';
        newRow.innerHTML = `
            <div>
                <select name="participants[${participantCount}][user_id]" 
                        onchange="updateRole(this, ${participantCount})"
                        class="w-full border rounded-md px-3 py-2 focus:ring-blue-500 focus:border-blue-500">
                    ${options}
                </select>
            </div>
            <div class="flex items-center space-x-2">
                <input type="text" 
                    name="participants[${participantCount}][role]" 
                    id="role-${participantCount}"
                    readonly
                    placeholder="Роль определится автоматически"
                    class="w-full bg-gray-100 border rounded-md px-3 py-2 text-gray-600">
                <button type="button" onclick="removeParticipant(this)" class="text-red-600 hover:text-red-800 p-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                </button>
            </div>
        `;
        container.appendChild(newRow);
        participantCount++;
    }
</string>
@endsection