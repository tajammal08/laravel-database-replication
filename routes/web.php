<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/a', function () {
    \App\Models\User::chunk(10, function ($users) {
        Log::debug('Data',[$users]);
    });
});

Route::get('/b', function () {
    \App\Models\User::chunk(10, function ($users) {
        Log::debug('Data',[$users]);
    });
});
