@extends('layouts.app')

@section('title', 'Финансовый статус')

@section('content')
<div class="space-y-6">
    <div class="flex justify-between items-center">
        <div>
            <h1 class="text-3xl font-bold">Финансовый статус</h1>
            <p class="text-gray-600 mt-2">{{ $project->name }}</p>
        </div>
        
        <a href="{{ route('projects.show', $project) }}" class="bg-gray-500 text-white px-4 py-2 rounded-md hover:bg-gray-600">
            Назад к проекту
        </a>
    </div>

    <!-- Текущий статус -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">Текущий финансовый статус</h2>
        
        @php
            $currentStatus = $project->currentFinancialStatus();
        @endphp

        @if($currentStatus)
            <div class="flex items-center space-x-4">
                <span class="px-4 py-2 text-sm rounded-full 
                    @if($currentStatus->financial_status === 'pending_payment') bg-yellow-100 text-yellow-800
                    @elseif($currentStatus->financial_status === 'paid') bg-green-100 text-green-800
                    @else bg-red-100 text-red-800
                    @endif">
                    {{ $currentStatus->financial_status }}
                </span>
                <div>
                    <p class="text-sm text-gray-600">Обновлено: {{ $currentStatus->user->name }}</p>
                    <p class="text-xs text-gray-500">{{ $currentStatus->created_at->format('d.m.Y H:i') }}</p>
                </div>
            </div>
            @if($currentStatus->comment)
                <p class="mt-2 text-sm text-gray-700">Комментарий: {{ $currentStatus->comment }}</p>
            @endif
        @else
            <p class="text-gray-500">Финансовый статус не установлен</p>
        @endif
    </div>

    <!-- Форма обновления статуса -->
    @can('updateFinancial', $project)
        <div class="bg-white rounded-lg shadow-md p-6">
            <h2 class="text-lg font-semibold mb-4">Обновить финансовый статус</h2>
            
            <form method="POST" action="{{ route('projects.financial.update', $project) }}">
                @csrf
                <div class="grid grid-cols-1 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Статус</label>
                        <div class="space-y-2">
                            <label class="flex items-center">
                                <input type="radio" name="financial_status" value="pending_payment" class="mr-2">
                                <span class="px-3 py-1 text-sm bg-yellow-100 text-yellow-800 rounded-full">На оплате</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="financial_status" value="paid" class="mr-2">
                                <span class="px-3 py-1 text-sm bg-green-100 text-green-800 rounded-full">Оплачено</span>
                            </label>
                            <label class="flex items-center">
                                <input type="radio" name="financial_status" value="not_paid" class="mr-2">
                                <span class="px-3 py-1 text-sm bg-red-100 text-red-800 rounded-full">Не оплачено</span>
                            </label>
                        </div>
                    </div>

                    <div>
                        <label for="comment" class="block text-sm font-medium text-gray-700 mb-2">Комментарий</label>
                        <textarea name="comment" id="comment" rows="3" class="w-full border rounded-md px-3 py-2"></textarea>
                    </div>

                    <div>
                        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                            Обновить статус
                        </button>
                    </div>
                </div>
            </form>
        </div>
    @endcan

    <!-- История изменений -->
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-lg font-semibold mb-4">История изменений</h2>

        <div class="space-y-4">
            @forelse($logs as $log)
                <div class="border-b last:border-0 pb-4 last:pb-0">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="px-2 py-1 text-xs rounded-full 
                                @if($log->financial_status === 'pending_payment') bg-yellow-100 text-yellow-800
                                @elseif($log->financial_status === 'paid') bg-green-100 text-green-800
                                @else bg-red-100 text-red-800
                                @endif">
                                {{ $log->financial_status }}
                            </span>
                            <p class="text-sm text-gray-600 mt-1">{{ $log->comment }}</p>
                            <p class="text-xs text-gray-500 mt-1">{{ $log->created_at->format('d.m.Y H:i') }}</p>
                        </div>
                        <div class="text-right">
                            <p class="text-sm font-medium">{{ $log->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $log->user->role }}</p>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-gray-500">Нет истории изменений</p>
            @endforelse
        </div>

        <div class="mt-4">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection