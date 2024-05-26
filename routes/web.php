<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\GoogleController;

Route::get('/', function () {
    return view('welcome');
});

Route::get("/token", [GoogleController::class, "getAccessToken"])->name("api.me");
Route::get("/read", [GoogleController::class, "readAll"])->name("api.readAll");
Route::get("/list", [GoogleController::class, "listThreads"])->name("api.list");
Route::get("/search", [GoogleController::class, "listSearch"])->name("api.search");
Route::post("/exposed", [GoogleController::class, "webhook"])->name("api.webhook");