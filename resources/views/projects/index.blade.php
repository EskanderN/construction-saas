@extends('layouts.app')

@section('title', 'Проекты')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <h1 class="text-3xl font-bold">Проекты</h1>
        
        @can('create', App\Models\Project::class)
            <a href="{{ route('projects.create') }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Создать проект
            </a>
        @endcan
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Название</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Создатель</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Участники</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата создания</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @foreach($projects as $project)
                    <tr class="hover:bg-gray-50 cursor-pointer" onclick="window.location='{{ route('projects.show', $project) }}'">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $project->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($project->status === 'created') bg-gray-100 text-gray-800
                                @elseif($project->status === 'in_calculation') bg-blue-100 text-blue-800
                                @elseif($project->status === 'on_approval') bg-yellow-100 text-yellow-800
                                @elseif($project->status === 'on_revision') bg-orange-100 text-orange-800
                                @elseif($project->status === 'approved') bg-green-100 text-green-800
                                @elseif($project->status === 'in_progress') bg-purple-100 text-purple-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ $project->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $project->creator->name }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $project->participants->count() }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $project->created_at->format('d.m.Y') }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $projects->links() }}
    </div>
</div>
@endsection