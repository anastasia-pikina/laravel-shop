@extends('dashboard.layouts.master')
@section('content')
    <h1 class="text-2xl font-bold">Заказы</h1>
    <div class="mt-6 overflow-x-auto rounded bg-white shadow">
        <table class="min-w-full text-left text-sm">
            <thead><tr class="border-b"><th class="p-3">Номер</th><th class="p-3">Дата</th><th class="p-3">Покупатель</th><th class="p-3">Сумма</th><th class="p-3">Статус</th></tr></thead>
            <tbody>
            @foreach($orders as $order)
                <tr class="border-b">
                    <td class="p-3"><a class="text-indigo-600" href="{{ route('dashboard.orders.show', $order) }}">{{ $order->number }}</a></td>
                    <td class="p-3">{{ $order->placed_at->format('d.m.Y H:i') }}</td>
                    <td class="p-3">{{ $order->customer_name }}</td>
                    <td class="p-3">{{ $order->total }} ₽</td>
                    <td class="p-3">{{ $order->status }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    <div class="mt-4">{{ $orders->links() }}</div>
@endsection
