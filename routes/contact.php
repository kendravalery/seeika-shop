<?php

use App\Http\Controllers\Shop\PageController;
use Illuminate\Support\Facades\Route;

Route::get('/contact-us', [PageController::class, 'contactUsShow'])->name('contact.show');

Route::post('/contact-us', [PageController::class, 'contactUs'])->name('contact.store')->middleware('throttle:5,1');
