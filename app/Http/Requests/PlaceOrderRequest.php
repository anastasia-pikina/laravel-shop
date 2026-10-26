<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceOrderRequest extends FormRequest
{
    protected function prepareForValidation(): void
    {
        $this->merge(['checkout_key' => $this->header('Idempotency-Key')]);
    }

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'checkout_key' => ['required', 'uuid'],
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:32'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.product_id' => ['required', 'integer', 'distinct', 'exists:products,id'],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:999'],
            'payment_method' => ['required', Rule::in(config('commerce.online_enabled') ? ['online', 'on_delivery'] : ['on_delivery'])],
            'delivery.method' => ['required', Rule::in(['courier', 'pickup'])],
            'delivery.recipient_name' => ['required', 'string', 'max:255'],
            'delivery.recipient_phone' => ['required', 'string', 'max:32'],
            'delivery.pickup_point_id' => ['required_if:delivery.method,pickup', 'nullable', 'integer', 'exists:pickup_points,id'],
            'delivery.postal_code' => ['nullable', 'string', 'max:16'],
            'delivery.region' => ['nullable', 'string', 'max:255'],
            'delivery.city' => ['required_if:delivery.method,courier', 'nullable', 'string', 'max:255'],
            'delivery.street' => ['required_if:delivery.method,courier', 'nullable', 'string', 'max:255'],
            'delivery.house' => ['required_if:delivery.method,courier', 'nullable', 'string', 'max:64'],
            'delivery.apartment' => ['nullable', 'string', 'max:64'],
        ];
    }
}
