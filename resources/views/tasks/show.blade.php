@extends('layouts.app')

@section('title', $task->title)

@section('content')
<div class="space-y-6">
    <!-- Заголовок -->
    <div class="flex justify-between items-start">
        <div>
            <h1 class="text-3xl font-bold">{{ $task->title }}</h1>
            <p class="text-gray-600 mt-2">
                Проект: <a href="{{ route('projects.show', $project) }}" class="text-blue-600 hover:text-blue-800">{{ $project->name }}</a>
            </p>
        </div>
        
        <div class="flex space-x-2">
            <span class="px-3 py-1 text-sm rounded-full 
                @if($task->status === 'sent') bg-yellow-100 text-yellow-800
                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                @else bg-green-100 text-green-800
                @endif">
                @switch($task->status)
                    @case('sent') Ожидает принятия @break
                    @case('in_progress') В работе @break
                    @case('completed') Выполнена @break
                    @default {{ $task->status }}
                @endswitch
            </span>
        </div>
    </div>

    <!-- Информация о задаче -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Создатель -->
            <div class="border rounded-lg p-4 bg-gray-50">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($task->creator->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Создатель</p>
                        <p class="font-semibold">{{ $task->creator->name }}</p>
                        <p class="text-xs text-gray-500">{{ $task->creator->role }}</p>
                    </div>
                </div>
                <div class="text-sm text-gray-600">
                    <p>📅 Создано: {{ $task->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>

            <!-- Исполнитель -->
            <div class="border rounded-lg p-4 bg-gray-50">
                <div class="flex items-center space-x-3 mb-3">
                    <div class="w-10 h-10 bg-green-600 rounded-full flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr($task->assignee->name, 0, 1)) }}
                    </div>
                    <div>
                        <p class="text-sm text-gray-500">Исполнитель</p>
                        <p class="font-semibold">{{ $task->assignee->name }}</p>
                        <p class="text-xs text-gray-500">{{ $task->assignee->role }}</p>
                    </div>
                </div>
                @if($task->status !== 'sent')
                    <div class="text-sm text-gray-600">
                        <p>🔄 Принято: {{ $task->updated_at->format('d.m.Y H:i') }}</p>
                    </div>
                @endif
            </div>
        </div>

        @if($task->description)
            <div class="mt-6 p-4 bg-gray-50 rounded-lg">
                <h3 class="font-semibold mb-2">Описание задачи</h3>
                <p class="text-gray-700">{{ $task->description }}</p>
            </div>
        @endif
    </div>

    <!-- Действия - разные для разных ролей -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Действия</h2>
        
        <div class="space-y-4">
            <!-- Для исполнителя (прораба) -->
            @if(Auth::id() === $task->assigned_to)
                @if($task->status === 'sent')
                    <form method="POST" action="{{ route('tasks.start', $task) }}">
                        @csrf
                        <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-3 rounded-lg font-medium transition flex items-center">
                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            Принять в работу
                        </button>
                    </form>
                    <p class="text-sm text-gray-500 mt-2">
                        ⏳ Задача ожидает вашего принятия. После принятия вы сможете добавлять отчеты.
                    </p>
                @endif

                @if($task->status === 'in_progress')
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Форма отчета -->
                        <div class="border rounded-lg p-4 bg-blue-50">
                            <h3 class="font-semibold mb-3">Добавить отчет</h3>
                            <form method="POST" action="{{ route('tasks.report', $task) }}" enctype="multipart/form-data">
                                @csrf
                                <div class="space-y-3">
                                    <textarea name="report_text" rows="3" required 
                                              class="w-full border rounded-lg p-2" 
                                              placeholder="Опишите выполненную работу..."></textarea>
                                    <input type="file" name="photo" accept="image/*" class="w-full border rounded-lg p-2">
                                    <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-4 py-2 rounded-lg transition w-full">
                                        Отправить отчет
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Кнопка завершения -->
                        <div class="border rounded-lg p-4 bg-yellow-50">
                            <h3 class="font-semibold mb-3">Завершить задачу</h3>
                            <p class="text-sm text-gray-600 mb-3">
                                Если все работы выполнены, отметьте задачу как завершенную.
                            </p>
                            <form method="POST" action="{{ route('tasks.complete', $task) }}">
                                @csrf
                                <button type="submit" class="bg-purple-500 hover:bg-purple-600 text-white px-4 py-2 rounded-lg transition w-full">
                                    Завершить задачу
                                </button>
                            </form>
                        </div>
                    </div>
                @endif
            @endif

            <!-- Для создателя (РП) - только просмотр -->
            @if(Auth::id() === $task->created_by && Auth::id() !== $task->assigned_to)
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-gray-700">
                        📋 Вы создали эту задачу для {{ $task->assignee->name }}.
                        Статус задачи: 
                        @if($task->status === 'sent')
                            <span class="text-yellow-600 font-medium">ожидает принятия исполнителем</span>
                        @elseif($task->status === 'in_progress')
                            <span class="text-blue-600 font-medium">в работе</span>
                        @else
                            <span class="text-green-600 font-medium">выполнена</span>
                        @endif
                    </p>
                </div>
            @endif
        </div>
    </div>

    <!-- Отчеты -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Отчеты по задаче</h2>

        @forelse($task->reports as $report)
            <div class="border-b last:border-0 pb-4 last:pb-0 mb-4 last:mb-0">
                <div class="flex items-start space-x-3">
                    <div class="w-8 h-8 bg-gray-300 rounded-full flex items-center justify-center text-sm font-bold">
                        {{ strtoupper(substr($report->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium">{{ $report->user->name }}</p>
                                <p class="text-sm text-gray-600 mt-1">{{ $report->report_text }}</p>
                            </div>
                            <p class="text-xs text-gray-500">{{ $report->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        @if($report->photo_path)
                            <div class="mt-2">
                                <a href="{{ Storage::url($report->photo_path) }}" target="_blank" 
                                   class="text-blue-600 hover:text-blue-800 text-sm flex items-center">
                                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    Просмотреть фото
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <p class="text-gray-500 text-center py-4">Нет отчетов</p>
        @endforelse
    </div>

    <!-- Комментарии/вопросы по задаче -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Вопросы и обсуждение</h2>
        
        <!-- Форма добавления комментария - доступна всем участникам -->
        <div class="mb-6">
            <form method="POST" action="{{ route('tasks.comments', $task) }}">
                @csrf
                <div class="flex space-x-2">
                    <input type="text" name="comment" required 
                           class="flex-1 border rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"
                           placeholder="Напишите вопрос или комментарий...">
                    <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-6 py-2 rounded-lg transition">
                        Отправить
                    </button>
                </div>
            </form>
        </div>

        <!-- Список комментариев -->
        <div class="space-y-4">
            @forelse($task->comments as $comment)
                <div class="flex items-start space-x-3 {{ $comment->user_id === $task->assigned_to ? 'bg-blue-50' : 'bg-gray-50' }} p-3 rounded-lg">
                    <div class="w-8 h-8 {{ $comment->user_id === $task->assigned_to ? 'bg-green-500' : 'bg-gray-500' }} rounded-full flex items-center justify-center text-white text-sm font-bold">
                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                    </div>
                    <div class="flex-1">
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="font-medium text-sm">
                                    {{ $comment->user->name }}
                                    <span class="text-xs text-gray-500 ml-2">{{ $comment->user->role }}</span>
                                </p>
                                <p class="text-gray-700 mt-1">{{ $comment->comment }}</p>
                            </div>
                            <p class="text-xs text-gray-500">{{ $comment->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500 text-center py-4">Нет комментариев</p>
            @endforelse
        </div>
    </div>
</div>
@endsection