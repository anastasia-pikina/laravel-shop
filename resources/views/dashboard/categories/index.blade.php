@extends('dashboard.layouts.master')
@section('content')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Записи</h1>
        <a href="{{ route('categories.create') }}" class="btn btn-primary">Создать запись</a>
    </div>
    <table class="table mt-4">
        <thead>
        <tr>
            <th>#</th>
            <th>Заголовок</th>
            <th>Код</th>
            <th>Родительская категория</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @foreach($categories as $category)
            <tr>
                <td>{{ $category->id }}</td>
                <td>{{ $category->name }}</td>
                <td>{{ $category->code }}</td>
                <td>{{ $category->parent ? $category->parent->name : '' }}</td>
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
@endsection
