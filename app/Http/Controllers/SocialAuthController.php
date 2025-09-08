<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Hash;

class SocialAuthController extends Controller
{
    /**
     * Redirect to Facebook OAuth
     */
    public function redirectToFacebook()
    {
        return Socialite::driver('facebook')->redirect();
    }

    /**
     * Handle Facebook OAuth callback
     */
    public function handleFacebookCallback()
    {
        try {
            $facebookUser = Socialite::driver('facebook')->user();
            
            // Check if user exists with this email
            $user = User::where('email', $facebookUser->getEmail())->first();
            
            if ($user) {
                // Update user's Facebook info if needed
                $user->update([
                    'facebook_id' => $facebookUser->getId(),
                    'avatar' => $facebookUser->getAvatar(),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $facebookUser->getName(),
                    'email' => $facebookUser->getEmail(),
                    'facebook_id' => $facebookUser->getId(),
                    'avatar' => $facebookUser->getAvatar(),
                    'email_verified_at' => now(),
                    'password' => Hash::make(Str::random(16)), // Random password since they'll use Facebook
                ]);
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Successfully authenticated with Facebook',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Facebook',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Handle Facebook login from frontend (using access token)
     */
    public function facebookLogin(Request $request)
    {
        $request->validate([
            'access_token' => 'required|string',
        ]);

        try {
            // Check if this is a mock request for development
            if ($request->has('mock_data')) {
                // Development mode - use mock data
                $mockData = $request->mock_data;
                
                // First try to find user by facebook_id
                $user = User::where('facebook_id', $mockData['id'])->first();
                
                if ($user) {
                    // User exists with this Facebook ID, update their info
                    $user->update([
                        'name' => $mockData['name'],
                        'avatar' => $mockData['picture']['data']['url'] ?? null,
                    ]);
                } else {
                    // Check if user exists with this email but no facebook_id
                    $existingUser = User::where('email', $mockData['email'])->first();
                    
                    if ($existingUser) {
                        // Link existing account to Facebook
                        $existingUser->update([
                            'facebook_id' => $mockData['id'],
                            'avatar' => $mockData['picture']['data']['url'] ?? null,
                        ]);
                        $user = $existingUser;
                    } else {
                        // Create new user with mock data
                        $user = User::create([
                            'name' => $mockData['name'],
                            'email' => $mockData['email'],
                            'facebook_id' => $mockData['id'],
                            'avatar' => $mockData['picture']['data']['url'] ?? null,
                            'email_verified_at' => now(),
                            'password' => Hash::make(Str::random(16)),
                        ]);
                    }
                }
            } else {
                // Production mode - use real Facebook API
                $facebookUser = Socialite::driver('facebook')->userFromToken($request->access_token);
                
                // First try to find user by facebook_id
                $user = User::where('facebook_id', $facebookUser->getId())->first();
                
                if ($user) {
                    // User exists with this Facebook ID, update their info
                    $user->update([
                        'name' => $facebookUser->getName(),
                        'avatar' => $facebookUser->getAvatar(),
                    ]);
                } else {
                    // Check if user exists with this email but no facebook_id
                    $existingUser = User::where('email', $facebookUser->getEmail())->first();
                    
                    if ($existingUser) {
                        // Link existing account to Facebook
                        $existingUser->update([
                            'facebook_id' => $facebookUser->getId(),
                            'avatar' => $facebookUser->getAvatar(),
                        ]);
                        $user = $existingUser;
                    } else {
                        // Create new user
                        $user = User::create([
                            'name' => $facebookUser->getName(),
                            'email' => $facebookUser->getEmail(),
                            'facebook_id' => $facebookUser->getId(),
                            'avatar' => $facebookUser->getAvatar(),
                            'email_verified_at' => now(),
                            'password' => Hash::make(Str::random(16)),
                        ]);
                    }
                }
            }

            // Generate JWT token
            $token = JWTAuth::fromUser($user);

            return response()->json([
                'success' => true,
                'message' => 'Successfully authenticated with Facebook',
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
                'user' => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                    'avatar' => $user->avatar,
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to authenticate with Facebook',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
