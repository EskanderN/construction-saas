@extends('layouts.app')

@section('title', 'Создание задачи')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Создание новой задачи</h1>

        <form method="POST" action="{{ route('projects.tasks.store', $project) }}">
            @csrf

            <div class="mb-4">
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Название задачи *</label>
                <input type="text" name="title" id="title" value="{{ old('title') }}" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500 @error('title') border-red-500 @enderror">
                @error('title')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Описание</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">{{ old('description') }}</textarea>
            </div>

            <div class="mb-6">
                <label for="assigned_to" class="block text-sm font-medium text-gray-700 mb-2">Исполнитель *</label>
                <select name="assigned_to" id="assigned_to" required
                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="">Выберите исполнителя</option>
                    @foreach($siteManagers as $manager)
                        <option value="{{ $manager->id }}" {{ old('assigned_to') == $manager->id ? 'selected' : '' }}>
                            {{ $manager->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-2">
                <a href="{{ route('projects.tasks.index', $project) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Отмена
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Создать задачу
                </button>
            </div>
        </form>
    </div>
</div>
@endsection