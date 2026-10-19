@extends('dashboard.layouts.master')
@section('content')
    <h1 class="text-2xl font-bold text-gray-800">Редактировать запись</h1>
    <form action="{{ route('reviews.update', $review) }}" method="POST" class="max-w-lg mt-6 space-y-4">
        @csrf
        @method('PUT')
        <div>
            <label for="text" class="block text-sm font-medium text-gray-700">Отзыв</label>
            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="text" name="text" value="{{old('text', $review->text)}}">
            @error('text')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Подтвержден</label>
            <input type="hidden" name="is_confirmed" value="0">
            <label class="inline-flex items-center mt-1">
                <input type="checkbox" name="is_confirmed" class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500" value="1" @if($review->is_confirmed==1) checked @endif />
                <span class="ms-2 text-sm text-gray-700">Подтвержден</span>
            </label>
            @error('is_confirmed')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Обновить</button>
    </form>
@endsection
