@extends('dashboard.layouts.master')
@section('content')
    <h1 class="text-2xl font-bold">Точки самовывоза</h1>
    @if($errors->any()) <div class="mt-4 text-red-600">{{ $errors->first() }}</div> @endif
    <form action="{{ route('dashboard.pickup-points.store') }}" method="POST" class="mt-6 flex flex-wrap gap-2">
        @csrf
        <input name="code" placeholder="Код" required class="rounded border-gray-300">
        <input name="name" placeholder="Название" required class="rounded border-gray-300">
        <input name="address" placeholder="Адрес" required class="min-w-80 rounded border-gray-300">
        <button class="rounded bg-indigo-600 px-4 py-2 text-white">Добавить</button>
    </form>
    <div class="mt-6 space-y-4">
        @foreach($points as $point)
            <form action="{{ route('dashboard.pickup-points.update', $point) }}" method="POST" class="flex flex-wrap items-center gap-2 rounded bg-white p-4 shadow">
                @csrf @method('PUT')
                <input name="code" value="{{ $point->code }}" required class="rounded border-gray-300">
                <input name="name" value="{{ $point->name }}" required class="rounded border-gray-300">
                <input name="address" value="{{ $point->address }}" required class="min-w-80 rounded border-gray-300">
                <select name="is_active" class="rounded border-gray-300">
                    <option value="1" @selected($point->is_active)>Активна</option>
                    <option value="0" @selected(!$point->is_active)>Выключена</option>
                </select>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">Сохранить</button>
            </form>
        @endforeach
    </div>
@endsection
