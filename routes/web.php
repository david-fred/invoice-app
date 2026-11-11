<?php

use Illuminate\Support\Facades\Route;
use App\Livewire\Clients\Form as ClientForm;
use App\Livewire\Clients\Index as ClientIndex;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Livewire\Invoices\Show as InvoiceShow;
use App\Http\Controllers\DownloadInvoicePdfController;

Route::get('/invoices/{invoice}/pdf', DownloadInvoicePdfController::class)->name('invoices.pdf');

Route::get('/invoices', InvoiceIndex::class)->name('invoices.index');
Route::get('/invoices/create', InvoiceForm::class)->name('invoices.create');
Route::get('/invoices/{invoice}', InvoiceShow::class)->name('invoices.show');
Route::get('/invoices/{invoice}/edit', InvoiceForm::class)->name('invoices.edit');

Route::get('/clients', ClientIndex::class)->name('clients.index');
Route::get('/clients/create', ClientForm::class)->name('clients.create');
Route::get('/clients/{client}/edit', ClientForm::class)->name('clients.edit');

Route::get('/dashboard', fn () => redirect()->route('invoices.index'))->name('dashboard');

Route::view('/', 'welcome')->name('home');
