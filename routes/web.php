<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');

// Módulos dinámicos públicos (listado y detalle).
Route::get('/m/{module}', [ModuleController::class, 'index'])->name('module.index');
Route::get('/m/{module}/{entry}', [ModuleController::class, 'show'])->name('module.show');

Route::post('/enviar-mensaje', [ContactController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('contact.store');

// Envío de formularios de módulos tipo "formulario".
Route::post('/f/{module}', [SubmissionController::class, 'store'])
    ->middleware('throttle:8,1')
    ->name('form.submit');

// Carrito de compras (pedido por WhatsApp).
Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrito/agregar/{entryId}', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrito/actualizar', [CartController::class, 'update'])->name('cart.update');
Route::post('/carrito/quitar/{entryId}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/carrito/finalizar', [CartController::class, 'checkout'])->name('cart.checkout');

// Páginas dinámicas por slug — debe ir al final (captura cualquier ruta restante).
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', '^(?!admin|blog|enviar-mensaje|livewire|storage|css|js|filament).*')
    ->name('page.show');
