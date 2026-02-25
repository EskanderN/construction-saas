@extends('layouts.app')

@section('title', 'Дашборд')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold">Дашборд</h1>

    <!-- Статистика -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Всего проектов</h3>
            <p class="text-3xl font-bold text-blue-600">{{ $stats['total_projects'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Активных проектов</h3>
            <p class="text-3xl font-bold text-green-600">{{ $stats['active_projects'] }}</p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-6">
            <h3 class="text-lg font-semibold text-gray-600 mb-2">Мои задачи</h3>
            <p class="text-3xl font-bold text-yellow-600">{{ $stats['my_tasks'] }}</p>
        </div>
    </div>

    <!-- Последние проекты -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-xl font-bold mb-4">Последние проекты</h2>
        
        <div class="space-y-4">
            @foreach($recentProjects as $project)
                <div class="border-b last:border-0 pb-4 last:pb-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <a href="{{ route('projects.show', $project) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                {{ $project->name }}
                            </a>
                            <p class="text-sm text-gray-600">Создал: {{ $project->creator->name }}</p>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full 
                            @if($project->status === 'created') bg-gray-100 text-gray-800
                            @elseif($project->status === 'in_progress') bg-green-100 text-green-800
                            @elseif($project->status === 'completed') bg-blue-100 text-blue-800
                            @else bg-yellow-100 text-yellow-800
                            @endif">
                            {{ $project->status }}
                        </span>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Мои задачи -->
    @if($myTasks->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-xl font-bold mb-4">Мои задачи</h2>
            
            <div class="space-y-4">
                @foreach($myTasks as $task)
                    <div class="border-b last:border-0 pb-4 last:pb-0">
                        <div class="flex justify-between items-start">
                            <div>
                                <a href="{{ route('projects.tasks.show', [$task->project, $task]) }}" class="text-lg font-semibold text-blue-600 hover:text-blue-800">
                                    {{ $task->title }}
                                </a>
                                <p class="text-sm text-gray-600">Проект: {{ $task->project->name }}</p>
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
    @endif
</div>
@endsection