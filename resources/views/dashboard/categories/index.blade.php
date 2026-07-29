@extends('dashboard.layouts.master')
@section('content')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Категории</h1>
        <div>
            <a href="{{ route('products.create', request()->has('parent_id') ? ['category_id' => request('parent_id')] : []) }}" class="btn btn-success">Создать товар</a>
            <a href="{{ route('categories.create', request()->has('parent_id') ? ['parent_category_id' => request('parent_id')] : []) }}" class="btn btn-primary">Создать подкатегорию</a>
        </div>
    </div>
    @if(count($breadcrumbs) > 0)
        <nav aria-label="breadcrumb" class="mt-3">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('categories.index') }}">Корневые</a></li>
                @foreach($breadcrumbs as $crumb)
                    @if($loop->last)
                        <li class="breadcrumb-item active" aria-current="page">{{ $crumb->name }}</li>
                    @else
                        <li class="breadcrumb-item"><a href="{{ route('categories.index', ['parent_id' => $crumb->id]) }}">{{ $crumb->name }}</a></li>
                    @endif
                @endforeach
            </ol>
        </nav>
    @endif

    @if($categories->count())
        <h5 class="mt-4">Подкатегории</h5>
        <table class="table mt-2">
            <thead>
            <tr>
                <th>#</th>
                <th>Заголовок</th>
                <th>Код</th>
                <th>Действия</th>
            </tr>
            </thead>
            <tbody>
            @foreach($categories as $category)
                <tr>
                    <td>{{ $category->id }}</td>
                    <td>
                        <a href="{{ route('categories.index', ['parent_id' => $category->id]) }}">{{ $category->name }}</a>
                    </td>
                    <td>{{ $category->code }}</td>
                    <td>
                        <a href="{{ route('categories.edit', $category) }}" class="btn btn-warning btn-sm">Редактировать</a>
                        <form action="{{ route('categories.destroy', $category) }}" method="POST" class="d-inline-block">
                            @csrf @method('DELETE')
                            <button class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
        {{ $categories->links() }}
    @endif

    @php $parentId = request('parent_id'); @endphp
    <h5 class="mt-4">Товары</h5>
    <table class="table mt-2">
        <thead>
        <tr>
            <th>#</th>
            <th>Заголовок</th>
            <th>Изображение</th>
            <th>Цена</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @forelse($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>
                    @if($product->image)
                        <img class="pt-1 pb-1" src="{{ route('image.crop', ['path' => 'product/source/' . $product->image, 'w' => 100, 'h' => 100]) }}" alt="{{ $product->name }}" style="max-height: 60px;">
                    @endif
                </td>
                <td>{{ $product->price }}</td>
                <td>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Редактировать</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline-block">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')">Удалить</button>
                    </form>
                </td>
            </tr>
        @empty
            <tr>
                <td colspan="5" class="text-center">Нет товаров в этой категории</td>
            </tr>
        @endforelse
        </tbody>
    </table>
    {{ $products->links() }}
@endsection