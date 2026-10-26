<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use App\Services\OrderService;
use App\Models\User;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('shop:expire-orders', function (OrderService $orders) {
    $this->info($orders->expireUnpaid().' заказов отменено');
})->purpose('Отменить просроченные неоплаченные заказы');

Schedule::command('shop:expire-orders')->everyMinute()->withoutOverlapping();

Artisan::command('shop:grant-admin {email}', function (string $email) {
    $user = User::query()->where('email', $email)->firstOrFail();
    $user->forceFill(['is_admin' => true])->save();
    $this->info('Доступ к админке предоставлен: '.$email);
})->purpose('Выдать доступ к админке существующему пользователю');
