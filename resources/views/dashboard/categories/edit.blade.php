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
            <input type="text" class="form-control" id="code" name="code" value="{{old('code', $category->code)}}" readonly>
            @error('code')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <div class="mb-3">
            <label for="title" class="form-label">Родительская категория</label>
            <select class="form-select" name="parent_category_id">
                <option value="">не выбрано</option>
                @foreach ($categories as $categoryItem)
                    <option value="{{ $categoryItem->id }}"@if($categoryItem->id === (int) old('parent_category_id', $category->parent_category_id)) selected @endif>{{ $categoryItem->name }}</option>
                @endforeach
            </select>
            @error('parent_category_id')
            <p class="text-danger">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit" class="btn btn-success">Обновить</button>
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
