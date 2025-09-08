<?php
 
 use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\SocialAuthController;
use App\Http\Controllers\Role\RoleController;
use App\Http\Controllers\Staff\StaffController;
use App\Http\Controllers\PetsController;
use App\Http\Controllers\VaccinationController;
use App\Http\Controllers\FavoriteVeterinarianController;
use App\Http\Controllers\AppointmentController;
use App\Http\Controllers\VeterinarianController;
use App\Http\Controllers\SocialNetworkController; 

// Health check endpoint for Render
Route::get('/health', function () {
    return response()->json(['status' => 'OK', 'timestamp' => now()]);
});

 Route::group(['prefix' => 'auth'], function () {
     Route::post('/register', [AuthController::class, 'register'])->name('register');
     Route::post('/login', [AuthController::class, 'login'])->name('login');
 
     // Facebook OAuth routes
     Route::get('/facebook', [SocialAuthController::class, 'redirectToFacebook']);
     Route::get('/facebook/callback', [SocialAuthController::class, 'handleFacebookCallback']);
     Route::post('/facebook/login', [SocialAuthController::class, 'facebookLogin']);
 
     Route::group(['middleware' => ['auth:api']], function () {
         Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
         Route::post('/refresh', [AuthController::class, 'refresh'])->name('refresh');
         Route::post('/me', [AuthController::class, 'me'])->name('me');
     });
 });
Route::group([
    'middleware' => ['auth:api'],
], function($router){
    Route::resource("role", RoleController::class);
    Route::resource("staffs", StaffController::class);
    Route::resource("pets", PetsController::class);
    Route::resource("vaccinations", VaccinationController::class);
    Route::get('pets/{pet}/vaccinations', [VaccinationController::class, 'getByPet']);
    Route::get('favorite-veterinarians', [FavoriteVeterinarianController::class, 'index']);
    Route::post('favorite-veterinarians', [FavoriteVeterinarianController::class, 'store']);
    Route::delete('favorite-veterinarians/{placeId}', [FavoriteVeterinarianController::class, 'destroy']);
    
    // Appointments routes
    Route::resource('appointments', AppointmentController::class);
    Route::patch('appointments/{id}/complete', [AppointmentController::class, 'markAsCompleted']);
    Route::patch('appointments/{id}/cancel', [AppointmentController::class, 'markAsCancelled']);
    
    // Veterinarians routes
    Route::resource('veterinarians', VeterinarianController::class);
    
    // Social Network routes
    Route::prefix('social')->group(function () {
        // Posts
        Route::get('pets/{pet}/posts', [SocialNetworkController::class, 'getPetPosts']);
        Route::get('feed', [SocialNetworkController::class, 'getFeedPosts']);
        Route::post('posts', [SocialNetworkController::class, 'createPost']);
        Route::put('posts/{post}', [SocialNetworkController::class, 'updatePost']);
        Route::delete('posts/{post}', [SocialNetworkController::class, 'deletePost']);
        
        // Likes
        Route::post('posts/{post}/like', [SocialNetworkController::class, 'toggleLike']);
        
        // Comments
        Route::post('posts/{post}/comments', [SocialNetworkController::class, 'addComment']);
        
        // Following
        Route::post('pets/{pet}/follow', [SocialNetworkController::class, 'toggleFollow']);
        
        // Search and suggestions
        Route::get('search', [SocialNetworkController::class, 'search']);
        Route::get('pets/{pet}/suggestions', [SocialNetworkController::class, 'getSuggestions']);
        Route::get('pets/{pet}/stats', [SocialNetworkController::class, 'getPetStats']);
        Route::get('pets/{pet}/profile', [SocialNetworkController::class, 'getPetProfile']);
    });
});
 