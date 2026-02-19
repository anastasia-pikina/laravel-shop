@extends('dashboard.layouts.master')
@section('content')
    <div class="d-flex justify-content-between align-items-center">
        <h1>Отзывы о товарах</h1>
        <a href="{{ route('reviews.create') }}" class="btn btn-primary">Создать отзыв</a>
    </div>
    <table class="table mt-4">
        <thead>
        <tr>
            <th>#</th>
            <th>Отзыв</th>
            <th>Товар</th>
            <th>Рейтинг</th>
            <th>Подтвержден</th>
            <th>Действия</th>
        </tr>
        </thead>
        <tbody>
        @foreach($reviews as $review)
            <tr>
                <td>{{ $review->id }}</td>
                <td>{{ $review->text }}</td>
                <td>{{ $review->product_id }}</td>
                <td>{{ $review->rating }}</td>
                <td>{{ $review->is_confirmed }}</td>
                <td>
                    <a href="{{ route('reviews.edit', $review) }}" class="btn btn-warning btn-sm">Редактировать</a>
                    <a href="{{ route('reviews.confirm', $review) }}" class="btn btn-warning btn-sm">Опубликовать</a>
                    <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="d-inline-block">
                        @csrf @method('DELETE')
                        <button class="btn btn-danger btn-sm" onclick="return confirm('Вы уверены?')">Удалить</button>
                    </form>
                </td>
            </tr>
        @endforeach
        </tbody>
    </table>
    {{ $reviews->links() }}
@endsection
