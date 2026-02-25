@extends('layouts.app')

@section('title', 'Добавление поставки')

@section('content')
<div class="max-w-4xl mx-auto">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h1 class="text-2xl font-bold mb-6">Добавление новой поставки</h1>

        <form method="POST" action="{{ route('projects.materials.store', $project) }}">
            @csrf

            <div class="grid grid-cols-2 gap-4">
                <div class="mb-4">
                    <label for="material_name" class="block text-sm font-medium text-gray-700 mb-2">Название материала *</label>
                    <input type="text" name="material_name" id="material_name" value="{{ old('material_name') }}" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="unit" class="block text-sm font-medium text-gray-700 mb-2">Единица измерения *</label>
                    <select name="unit" id="unit" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                        <option value="шт">Штуки</option>
                        <option value="кг">Килограммы</option>
                        <option value="т">Тонны</option>
                        <option value="м">Метры</option>
                        <option value="м2">Квадратные метры</option>
                        <option value="м3">Кубические метры</option>
                        <option value="уп">Упаковки</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label for="quantity" class="block text-sm font-medium text-gray-700 mb-2">Количество *</label>
                    <input type="number" name="quantity" id="quantity" value="{{ old('quantity') }}" step="0.01" min="0" required
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label for="delivery_date" class="block text-sm font-medium text-gray-700 mb-2">Плановая дата поставки</label>
                    <input type="date" name="delivery_date" id="delivery_date" value="{{ old('delivery_date') }}"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Кнопки -->
            <div class="flex justify-end space-x-2 mt-6">
                <a href="{{ route('projects.show', $project) }}" class="px-4 py-2 bg-gray-300 text-gray-700 rounded-md hover:bg-gray-400">
                    Отмена
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                    Добавить поставку
                </button>
            </div>
        </form>
    </div>
</div>
@endsection