<?php

use App\Livewire\Auth\Register;
use App\Livewire\HomePage;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('dashboard/login');
});
Route::get('/login', function () {
    return redirect(route('filament.admin.auth.login'));
})->name('login'); // fix filament route login

Route::get('/home', HomePage::class);
Route::get('signup', Register::class);
