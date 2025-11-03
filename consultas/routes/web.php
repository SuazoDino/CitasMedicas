<?php
use Illuminate\Support\Facades\Route;

Route::view('/', 'app');
Route::view('/login', 'app')->name('login');
Route::view('/{any}', 'app')->where('any', '.*');
