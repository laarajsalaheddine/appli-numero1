<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MainController;


Route::get('/', [MainController::class, "index"]);
Route::get('/show', [MainController::class, "show"])->name('afficher-liste');
Route::get('/delete/{id}', [MainController::class, "delete"])->name('supprimer-element');
Route::get('/edit/{id}', [MainController::class, "edit"])->name('editer-element');
Route::post('/edit', [MainController::class, "edit"])->name('editer-element');

