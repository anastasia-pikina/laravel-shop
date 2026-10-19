@extends('dashboard.layouts.master')
@section('content')
    <div class="flex justify-between items-center">
        <h1 class="text-2xl font-bold text-gray-800">Отзывы о товарах</h1>
    </div>
    <div class="overflow-x-auto mt-6">
        <table class="min-w-full divide-y divide-gray-200 text-sm">
            <thead class="bg-gray-50">
            <tr>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">#</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Отзыв</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Товар</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Рейтинг</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Подтвержден</th>
                <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
            </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
            @foreach($reviews as $review)
                <tr>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $review->id }}</td>
                    <td class="px-4 py-3">{{ $review->text }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $review->product_id }}</td>
                    <td class="px-4 py-3 whitespace-nowrap">{{ $review->rating }}</td>
                    <td class="px-4 py-3 whitespace-nowrap"> @if($review->is_confirmed==1) да @else нет @endif</td>
                    <td class="px-4 py-3 whitespace-nowrap">
                        <a href="{{ route('reviews.edit', $review) }}" class="bg-yellow-500 text-white px-3 py-1 rounded-md text-xs hover:bg-yellow-600 mr-2">Редактировать</a>
                        <form action="{{ route('reviews.confirm', $review) }}" method="POST" class="inline-block">
                            @csrf @method('PUT')
                            <button class="px-3 py-1 rounded-md text-xs text-white mr-2 {{ $review->is_confirmed ? 'bg-gray-500 hover:bg-gray-600' : 'bg-green-600 hover:bg-green-700' }}">
                                {{ $review->is_confirmed ? 'Отменить одобрение' : 'Одобрить' }}
                            </button>
                        </form>
                        <form action="{{ route('reviews.destroy', $review) }}" method="POST" class="inline-block">
                            @csrf @method('DELETE')
                            <button class="bg-red-600 text-white px-3 py-1 rounded-md text-xs hover:bg-red-700" onclick="return confirm('Вы уверены?')">Удалить</button>
                        </form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $reviews->links() }}</div>
@endsection
