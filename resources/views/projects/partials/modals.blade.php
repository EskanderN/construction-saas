<!-- Модальное окно для утверждения проекта -->
<div id="approveModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
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
<div id="rejectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
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
<div id="rejectPtoModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
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
<div id="rejectSupplyModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
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
<div id="addParticipantModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
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

<!-- Модальное окно для возврата проекта из реализации на доработку -->
<div id="reworkModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 max-w-md w-full">
        <h3 class="text-lg font-bold mb-4">Вернуть проект на доработку</h3>
        <form method="POST" action="{{ route('projects.rework', $project) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-2">Причина возврата</label>
                <textarea name="comment" rows="4" required class="w-full border rounded-md px-3 py-2" 
                          placeholder="Укажите причину возврата проекта на доработку..."></textarea>
            </div>
            <div class="flex justify-end space-x-2">
                <button type="button" onclick="closeReworkModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">
                    Отмена
                </button>
                <button type="submit" class="px-4 py-2 bg-orange-500 text-white rounded-md hover:bg-orange-600">
                    Вернуть на доработку
                </button>
            </div>
        </form>
    </div>
</div>

<script>
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

function openReworkModal() {
    document.getElementById('reworkModal').classList.remove('hidden');
    document.getElementById('reworkModal').classList.add('flex');
}

function closeReworkModal() {
    document.getElementById('reworkModal').classList.add('hidden');
    document.getElementById('reworkModal').classList.remove('flex');
}

// Закрытие модалок по клику на фон
window.onclick = function(event) {
    if (event.target.classList.contains('fixed')) {
        event.target.classList.add('hidden');
        event.target.classList.remove('flex');
    }
}
</script>