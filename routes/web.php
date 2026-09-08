<?php

use App\Http\Controllers\BlogController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\ModuleController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\SeoController;
use App\Http\Controllers\SubmissionController;
use Illuminate\Support\Facades\Route;

// SEO
Route::get('/sitemap.xml', [SeoController::class, 'sitemap']);
Route::get('/robots.txt', [SeoController::class, 'robots']);

// Búsqueda
Route::get('/buscar', [SearchController::class, 'index'])->name('search');

// Módulos dinámicos públicos (listado y detalle).
Route::get('/m/{module}', [ModuleController::class, 'index'])->name('module.index');
Route::get('/m/{module}/{entry}', [ModuleController::class, 'show'])->name('module.show');

Route::post('/enviar-mensaje', [ContactController::class, 'store'])->middleware('throttle:8,1')->name('contact.store');
Route::post('/f/{module}', [SubmissionController::class, 'store'])->middleware('throttle:8,1')->name('form.submit');
Route::post('/newsletter/suscribir', [NewsletterController::class, 'subscribe'])->middleware('throttle:5,1')->name('newsletter.subscribe');
Route::get('/newsletter/baja/{subscriber}', [NewsletterController::class, 'unsubscribe'])->middleware('signed')->name('newsletter.unsubscribe');
Route::post('/reservas', [ReservationController::class, 'store'])->middleware('throttle:8,1')->name('reservation.store');

// Carrito de compras
Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
Route::post('/carrito/agregar/{entryId}', [CartController::class, 'add'])->name('cart.add');
Route::post('/carrito/actualizar', [CartController::class, 'update'])->name('cart.update');
Route::post('/carrito/quitar', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/carrito/cupon', [CartController::class, 'applyCoupon'])->name('cart.coupon.apply');
Route::post('/carrito/cupon/quitar', [CartController::class, 'removeCoupon'])->name('cart.coupon.remove');
Route::post('/carrito/finalizar', [CartController::class, 'checkout'])->name('cart.checkout');

// Pagos con Stripe
Route::post('/pago/checkout', [PaymentController::class, 'checkout'])->name('pago.checkout');
Route::get('/pago/exito', [PaymentController::class, 'success'])->name('pago.exito');
Route::get('/pago/cancelado', [PaymentController::class, 'cancel'])->name('pago.cancelado');
Route::post('/stripe/webhook', [PaymentController::class, 'webhook'])->name('stripe.webhook');

// Pagos con PayPal
Route::post('/pago/paypal/checkout', [PayPalController::class, 'checkout'])->name('paypal.checkout');
Route::get('/pago/paypal/capturar', [PayPalController::class, 'capture'])->name('paypal.capture');
Route::get('/pago/paypal/cancelado', [PayPalController::class, 'cancel'])->name('paypal.cancel');

/*
| Contenido del sitio. Las rutas viven en la raíz; el idioma se maneja con el
| middleware global SetLocale (prefijo /en, /fr…), que reescribe la petición y
| antepone el prefijo en las URLs generadas. El catch-all va al final.
*/
Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/blog', [BlogController::class, 'index'])->name('blog.index');
Route::get('/blog/{post:slug}', [BlogController::class, 'show'])->name('blog.show');
Route::get('/{page:slug}', [PageController::class, 'show'])
    ->where('page', '^(?!admin|blog|enviar-mensaje|livewire|storage|css|js|filament|buscar|m|carrito|pago|stripe|f|newsletter|reservas|sitemap|robots).*')
    ->name('page.show');
