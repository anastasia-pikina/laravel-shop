@extends('dashboard.layouts.master')
@section('content')
    <h1 class="text-2xl font-bold text-gray-800">Создать запись</h1>
    <form action="{{ route('categories.store') }}" method="POST" class="max-w-lg mt-6 space-y-4">
        @csrf
        <div>
            <label for="name" class="block text-sm font-medium text-gray-700">Название</label>
            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="name" name="name" value="{{old('name')}}">
            @error('name')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="code" class="block text-sm font-medium text-gray-700">Символьный код</label>
            <input type="text" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" id="code" name="code" value="{{old('code')}}" readonly>
            @error('code')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label for="parent_category_id" class="block text-sm font-medium text-gray-700">Родительская категория</label>
            <select class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500" name="parent_category_id" id="parent_category_id">
                <option value="">не выбрано</option>
                @foreach ($categories as $categoryItem)
                    <option value="{{ $categoryItem->id }}"@if($categoryItem->id === (int) old('parent_category_id', $parentCategoryId ?? null)) selected @endif>{{ $categoryItem->name }}</option>
                @endforeach
            </select>
            @error('parent_category_id')
            <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700">Сохранить</button>
    </form>
    @push('scripts')
    <script>
        function transliterate(str) {
            const map = {
                'а':'a','б':'b','в':'v','г':'g','д':'d','е':'e','ё':'e',
                'ж':'zh','з':'z','и':'i','й':'y','к':'k','л':'l','м':'m',
                'н':'n','о':'o','п':'p','р':'r','с':'s','т':'t','у':'u',
                'ф':'f','х':'kh','ц':'ts','ч':'ch','ш':'sh','щ':'shch',
                'ъ':'','ы':'y','ь':'','э':'e','ю':'yu','я':'ya'
            };
            return str.toLowerCase()
                .replace(/[а-яё]/g, c => map[c] || c)
                .replace(/[^a-z0-9]+/g, '-')
                .replace(/^-+|-+$/g, '');
        }

        document.getElementById('name').addEventListener('input', function () {
            document.getElementById('code').value = transliterate(this.value);
        });
    </script>
    @endpush
@endsection
