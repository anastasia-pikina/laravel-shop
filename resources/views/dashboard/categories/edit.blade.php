@extends('dashboard.layouts.master')
@section('content')
    <h1>Редактировать запись</h1>
    <form action="{{ route('categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Название</label>
            <input type="text" class="form-control" id="name" name="name" value="{{old('name', $category->name)}}">
            @error('name')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Символьный код</label>
            <input type="text" class="form-control" id="code" name="code" value="{{old('code', $category->code)}}">
            @error('code')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Родительская категория</label>
            <select class="form-select" name="parent_category_id">
                <option value="">не выбрано</option>
                @foreach ($categories as $categoryItem)
                    <option value="{{ $categoryItem->id }}"@if($categoryItem->id === (int) old('parent_category_id', $categoryItem->id)) selected @endif>{{ $categoryItem->name }}</option>
                @endforeach
            </select>
            @error('parent_category_id')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Обновить</button>
    </form>
@endsection
