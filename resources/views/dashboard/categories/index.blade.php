@extends('dashboard.layouts.master')
@section('content')
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Категории</h1>
        <div class="flex gap-2">
            <a href="{{ route('dashboard.products.create', request()->has('parent_id') ? ['category_id' => request('parent_id')] : []) }}" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Создать товар</a>
            <a href="{{ route('dashboard.categories.create', request()->has('parent_id') ? ['parent_category_id' => request('parent_id')] : []) }}" class="bg-indigo-600 text-white px-4 py-2 rounded-md hover:bg-indigo-700">Создать подкатегорию</a>
        </div>
    </div>
    @if(count($breadcrumbs) > 0)
        <nav aria-label="breadcrumb" class="mt-4">
            <ol class="flex items-center space-x-2 text-sm text-gray-600">
                <li><a href="{{ route('dashboard.categories.index') }}" class="hover:text-indigo-600">Корневые</a></li>
                @foreach($breadcrumbs as $crumb)
                    @if($loop->last)
                        <li class="text-gray-900 font-medium">{{ $crumb->name }}</li>
                    @else
                        <li><a href="{{ route('dashboard.categories.index', ['parent_id' => $crumb->id]) }}" class="hover:text-indigo-600">{{ $crumb->name }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif

    @if($categories->count())
        <h2 class="text-lg font-semibold text-gray-800 mt-6">Подкатегории</h2>
        <div class="overflow-x-auto mt-2">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заголовок</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Код</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                @foreach($categories as $category)
                    <tr>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $category->id }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('dashboard.categories.index', ['parent_id' => $category->id]) }}" class="text-indigo-600 hover:underline">{{ $category->name }}</a>
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap">{{ $category->code }}</td>
                        <td class="px-4 py-3 whitespace-nowrap">
                            <a href="{{ route('dashboard.categories.edit', $category) }}" class="bg-yellow-500 text-white px-3 py-1 rounded-md text-xs hover:bg-yellow-600 mr-2">Редактировать</a>
                            <form action="{{ route('dashboard.categories.destroy', $category) }}" method="POST" class="inline-block">
                                @csrf @method('DELETE')
                                <button class="bg-red-600 text-white px-3 py-1 rounded-md text-xs hover:bg-red-700" onclick="return confirm('Вы уверены?')">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $categories->links() }}</div>
    @endif

    @php $parentId = request('parent_id'); @endphp
    <h2 class="text-lg font-semibold text-gray-800 mt-6">Товары</h2>
    <div class="overflow-x-auto mt-2">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заголовок</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Изображение</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Цена</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @forelse($products as $product)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $product->id }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $product->name }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        @if($product->image)
                            <img class="my-1" src="{{ route('image.crop', ['path' => 'product/source/' . $product->image, 'w' => 100, 'h' => 100]) }}" alt="{{ $product->name }}" style="max-height: 60px;">
                        @endif
                    </td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $product->price }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <a href="{{ route('products.edit', $product) }}" class="bg-yellow-500 text-white px-3 py-1 rounded-md text-xs hover:bg-yellow-600 mr-2">Редактировать</a>
                        <form action="{{ route('products.destroy', $product) }}" method="POST" class="inline-block">
                            @csrf @method('DELETE')
                            <button class="bg-red-600 text-white px-3 py-1 rounded-md text-xs hover:bg-red-700" onclick="return confirm('Вы уверены?')">Удалить</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-4 py-3 text-center text-gray-500">Нет товаров в этой категории</td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $products->links() }}</div>
@endsection
