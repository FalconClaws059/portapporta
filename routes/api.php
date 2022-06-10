<?php

use App\Http\Controllers\LoginController;
use App\Http\Controllers\RegisterController;
use App\Http\Controllers\TicketController;
use App\Http\Controllers\VerificationController;

use App\Http\Resources\CentriRaccoltaResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\RifiutarioResource;
use App\Http\Resources\TrashTypeResource;
use App\Http\Resources\UtenzeMetaResource;
use App\Http\Resources\ZoneConfiniResource;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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


Route::post('/register', [RegisterController::class, 'register']);
Route::post('/login', [LoginController::class, 'login']);
Route::post('/logout', [LoginController::class, 'logout']);

// NO MIDDLEWARES
Route::prefix('c')->name('company.')->group(function () {
    Route::get('/{id}/wastes.json', function ($id) {
        return new RifiutarioResource(Company::findOrFail($id));
    })->name('wastes.json');
    Route::get('/{id}/user_types.json', function ($id) {
        return new UtenzeMetaResource(Company::findOrFail($id));
    })->name('user_types.json');
    Route::get('/{id}/trash_types.json', function ($id) {
        return new TrashTypeResource(Company::findOrFail($id));
    })->name('trash_types.json');
    Route::get('/{id}/zones.geojson', function ($id) {
        return new ZoneConfiniResource(Company::findOrFail($id));
    })->name('zones.geojson');
});


// AUTH
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// AUTH AND SIGNED
Route::prefix('c')->name('company.')->middleware('auth:sanctum', 'signed')->group(function () {
    Route::get('/{id}/config.json', function ($id) {
        return new CompanyResource(Company::findOrFail($id));
    })->name('config.json');
    Route::get('/{id}/waste_collection_centers.geojson', function ($id) {
        return new CentriRaccoltaResource(Company::findOrFail($id));
    })->name('waste_collection_centers.geojson');
    Route::post('/{id}/ticket', [TicketController::class, 'store'])->name('ticket');
    Route::get('email/resend', [VerificationController::class, 'resend'])->name('verification.resend');
});

Route::get('email/verify/{id}', [VerificationController::class, 'verify'])->middleware('signed')->name('verification.verify');
