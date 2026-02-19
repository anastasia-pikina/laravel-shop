@extends('dashboard.layouts.master')
@section('content')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Записи</h1>
        <a href="{{ route('products.create') }}" class="btn btn-primary">Создать запись</a>
    </div>
    <table class="table mt-4">
        <thead>
        <tr>
            <th>#</th>
            <th>Заголовок</th>
            <th>Цена</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @foreach($products as $product)
            <tr>
                <td>{{ $product->id }}</td>
                <td>{{ $product->name }}</td>
                <td>{{ $product->price }}</td>
                <td>
                    <a href="{{ route('products.edit', $product) }}" class="btn btn-warning btn-sm">Редактировать</a>
                    <form action="{{ route('products.destroy', $product) }}" method="POST" class="d-inline-block">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')">Удалить</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $products->links() }}
@endsection
