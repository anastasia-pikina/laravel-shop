@extends('dashboard.layouts.master')
@section('content')
    <h1 class="text-2xl font-bold text-gray-800">Редактировать запись</h1>
    <form action="{{ route('dashboard.products.update', $product) }}" method="POST" enctype="multipart/form-data" class="max-w-lg mt-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="name" name="name" value="{{old('name', $product->name)}}">
            @error('name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="image" class="block text-sm font-medium text-gray-700">Изображение</label>
            <input class="mt-1 block w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100" type="file" name="image" id="image">
            @error('image')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
            @isset($product->image)
                <img class="my-2 max-h-40" src="{{ route('image.crop', ['path' => 'product/source/' . $product->image, 'w' => 500, 'h' => 500]) }}" alt="{{ $product->name }}">
                <div>
                    <label class="inline-flex items-center">
                        <input class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" name="remove_image" type="checkbox" value="1" id="remove_image">
                        <span class="ms-2 text-sm text-gray-700">Удалить изображение</span>
                    </label>
                </div>
            @endisset
        </div>
        <div>
            <label for="description" class="block text-sm font-medium text-gray-700">Контент</label>
            <textarea class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="description" name="description" rows="5">{{old('description', $product->description)}}</textarea>
            @error('description')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="price" class="block text-sm font-medium text-gray-700">Цена</label>
            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="price" name="price" value="{{old('price', $product->price)}}">
            @error('price')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="category_id" class="block text-sm font-medium text-gray-700">Категория</label>
            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" name="category_id" id="category_id">
                <option value="">не выбрано</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"@if($category->id === (int) old('category_id', $product->category_id)) selected @endif>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <input type="hidden" name="is_active" value="0">
            <label><input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active))> Активен в каталоге</label>
        </div>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Обновить</button>
    </form>
    <div class="max-w-lg mt-8 rounded bg-white p-4 shadow">
        <h2 class="text-xl font-semibold">Остаток</h2>
        <p class="mt-2">Артикул: {{ $product->sku }}</p>
        <p>На складе: {{ $product->inventory?->on_hand ?? 0 }}, резерв: {{ $product->inventory?->reserved ?? 0 }}</p>
        <form action="{{ route('dashboard.inventory.adjust', $product) }}" method="POST" class="mt-4 flex gap-2">
            @csrf
            <input name="delta" type="number" required placeholder="Изменение (+/-)" class="rounded border-gray-300">
            <button class="rounded bg-indigo-600 px-4 py-2 text-white">Применить</button>
        </form>
        @error('quantity') <p class="text-red-600">{{ $message }}</p> @enderror
        @error('delta') <p class="text-red-600">{{ $message }}</p> @enderror
    </div>
@endsection
