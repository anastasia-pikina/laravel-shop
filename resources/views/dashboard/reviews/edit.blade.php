@extends('dashboard.layouts.master')
@section('content')
    <h1>Редактировать запись</h1>
    <form action="{{ route('reviews.update', $review) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="text" class="form-label">Отзыв</label>
            <input type="text" class="form-control" id="text" name="text" value="{{old('text', $review->text)}}">
            @error('text')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="is_confirmed" class="form-label">Подтвержден</label>
            <input type="hidden" name="is_confirmed" value="0">
            <input type="checkbox" name="is_confirmed" class="form-check-input" value="1" @if($review->is_confirmed==1) checked @endif />
            @error('is_confirmed')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Обновить</button>
    </form>
@endsection
