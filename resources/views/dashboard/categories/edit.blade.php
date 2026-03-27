@extends('dashboard.layouts.master')
@section('content')
    <h1>Редактировать запись</h1>
    <form action="{{ route('categories.update', $category) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Название{{ $cats }}</label>
            <input type="text" class="form-control" id="name" name="name" value="{{old('name', $category->name)}}">
            @error('name')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Символьный код</label>
            <input type="text" class="form-control" id="code" name="code" value="{{old('code', $category->code)}}">
            @error('name')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Родительская категория</label>
            <input type="text" class="form-control" id="parent_category_id" name="parent_category_id" value="{{old('parent_category_id', $category->parent_category_id)}}">
            @error('name')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Обновить</button>
    </form>
@endsection
