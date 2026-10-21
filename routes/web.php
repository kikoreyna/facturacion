<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\ClientController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ServiceOrderController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\TariffListController;

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('clients', ClientController::class)->except(['show', 'destroy']);
    Route::resource('services', ServiceController::class)->except(['show', 'destroy']);
    Route::resource('tariff-lists', TariffListController::class)->except(['show', 'destroy']);

    Route::prefix('service-orders')->name('service-orders.')->group(function () {
        Route::get('/', [ServiceOrderController::class, 'index'])->name('index');
        Route::get('create', [ServiceOrderController::class, 'create'])->name('create');
        Route::post('/', [ServiceOrderController::class, 'store'])->name('store');
        Route::get('{serviceOrder}', [ServiceOrderController::class, 'show'])->name('show');
        Route::get('{serviceOrder}/edit', [ServiceOrderController::class, 'edit'])->name('edit');
        Route::put('{serviceOrder}', [ServiceOrderController::class, 'update'])->name('update');
        Route::post('{serviceOrder}/items', [ServiceOrderController::class, 'storeItem'])->name('items.store');
        Route::put('{serviceOrder}/items/{item}', [ServiceOrderController::class, 'updateItem'])->name('items.update');
        Route::delete('{serviceOrder}/items/{item}', [ServiceOrderController::class, 'destroyItem'])->name('items.destroy');
        Route::post('{serviceOrder}/logs', [ServiceOrderController::class, 'storeLog'])->name('logs.store');
        Route::patch('{serviceOrder}/status', [ServiceOrderController::class, 'updateStatus'])->name('status');
    });

    Route::get('invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('invoices/{invoice}/pdf', [InvoiceController::class, 'pdf'])->name('invoices.pdf');
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'updateStatus'])->name('invoices.status');
});
