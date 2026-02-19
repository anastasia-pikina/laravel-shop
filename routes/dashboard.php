<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('dashboard.app');
})->middleware(['auth', 'verified'])->name('dashboard.home');
