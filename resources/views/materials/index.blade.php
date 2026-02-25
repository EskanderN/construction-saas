@extends('layouts.app')

@section('title', 'Поставки материалов')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Поставки материалов</h1>
            <p class="text-gray-600 mt-2">{{ $project->name }}</p>
        </div>
        
        <div class="flex space-x-2">
            <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
                Назад к проекту
            </a>
            @can('createMaterial', $project)
                <a href="{{ route('projects.materials.create', $project) }}" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                    Добавить поставку
                </a>
            @endcan
        </div>
    </div>

    <div class="bg-white rounded-lg shadow-md overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Материал</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Количество</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Снабженец</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Плановая дата</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Подтверждение</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($deliveries as $delivery)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $delivery->material_name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $delivery->quantity }} {{ $delivery->unit }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $delivery->supplyUser->name }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $delivery->delivery_date ? $delivery->delivery_date->format('d.m.Y') : 'Не указана' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($delivery->status === 'pending') bg-yellow-100 text-yellow-800
                                @else bg-green-100 text-green-800
                                @endif">
                                {{ $delivery->status === 'pending' ? 'Ожидает' : 'Подтверждена' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($delivery->status === 'confirmed')
                                <div class="text-sm">
                                    <div>{{ $delivery->confirmed_date->format('d.m.Y') }}</div>
                                    <div class="text-xs text-gray-500">{{ $delivery->siteManagerUser->name ?? '' }}</div>
                                </div>
                            @else
                                <span class="text-sm text-gray-500">—</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                            Нет поставок
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $deliveries->links() }}
    </div>
</div>
@endsection