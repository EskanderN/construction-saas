@extends('layouts.app')

@section('title', 'Задачи проекта')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Задачи проекта</h1>
            <p class="text-gray-600 mt-2">{{ $project->name }}</p>
        </div>
        
        <div class="flex space-x-2">
            <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Назад к проекту
            </a>
            @can('createTask', $project)
                <a href="{{ route('projects.tasks.create', $project) }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Создать задачу
                </a>
            @endcan
        </div>
    </div>

    <!-- Фильтр по статусам -->
    <div class="bg-white rounded-lg shadow-md p-4">
        <div class="flex space-x-2">
            <a href="{{ route('projects.tasks.index', $project) }}" 
               class="px-3 py-1 rounded-full text-sm {{ !request('status') ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                Все
            </a>
            <a href="{{ route('projects.tasks.index', ['project' => $project, 'status' => 'sent']) }}" 
               class="px-3 py-1 rounded-full text-sm {{ request('status') == 'sent' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                Ожидают
            </a>
            <a href="{{ route('projects.tasks.index', ['project' => $project, 'status' => 'in_progress']) }}" 
               class="px-3 py-1 rounded-full text-sm {{ request('status') == 'in_progress' ? 'bg-blue-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                В работе
            </a>
            <a href="{{ route('projects.tasks.index', ['project' => $project, 'status' => 'completed']) }}" 
               class="px-3 py-1 rounded-full text-sm {{ request('status') == 'completed' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-700' }}">
                Выполнены
            </a>
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Задача</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Исполнитель</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создана</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обновления</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($tasks as $task)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('projects.tasks.show', [$project, $task]) }}'">
                        <td class="px-6 py-4">
                            <div class="text-sm font-medium text-gray-900">{{ $task->title }}</div>
                            <div class="text-sm text-gray-500">{{ Str::limit($task->description, 50) }}</div>
                            @if($task->comments_count > 0)
                                <div class="text-xs text-blue-600 mt-1">
                                    💬 {{ $task->comments_count }} {{ $task->comments_count == 1 ? 'комментарий' : 'комментариев' }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $task->assignee->name }}</div>
                            <div class="text-xs text-gray-500">{{ $task->assignee->role }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($task->status === 'sent') bg-yellow-100 text-yellow-800
                                @elseif($task->status === 'in_progress') bg-blue-100 text-blue-800
                                @else bg-green-100 text-green-800
                                @endif">
                                @switch($task->status)
                                    @case('sent') Ожидает @break
                                    @case('in_progress') В работе @break
                                    @case('completed') Выполнена @break
                                    @default {{ $task->status }}
                                @endswitch
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $task->created_at->format('d.m.Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($task->status === 'in_progress' && $task->updated_at != $task->created_at)
                                Принята: {{ $task->updated_at->format('d.m.Y H:i') }}
                            @elseif($task->status === 'completed')
                                Завершена: {{ $task->updated_at->format('d.m.Y H:i') }}
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-6 py-4 text-center text-gray-500">
                            Нет задач
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $tasks->links() }}
    </div>
</div>
@endsection