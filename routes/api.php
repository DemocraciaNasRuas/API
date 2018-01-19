<?php

use Illuminate\Http\Request;

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

Route::apiResource('addresses', 'AddressesController')->middleware('auth:api');
Route::apiResource('cities', 'CitiesController')->middleware('auth:api');
Route::apiResource('events', 'EventsController')->middleware('auth:api');
Route::apiResource('states', 'StatesController')->middleware('auth:api');
