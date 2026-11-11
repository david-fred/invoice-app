<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;

Route::get('/clients', ClientIndex::class)->name('clients.index');
Route::get('/clients/create', ClientForm::class)->name('clients.create');
Route::get('/clients/{client}/edit', ClientForm::class)->name('clients.edit');
Route::view('/', 'welcome')->name('home');
