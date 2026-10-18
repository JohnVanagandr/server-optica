<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\Front\WidgetController;

Route::get('/w/{widget}', [WidgetController::class, 'renderIframe'])->name('widget.render');