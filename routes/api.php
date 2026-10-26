<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\{AuthController, DestinationController, DeviceTokenController, EmergencyContactController, EventController, FavoritePhraseController, ItineraryController, LookupController, PhraseController, PlaceController, SavedPlaceController, SyncController, TransportRouteController, TripController, VisitedPlaceController};



Route::prefix('v1')->middleware('throttle:60,1')->group(function () {

    // ---- Public (guests can use these; a token is optional) ----
    Route::get('lookups', [LookupController::class, 'index']);

    Route::get('destinations', [DestinationController::class, 'index']);
    Route::get('destinations/{slug}', [DestinationController::class, 'show']);
    Route::get('destinations/{slug}/pack', [SyncController::class, 'pack']);

    Route::get('places', [PlaceController::class, 'index']);
    Route::get('places/{slug}', [PlaceController::class, 'show']);

    Route::get('phrases', [PhraseController::class, 'index']);

     Route::get('itineraries', [ItineraryController::class, 'index']);
    Route::get('itineraries/{slug}', [ItineraryController::class, 'show']);
    Route::get('transport-routes', [TransportRouteController::class, 'index']);
    Route::get('events', [EventController::class, 'index']);
    
    Route::get('emergency-contacts', [EmergencyContactController::class, 'index']);
    Route::get('sync', [SyncController::class, 'index']);



    // ---- Auth (stricter limit against password guessing) ----
    Route::middleware('throttle:10,1,auth')->group(function () {
        Route::post('auth/register', [AuthController::class, 'register']);
        Route::post('auth/login', [AuthController::class, 'login']);
        Route::post('auth/social', [AuthController::class, 'social']);
    });

    // ---- Logged-in users ----
    Route::middleware('auth:sanctum')->group(function () {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::get('me', [AuthController::class, 'me']);
        Route::patch('me', [AuthController::class, 'update']);
        Route::delete('me', [AuthController::class, 'destroy']);

        Route::get('me/saved-places', [SavedPlaceController::class, 'index']);
        Route::put('me/saved-places/{slug}', [SavedPlaceController::class, 'store']);
        Route::delete('me/saved-places/{slug}', [SavedPlaceController::class, 'destroy']);

        Route::get('me/trips', [TripController::class, 'index']);
        Route::get('me/trips/{uuid}', [TripController::class, 'show'])->whereUuid('uuid');
        Route::put('me/trips/{uuid}', [TripController::class, 'upsert'])->whereUuid('uuid');
        Route::delete('me/trips/{uuid}', [TripController::class, 'destroy'])->whereUuid('uuid');

        Route::get('me/favorite-phrases', [FavoritePhraseController::class, 'index']);
        Route::put('me/favorite-phrases/{phrase}', [FavoritePhraseController::class, 'store'])->whereNumber('phrase');
        Route::delete('me/favorite-phrases/{phrase}', [FavoritePhraseController::class, 'destroy'])->whereNumber('phrase');

        Route::get('me/visited-places', [VisitedPlaceController::class, 'index']);
        Route::put('me/visited-places/{slug}', [VisitedPlaceController::class, 'store']);
        Route::delete('me/visited-places/{slug}', [VisitedPlaceController::class, 'destroy']);
    });

    // ---- Push tokens (guests allowed; a Bearer token links the device to the user) ----
    Route::middleware('throttle:20,1,device')->group(function () {
        Route::put('device-tokens', [DeviceTokenController::class, 'store']);
        Route::delete('device-tokens', [DeviceTokenController::class, 'destroy']);
    });
});
