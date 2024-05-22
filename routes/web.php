<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/me", [GoogleController::class, "getAccessToken"])->name("api.me");
Route::get("/read", [GoogleController::class, "readAll"])->name("api.readAll");
Route::get("/read-me", [GoogleController::class, "listThreads"])->name("api.list");