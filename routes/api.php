<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\APIAccountDataLoad; 
use App\Http\Controllers\API\APIUser; 
use App\Http\Controllers\TasksController;
use App\Http\Controllers\API\APIUserExportController;
use App\Http\Controllers\Api\APISyncronize;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::post('login',[APIUser::class,'login']);
// Route::post('register', [APIUser::class,'register']);
// Route::get('ping',[APIUser::class,'ping']);

Route::group(['middleware' => 'auth:api'], function(){
    Route::post('account/data/load/save',[APIAccountDataLoad::class,'postAccountDataLoad']);
    Route::post('account/data/load/csr/save',[APIAccountDataLoad::class,'postAccountDataLoadCSR']);
    Route::post('user/token/logout',[APIUser::class,'logoutAPI']);
});
Route::post('user/register',[APIUser::class,'register']);
Route::post('user/token/register',[APIUser::class,'registerAPI']);
Route::post('user/token/login',[APIUser::class,'loginAPI']); 


// Syncronisasi data
Route::get('solvemate/users', [APIUserExportController::class, 'index'])->middleware('validate.solvemate');

// Syncronisasi data dari provider ke consumer
// Route::get('/sync/locations', [APISyncronize::class, 'syncLocations']); // without API Key 
Route::get('/sync/locations', [APISyncronize::class, 'syncLocations'])->middleware('validate.apikey');

 

// Route::get('/sync-from-dummy-api', [TasksController::class, 'sync']); 
// Route::post('account/organization',[APIAccountMatrix::class,'postMasterOrganization']); 

// Route::post('sales/save',[APISales::class,'salesSave']);
// Route::get('sales/today/store/{storeid}',[APISales::class,'salesTodayStore']);
// Route::get('sales/id/{saleid}',[APISales::class,'salesById']);
// Route::get('sales/today/topten/store/{storeid}',[APISales::class,'salesTodayStoreTopTen']);
// Route::get('sales/cancel/find/{storeid}/{saleno}',[APISales::class,'salesCancelFind']);
// Route::delete('sales/cancel/{id}',[APISales::class,'salesCancel']);
// Route::put('stores/update/{storeid}',[APIStores::class,'storeUpdate']);

