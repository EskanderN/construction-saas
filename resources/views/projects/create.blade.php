@extends('layouts.app')

@section('title', 'Создание проекта')

@section('content')
@php
    $roleLabels = [
        'director' => 'Директор',
        'deputy_director' => 'Зам. директора',
        'pto' => 'ПТО',
        'supply' => 'Снабжение',
        'project_manager' => 'Рук. проекта',
        'site_manager' => 'Прораб',
        'accountant' => 'Бухгалтер',
    ];

    $autoUsers = $users->filter(fn($u) => in_array($u->role, ['director','deputy_director','pto','supply'], true));
    $extraUsers = $users->filter(fn($u) => !in_array($u->role, ['director','deputy_director','pto','supply'], true))->values();

    // Для JS (минимально)
    $extraUsersJs = $extraUsers->map(fn($u) => [
        'id' => $u->id,
        'name' => $u->name,
        'role' => $u->role,
        'roleName' => $roleLabels[$u->role] ?? $u->role,
    ])->values();
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
                <span class="text-gray-500">Создание</span>
            </div>
            <h1 class="text-2xl font-extrabold text-gray-900 leading-tight">Создать проект</h1>
            <p class="mt-1 text-sm text-gray-500">Заполните базовые данные, добавьте участников и файлы (по желанию).</p>
        </div>
    </div>

    <form method="POST" action="{{ route('projects.store') }}" enctype="multipart/form-data" class="space-y-5">
        @csrf

        {{-- MAIN --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="font-bold text-gray-900 text-sm">Основная информация</p>
                <p class="text-xs text-gray-400 mt-0.5">Поля со звёздочкой обязательны</p>
            </div>
            <div class="p-5 space-y-4">
                <div>
                    <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">Название проекта <span class="text-red-500">*</span></label>
                    <input
                        type="text"
                        name="name"
                        id="name"
                        value="{{ old('name') }}"
                        required
                        autofocus
                        class="w-full text-sm border rounded-lg px-3 py-2.5 focus:outline-none focus:border-blue-400 {{ $errors->has('name') ? 'border-red-300' : 'border-gray-200' }}"
                        placeholder="Например: ЖК Green Park — корпус А"
                    >
                    @error('name')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">Описание</label>
                    <textarea
                        name="description"
                        id="description"
                        rows="4"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-blue-400 resize-none"
                        placeholder="Коротко: что строим, где, важные примечания..."
                    >{{ old('description') }}</textarea>
                    @error('description')
                        <p class="text-xs text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        {{-- AUTO PARTICIPANTS --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-center justify-between gap-3 flex-wrap">
                <div>
                    <p class="font-bold text-gray-900 text-sm">Автоматические участники</p>
                    <p class="text-xs text-gray-400 mt-0.5">Будут добавлены автоматически (дирекция / ПТО / снабжение)</p>
                </div>
                <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-blue-50 text-blue-700">
                    {{ $autoUsers->count() }} чел.
                </span>
            </div>

            <div class="p-5">
                @if($autoUsers->count())
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        @foreach($autoUsers as $u)
                            <div class="flex items-center justify-between p-3 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 hover:bg-white transition-all">
                                <div class="flex items-center gap-3 min-w-0">
                                    <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center text-xs font-bold flex-shrink-0">
                                        {{ strtoupper(mb_substr($u->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0">
                                        <p class="text-sm font-semibold text-gray-800 truncate">{{ $u->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $roleLabels[$u->role] ?? $u->role }}</p>
                                    </div>
                                </div>
                                <svg class="w-4 h-4 text-blue-500 flex-shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-400">Нет автоматических участников</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- EXTRA PARTICIPANTS --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100 flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <p class="font-bold text-gray-900 text-sm">Дополнительные участники</p>
                    <p class="text-xs text-gray-400 mt-0.5">Необязательно. Роль подставится автоматически по должности.</p>
                </div>
                <button
                    type="button"
                    onclick="addParticipantRow()"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-blue-600 text-white text-xs font-semibold hover:bg-blue-700 transition-colors"
                >
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    Добавить
                </button>
            </div>

            <div class="p-5">
                @if($extraUsers->count())
                    <div id="participants-container" class="space-y-2"></div>
                    <p class="text-xs text-gray-400 mt-3">Подсказка: можно добавить несколько. Пустые строки не сохранятся.</p>
                @else
                    <div class="text-center py-8 border-2 border-dashed border-gray-200 rounded-lg">
                        <p class="text-sm text-gray-400">Нет доступных пользователей для добавления</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- FILES --}}
        <div class="bg-white rounded-xl border border-gray-200">
            <div class="px-5 py-4 border-b border-gray-100">
                <p class="font-bold text-gray-900 text-sm">Файлы</p>
                <p class="text-xs text-gray-400 mt-0.5">Можно прикрепить несколько (до 20 МБ каждый)</p>
            </div>

            <div class="p-5">
                <input type="file" name="files[]" id="files" multiple class="hidden" onchange="renderFileList()">

                <label for="files" class="flex flex-col items-center justify-center gap-2 p-6 border-2 border-dashed border-gray-200 rounded-lg cursor-pointer hover:border-blue-400 hover:bg-blue-50 transition-colors">
                    <svg class="w-7 h-7 text-gray-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
                    <span class="text-sm font-semibold text-gray-600">Нажмите, чтобы выбрать файлы</span>
                    <span class="text-xs text-gray-400">PDF/DOC/XLS/изображения — как обычно</span>
                    <span id="files-count" class="text-xs text-blue-700 font-semibold" style="display:none;"></span>
                </label>

                <div id="file-list" class="mt-3 text-sm text-gray-600"></div>
            </div>
        </div>

        {{-- ACTIONS --}}
        <div class="flex items-center justify-end gap-2">
            <a href="{{ route('projects.index') }}"
               class="px-4 py-2 rounded-lg border border-gray-200 text-sm font-semibold text-gray-600 hover:bg-gray-50 transition-colors">
                Отмена
            </a>
            <button type="submit"
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-blue-600 text-white text-sm font-semibold hover:bg-blue-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Создать проект
            </button>
        </div>
    </form>
</div>

<script>
(function () {
    const USERS = @json($extraUsersJs);
    let idx = 0;

    window.addParticipantRow = function () {
        const wrap = document.getElementById('participants-container');
        if (!wrap || !USERS.length) return;

        const options = ['<option value="">Выберите пользователя</option>']
            .concat(USERS.map(u => `<option value="${u.id}" data-role="${u.role}" data-role-name="${escapeHtml(u.roleName)}">${escapeHtml(u.name)} (${escapeHtml(u.roleName)})</option>`))
            .join('');

        const row = document.createElement('div');
        row.className = 'participant-row p-3 rounded-lg bg-gray-50 border border-transparent hover:border-gray-200 hover:bg-white transition-all';
        row.innerHTML = `
            <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <select name="participants[${idx}][user_id]"
                        class="w-full text-sm border border-gray-200 rounded-lg px-3 py-2.5 focus:outline-none focus:border-blue-400"
                        onchange="fillRole(this, ${idx})">
                    ${options}
                </select>

                <div class="flex items-center gap-2">
                    <input type="text"
                           name="participants[${idx}][role]"
                           id="role-${idx}"
                           readonly
                           class="w-full text-sm bg-white border border-gray-200 rounded-lg px-3 py-2.5 text-gray-600"
                           placeholder="Роль определится автоматически">
                    <button type="button"
                            onclick="this.closest('.participant-row').remove()"
                            class="p-2 rounded-lg text-gray-300 hover:text-red-600 hover:bg-red-50 transition-all"
                            title="Удалить">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/><path d="M10 11v6M14 11v6"/><path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    </button>
                </div>
            </div>
        `;
        wrap.appendChild(row);
        idx++;
    };

    window.fillRole = function (select, i) {
        const opt = select.options[select.selectedIndex];
        const role = opt?.dataset?.role || '';
        const roleName = opt?.dataset?.roleName || '';
        const input = document.getElementById('role-' + i);
        if (!input) return;
        input.value = role ? (roleName || role) : '';
    };

    window.renderFileList = function () {
        const input = document.getElementById('files');
        const list = document.getElementById('file-list');
        const count = document.getElementById('files-count');
        if (!input || !list || !count) return;

        const files = Array.from(input.files || []);
        list.innerHTML = '';
        count.style.display = files.length ? '' : 'none';
        count.textContent = files.length ? `${files.length} файлов выбрано` : '';

        if (!files.length) return;

        const ul = document.createElement('ul');
        ul.className = 'space-y-1';
        files.slice(0, 8).forEach(f => {
            const li = document.createElement('li');
            li.className = 'flex items-center justify-between p-2 rounded-lg bg-white border border-gray-200';
            li.innerHTML = `<span class="text-sm text-gray-700 truncate">${escapeHtml(f.name)}</span><span class="text-xs text-gray-400 ml-3 flex-shrink-0">${humanSize(f.size)}</span>`;
            ul.appendChild(li);
        });

        if (files.length > 8) {
            const more = document.createElement('div');
            more.className = 'text-xs text-gray-400 mt-2';
            more.textContent = `+ ещё ${files.length - 8} файлов`;
            list.appendChild(ul);
            list.appendChild(more);
            return;
        }

        list.appendChild(ul);
    };

    function humanSize(bytes) {
        const kb = bytes / 1024;
        if (kb < 1024) return Math.round(kb) + ' KB';
        return (kb / 1024).toFixed(1) + ' MB';
    }

    function escapeHtml(str) {
        return String(str).replace(/[&<>"']/g, s => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[s]));
    }

    document.addEventListener('DOMContentLoaded', function () {
        if (USERS.length) addParticipantRow(); // 1 строка по умолчанию — удобно
    });
})();
</script>
@endsection