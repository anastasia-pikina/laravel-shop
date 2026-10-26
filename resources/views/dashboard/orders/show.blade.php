@extends('dashboard.layouts.master')
@section('content')
    <h1 class="text-2xl font-bold">Заказ {{ $order->number }}</h1>
    @if($errors->any()) <div class="mt-4 text-red-600">{{ $errors->first() }}</div> @endif
    <div class="mt-6 grid gap-6 lg:grid-cols-2">
        <section class="rounded bg-white p-4 shadow">
            <h2 class="text-lg font-semibold">Покупатель и товары</h2>
            <p>{{ $order->customer_name }} · {{ $order->customer_email }} · {{ $order->customer_phone }}</p>
            <p>Статус: {{ $order->status }}</p>
            <ul class="mt-4 divide-y">
                @foreach($order->items as $item)
                    <li class="py-2">{{ $item->name }} ({{ $item->sku }}) — {{ $item->quantity }} × {{ $item->unit_price }} ₽ = {{ $item->total }} ₽</li>
                @endforeach
            </ul>
            <p class="mt-3">Товары: {{ $order->subtotal }} ₽; доставка: {{ $order->delivery_amount }} ₽</p>
            <p class="font-bold">Итого: {{ $order->total }} ₽</p>
        </section>
        <section class="rounded bg-white p-4 shadow">
            <h2 class="text-lg font-semibold">Доставка</h2>
            <p>{{ $order->delivery->method }} · {{ $order->delivery->status }}</p>
            <p>{{ $order->delivery->recipient_name }} · {{ $order->delivery->recipient_phone }}</p>
            <p>{{ $order->delivery->address_snapshot ?: implode(', ', array_filter([$order->delivery->postal_code, $order->delivery->region, $order->delivery->city, $order->delivery->street, $order->delivery->house, $order->delivery->apartment])) }}</p>
            <form action="{{ route('dashboard.orders.delivery', $order) }}" method="POST" class="mt-4 flex gap-2">
                @csrf @method('PUT')
                <select name="status" class="rounded border-gray-300">
                    @foreach(['preparing', 'ready_for_pickup', 'in_transit', 'delivered', 'failed'] as $status)
                        <option value="{{ $status }}">{{ $status }}</option>
                    @endforeach
                </select>
                <button class="rounded bg-indigo-600 px-4 py-2 text-white">Обновить</button>
            </form>
        </section>
        <section class="rounded bg-white p-4 shadow">
            <h2 class="text-lg font-semibold">Оплата</h2>
            @foreach($order->payments as $payment)
                <p>{{ $payment->method }} · {{ $payment->status }} · {{ $payment->amount }} ₽</p>
                @foreach($payment->refunds as $refund)
                    <p>Возврат: {{ $refund->status }} · {{ $refund->amount }} ₽</p>
                @endforeach
            @endforeach
            @if($order->payment_method === 'on_delivery' && $order->delivery->status === 'delivered' && in_array($order->status, ['confirmed', 'processing']))
                <form action="{{ route('dashboard.orders.paid', $order) }}" method="POST" class="mt-4">@csrf
                    <button class="rounded bg-green-600 px-4 py-2 text-white">Отметить оплату при получении</button>
                </form>
            @endif
        </section>
    </div>
    @if(in_array($order->status, ['awaiting_payment', 'confirmed', 'processing']) && !$order->delivery->handed_over_at)
        <form action="{{ route('dashboard.orders.cancel', $order) }}" method="POST" class="mt-6">@csrf
            <button class="rounded bg-red-600 px-4 py-2 text-white">Отменить заказ</button>
        </form>
    @endif
    @if($order->delivery->status === 'failed')
        <form action="{{ route('dashboard.orders.returned', $order) }}" method="POST" class="mt-6">@csrf
            <button class="rounded bg-yellow-600 px-4 py-2 text-white">Подтвердить физический возврат товара</button>
        </form>
    @endif
@endsection
