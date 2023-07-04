<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\BatchController;
use App\Http\Controllers\BrandController;
use App\Http\Controllers\PresentationController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchasesController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\UnitMeasurementController;
use App\Models\Brand;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('register', [AuthController::class, 'register']);
Route::post('login',  [AuthController::class, 'login']);

Route::resource('batch',  BatchController::class);
Route::resource('brand',  BrandController::class);
Route::resource('presentation',  PresentationController::class);
Route::resource('product',  ProductController::class);
Route::resource('purchases',  PurchasesController::class);
Route::resource('sales',  SalesController::class);
Route::resource('unitMeasurement',  UnitMeasurementController::class);

Route::get('searchProduct',  [ProductController::class, 'search']);
Route::get('getSales',  [SalesController::class, 'getSales']);
Route::get('getPurchases',  [PurchasesController::class, 'getPurchases']);
