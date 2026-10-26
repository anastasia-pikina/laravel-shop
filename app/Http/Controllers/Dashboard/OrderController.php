<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class OrderController extends Controller
{
    public function index()
    {
        return view('dashboard.orders.index', ['orders' => Order::query()->latest()->paginate(20)]);
    }

    public function show(Order $order)
    {
        return view('dashboard.orders.show', ['order' => $order->load('items', 'delivery', 'payments.refunds')]);
    }

    public function delivery(Request $request, Order $order, OrderService $orders)
    {
        $data = $request->validate(['status' => ['required', Rule::in(['preparing', 'ready_for_pickup', 'in_transit', 'delivered', 'failed'])]]);
        $orders->updateDelivery($order, $data['status']);

        return back()->with('global', 'Статус доставки обновлен.');
    }

    public function markPaid(Order $order, OrderService $orders)
    {
        $orders->markPaymentAtDelivery($order);

        return back()->with('global', 'Оплата отмечена.');
    }

    public function cancel(Order $order, OrderService $orders)
    {
        $orders->cancel($order);

        return back()->with('global', 'Заказ отменен.');
    }

    public function returned(Order $order, OrderService $orders)
    {
        $orders->returnFailedDelivery($order);

        return back()->with('global', 'Товар возвращен на склад.');
    }
}
