@extends('dashboard.layouts.master')
@section('content')
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Записи</h1>
        <a href="{{ route('dashboard.products.create') }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Создать запись</a>
    </div>
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заголовок</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Изображение</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Категория</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($products as $product)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $product->id }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $product->name }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($product->image)
                        <img class="my-1" src="{{ route('image.crop', ['path' => 'product/source/' . $product->image, 'w' => 200, 'h' => 200]) }}" alt="{{ $product->name }}">
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $product->price }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $product->category->name }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <a href="{{ route('dashboard.products.edit', $product) }}" class="bg-yellow-500 text-white px-3 py-1 rounded-md text-xs hover:bg-yellow-600 mr-2">Редактировать</a>
                        <form action="{{ route('dashboard.products.destroy', $product) }}" method="POST" class="inline-block">
                            @csrf @method('DELETE')
                            <button class="bg-red-600 text-white px-3 py-1 rounded-md text-xs hover:bg-red-700" onclick="return confirm('Вы уверены?')">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
