<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'price' => 'required|numeric',
            'description' => 'nullable',
            'category_id' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg,webp'
        ];
    }

    public function messages() {
        return [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'price.required' => 'Поле "Цена" обязательно для заполнения.',
            'price.numeric' => 'Поле "Цена" должно быть числом.',
            'category_id' => 'Поле "Категория" обязательно для заполнения.',
            'image' => 'Изображение может быть только следующих форматов: jpeg, png, jpg, gif, webp, svg.',
        ];
    }
}
