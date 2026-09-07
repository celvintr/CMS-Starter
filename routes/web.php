<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PageController::class, 'home'])->name('home');

// SEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);

// Búsqueda
Route::get('/buscar', [\App\Http\Controllers\SearchController::class, 'index'])->name('search');

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

// Pagos con Stripe (checkout hosteado).
Route::post('/pago/checkout', [PaymentController::class, 'checkout'])->name('pago.checkout');
Route::get('/pago/exito', [PaymentController::class, 'success'])->name('pago.exito');
Route::get('/pago/cancelado', [PaymentController::class, 'cancel'])->name('pago.cancelado');
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

// Pagos con PayPal (aprobar + capturar).
Route::post('/pago/paypal/checkout', [PayPalController::class, 'checkout'])->name('paypal.checkout');
Route::get('/pago/paypal/capturar', [PayPalController::class, 'capture'])->name('paypal.capture');
Route::get('/pago/paypal/cancelado', [PayPalController::class, 'cancel'])->name('paypal.cancel');

// Páginas dinámicas por slug — debe ir al final (captura cualquier ruta restante).
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', '^(?!admin|blog|enviar-mensaje|livewire|storage|css|js|filament).*')
    ->name('page.show');
