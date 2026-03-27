<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ProductCategoryRequest extends FormRequest
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
            'code' => 'required|string|max:255',
            'parent_category_id' => 'nullable|integer|exists:product_categories,id',
        ];
    }

    public function messages() {
        return [
            'name.required' => 'Поле "Название" обязательно для заполнения.',
            'code.required' => 'Поле "Символьный код" обязательно для заполнения.',
           // 'parent_category_id.integer' => 'Поле "Символьный код" обязательно для заполнения.',
        ];
    }
}
