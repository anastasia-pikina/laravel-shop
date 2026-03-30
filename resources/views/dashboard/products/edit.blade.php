
@extends('dashboard.layouts.master')
@section('content')
    <h1>Редактировать запись</h1>
    <form action="{{ route('products.update', $product) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label for="title" class="form-label">Название</label>
            <input type="text" class="form-control" id="name" name="name" value="{{old('name', $product->name)}}">
            @error('name')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Изображение</label>
            <input class="form-control" type="file" name="image" id="image">
            @error('image')
            <p class="text-danger">{{ $message }}</p>
            @enderror
            @isset($image)
                <img class="pt-1 pb-1" src="{{ $image }}" alt="{{ $product->name }}">
                <div class="form-check">
                    <input class="form-check-input" name="remove_image" type="checkbox" value="" id="remove_image">
                    <label class="form-check-label" for="remove_image">
                        Удалить изображение
                    </label>
                </div>
            @endisset
        </div>
        <div class="mb-3">
            <label for="content" class="form-label">Контент</label>
            <textarea class="form-control" id="description" name="description" rows="5">{{old('description', $product->description)}}</textarea>
            @error('description')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Цена</label>
            <input type="text" class="form-control" id="price" name="price" value="{{old('price', $product->price)}}">
            @error('price')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Категория</label>
            <select class="form-select" name="category_id">
                <option value="">не выбрано{{old('category_id')}}</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->id }}"@if($category->id === (int) old('category_id', $category->id)) selected @endif>{{ $category->name }}</option>
                @endforeach
            </select>
            @error('category_id')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Обновить</button>
    </form>
@endsection
