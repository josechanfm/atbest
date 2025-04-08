<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Config;

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

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    dd($request->all());
    //return $request->user();
});
Route::get('search/members',[App\Http\Controllers\Api\DataController::class, 'searchMembers'])->name('api.search.members');

Route::get('config/item', function(Request $request){
    return response()->json(Config::item($request->key));

})->name('api.config.item');        


