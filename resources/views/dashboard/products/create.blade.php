@extends('dashboard.layouts.master')
@section('content')
    <h1>Создать запись</h1>
    <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="mb-3">
            <label for="title" class="form-label">Название</label>
            <input type="text" class="form-control" id="name" name="name" value="{{old('name')}}">
            @error('name')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Изображение</label>
            <input type="file" name="image" id="image" class="form-control">
            @error('image')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Описание</label>
            <textarea class="form-control" id="description" name="description" rows="5">{{old('description')}}</textarea>
            @error('description')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Цена</label>
            <input type="text" class="form-control" id="price" name="price" value="{{old('price')}}">
            @error('price')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Категория</label>
            <select class="form-select" name="category_id">
                <option value="">не выбрано</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"@if((int) old('category_id', $selectedCategoryId ?? 0) === $category->id) selected @endif>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Сохранить</button>
    </form>
@endsection