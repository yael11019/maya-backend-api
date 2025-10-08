<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\SocialPost;
use App\Models\PostLike;
use App\Models\PostComment;
use App\Models\PetFollower;
use App\Models\Pet;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;

class SocialNetworkController extends Controller
{
    /**
     * Public pet profile for social network (no auth required)
     */
    public function publicPetProfile($petId): JsonResponse
    {
        try {
            $userId = Auth::id();
            $pet = Pet::with('user')->findOrFail($petId);
            $followers = $pet->followersCount();
            $posts = SocialPost::where('pet_id', $petId)
                ->where('is_active', true)
                ->with(['comments.pet'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($post) use ($userId) {
                    return [
                        'id' => $post->id,
                        'type' => $post->media_type,
                        'media_url' => asset('storage/' . $post->media_url),
                        'caption' => $post->caption,
                        'likes_count' => $post->likes()->count(),
                        'comments_count' => $post->comments()->count(),
                        'is_liked' => $userId ? $post->isLikedBy($userId) : false,
                        'created_at' => $post->created_at->toISOString(),
                        'comments' => $post->comments->take(3)->map(function ($comment) {
                            return [
                                'id' => $comment->id,
                                'pet_name' => $comment->pet->name,
                                'pet_avatar' => $comment->pet->avatar ? asset('storage/' . $comment->pet->avatar) : null,
                                'text' => $comment->comment,
                                'created_at' => $comment->created_at->toISOString(),
                            ];
                        }),
                    ];
                });
            $profile = [
                'id' => $pet->id,
                'name' => $pet->name,
                'breed' => $pet->breed,
                'age' => $pet->age,
                'weight' => $pet->weight,
                'description' => $pet->description,
                'avatar' => $pet->avatar ? asset('storage/' . $pet->avatar) : null,
                'owner' => [
                    'id' => $pet->user->id,
                    'name' => $pet->user->name,
                ],
                'followers' => $followers,
                'posts' => $posts,
                'created_at' => $pet->created_at->toISOString(),
            ];
            return response()->json([
                'success' => true,
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching public pet profile: ' . $e->getMessage(),
            ], 500);
        }
    }
    // No necesitamos constructor con middleware aquí
    // El middleware se aplicará en las rutas

    /**
     * Get posts for a specific pet (profile view)
     */
    public function getPetPosts($petId): JsonResponse
    {
        try {
            $posts = SocialPost::where('pet_id', $petId)
                ->where('is_active', true)
                ->with(['pet', 'user', 'comments.pet'])
                ->withCount(['likes', 'comments'])
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($post) {
                    return [
                        'id' => $post->id,
                        'type' => $post->media_type,
                        'media_url' => asset('storage/' . $post->media_url),
                        'caption' => $post->caption,
                        'likes_count' => $post->likes_count,
                        'comments_count' => $post->comments_count,
                        'is_liked' => $post->isLikedBy(Auth::id()),
                        'created_at' => $post->created_at->toISOString(),
                        'pet' => [
                            'id' => $post->pet->id,
                            'name' => $post->pet->name,
                            'avatar' => $post->pet->avatar ? asset('storage/' . $post->pet->avatar) : null,
                        ],
                        'comments' => $post->comments->map(function ($comment) {
                            return [
                                'id' => $comment->id,
                                'pet_name' => $comment->pet->name,
                                'pet_avatar' => $comment->pet->avatar ? asset('storage/' . $comment->pet->avatar) : null,
                                'text' => $comment->comment,
                                'created_at' => $comment->created_at->toISOString(),
                                'pet_id' => $comment->pet->id, // Agregado para identificar la mascota que hizo el comentario
                            ];
                        }),
                    ];
                });

            return response()->json([
                'success' => true,
                'posts' => $posts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching posts: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get feed posts (all posts for discovery)
     */
    public function getFeedPosts(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Get all active posts from all pets, prioritizing followed pets
            $posts = SocialPost::where('is_active', true)
                ->with(['pet.user', 'user', 'comments.pet'])
                ->withCount(['likes', 'comments'])
                ->orderByRaw("
                    CASE 
                        WHEN pet_id IN (
                            SELECT followed_pet_id 
                            FROM pet_followers 
                            WHERE follower_user_id = ?
                        ) THEN 0 
                        ELSE 1 
                    END, created_at DESC
                ", [$userId])
                ->limit(100)
                ->get()
                ->map(function ($post) use ($userId) {
                    return [
                        'id' => $post->id,
                        'type' => $post->media_type,
                        'media_url' => asset('storage/' . $post->media_url),
                        'caption' => $post->caption,
                        'likes_count' => $post->likes_count,
                        'comments_count' => $post->comments_count,
                        'is_liked' => $post->isLikedBy($userId),
                        'created_at' => $post->created_at->toISOString(),
                        'pet' => [
                            'id' => $post->pet->id,
                            'name' => $post->pet->name,
                            'avatar' => $post->pet->avatar ? asset('storage/' . $post->pet->avatar) : null,
                            'breed' => $post->pet->breed,
                            'age' => $post->pet->age,
                            'is_following' => $post->pet->isFollowedBy($userId),
                            'is_own_pet' => $post->pet->user_id == $userId,
                            'owner_name' => $post->pet->user->name,
                            'followers_count' => $post->pet->followersCount(),
                        ],
                        'comments' => $post->comments->take(3)->map(function ($comment) {
                            return [
                                'id' => $comment->id,
                                'pet_name' => $comment->pet->name,
                                'pet_avatar' => $comment->pet->avatar ? asset('storage/' . $comment->pet->avatar) : null,
                                'text' => $comment->comment,
                                'created_at' => $comment->created_at->toISOString(),
                            ];
                        }),
                    ];
                });

            return response()->json([
                'success' => true,
                'posts' => $posts,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching feed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Create a new post
     */
    public function createPost(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'pet_id' => 'required|exists:pets,id',
                'caption' => 'nullable|string|max:2000',
                'file' => 'required|file|mimes:jpeg,png,jpg,gif,mp4,mov,avi|max:20480', // 20MB max
            ]);

            // Verify that the pet belongs to the authenticated user
            $pet = Pet::where('id', $request->pet_id)
                     ->where('user_id', Auth::id())
                     ->first();

            if (!$pet) {
                return response()->json([
                    'success' => false,
                    'message' => 'Pet not found or access denied',
                ], 403);
            }

            // Handle file upload
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $safeName = preg_replace('/[^A-Za-z0-9_\-\.]/', '_', $originalName);
            $fileName = 'social_posts/' . $safeName;
            $filePath = $file->storeAs('social_posts', $safeName, 'public');

            // Determine media type
            $mimeType = $file->getMimeType();
            $mediaType = str_starts_with($mimeType, 'image/') ? 'image' : 'video';

            // Create the post
            $post = SocialPost::create([
                'pet_id' => $request->pet_id,
                'user_id' => Auth::id(),
                'caption' => $request->caption,
                'media_url' => $fileName,
                'media_type' => $mediaType,
                'mime_type' => $mimeType,
            ]);

            $post->load(['pet', 'user']);

            return response()->json([
                'success' => true,
                'message' => 'Post created successfully',
                'post' => [
                    'id' => $post->id,
                    'type' => $post->media_type,
                    'media_url' => asset('storage/' . $post->media_url),
                    'caption' => $post->caption,
                    'likes_count' => 0,
                    'comments_count' => 0,
                    'is_liked' => false,
                    'created_at' => $post->created_at->toISOString(),
                    'pet' => [
                        'id' => $post->pet->id,
                        'name' => $post->pet->name,
                        'avatar' => $post->pet->avatar ? asset('storage/' . $post->pet->avatar) : null,
                    ],
                    'comments' => [],
                ],
            ], 201);
        } catch (\Exception $e) {
            Log::error('Error creating post', [
                'error' => $e->getMessage(),
                'user_id' => Auth::id()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error creating post: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Toggle like on a post
     */
    public function toggleLike($postId): JsonResponse
    {
        try {
            $post = SocialPost::findOrFail($postId);
            $userId = Auth::id();

            $existingLike = PostLike::where('post_id', $postId)
                                  ->where('user_id', $userId)
                                  ->first();

            if ($existingLike) {
                // Unlike
                $existingLike->delete();
                $post->decrement('likes_count');
                $isLiked = false;
            } else {
                // Like
                PostLike::create([
                    'post_id' => $postId,
                    'user_id' => $userId,
                ]);
                $post->increment('likes_count');
                $isLiked = true;
                // Notificación de nuevo like
                $pet = Pet::where('user_id', $userId)->first();
                \App\Models\Notification::create([
                    'actor_pet_id' => $pet ? $pet->id : null,
                    'target_pet_id' => $post->pet_id,
                    'post_id' => $postId,
                    'type' => 'like',
                    'user_id' => $userId,
                    'message' => 'Tu post recibió un nuevo like',
                ]);
            }

            return response()->json([
                'success' => true,
                'is_liked' => $isLiked,
                'likes_count' => $post->fresh()->likes_count,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling like: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Add comment to a post
     */
    public function addComment(Request $request, $postId): JsonResponse
    {
        try {
            $request->validate([
                'comment' => 'required|string|max:1000',
                'pet_id' => 'required|exists:pets,id'
            ]);

            $post = SocialPost::findOrFail($postId);
            
            // Verificar que la mascota pertenece al usuario autenticado
            $pet = Pet::where('id', $request->pet_id)
                     ->where('user_id', Auth::id())
                     ->firstOrFail();
            
            $comment = PostComment::create([
                'post_id' => $postId,
                'pet_id' => $request->pet_id,
                'comment' => $request->comment,
            ]);

            $post->increment('comments_count');
            $comment->load('pet');

            // Notificación de nuevo comentario
            \App\Models\Notification::create([
                'actor_pet_id' => $request->pet_id, // quien comenta
                'target_pet_id' => $post->pet_id, // dueño del post
                'post_id' => $postId,
                'type' => 'comment',
                'user_id' => Auth::id(),
                'message' => 'Nuevo comentario en tu post',
            ]);

            return response()->json([
                'success' => true,
                'comment' => [
                    'id' => $comment->id,
                    'pet_name' => $comment->pet->name,
                    'pet_avatar' => $comment->pet->avatar ? asset('storage/' . $comment->pet->avatar) : null,
                    'text' => $comment->comment,
                    'created_at' => $comment->created_at->toISOString(),
                ],
                'comments_count' => $post->fresh()->comments_count,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error adding comment: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Follow/Unfollow a pet
     */
    public function toggleFollow($petId): JsonResponse
    {
        try {
            $pet = Pet::findOrFail($petId);
            $userId = Auth::id();

            // Can't follow your own pet
            if ($pet->user_id == $userId) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot follow your own pet',
                ], 400);
            }

            $existingFollow = PetFollower::where('followed_pet_id', $petId)
                                       ->where('follower_user_id', $userId)
                                       ->first();

            if ($existingFollow) {
                // Unfollow
                $existingFollow->delete();
                $isFollowing = false;
            } else {
                // Follow
                PetFollower::create([
                    'followed_pet_id' => $petId,
                    'follower_user_id' => $userId,
                ]);
                $isFollowing = true;
                // Notificación de nuevo seguidor
                $actorPet = Pet::where('user_id', $userId)->first();
                \App\Models\Notification::create([
                    'actor_pet_id' => $actorPet ? $actorPet->id : null,
                    'target_pet_id' => $petId,
                    'type' => 'follow',
                    'user_id' => $userId,
                    'message' => 'Tu mascota tiene un nuevo seguidor',
                ]);
            }

            $followersCount = $pet->followersCount();

            return response()->json([
                'success' => true,
                'is_following' => $isFollowing,
                'followers_count' => $followersCount,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error toggling follow: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Search pets and users
     */
    public function search(Request $request): JsonResponse
    {
        try {
            $query = $request->get('q', '');
            $type = $request->get('type', 'all'); // all, pets, users

            if (strlen($query) < 2) {
                return response()->json([
                    'success' => true,
                    'results' => [],
                ]);
            }

            $results = collect();
            $userId = Auth::id();

            // Search pets
            if ($type === 'all' || $type === 'pets') {
                $pets = Pet::where('name', 'LIKE', "%{$query}%")
                          ->orWhere('breed', 'LIKE', "%{$query}%")
                          ->with('user')
                          ->limit(20)
                          ->get()
                          ->map(function ($pet) use ($userId) {
                              return [
                                  'id' => $pet->id,
                                  'name' => $pet->name,
                                  'breed' => $pet->breed,
                                  'avatar' => $pet->avatar ? asset('storage/' . $pet->avatar) : null,
                                  'type' => 'pet',
                                  'followers_count' => $pet->followersCount(),
                                  'is_following' => $pet->isFollowedBy($userId),
                              ];
                          });
                $results = $results->merge($pets);
            }

            // Search users
            if ($type === 'all' || $type === 'users') {
                $users = User::where('name', 'LIKE', "%{$query}%")
                           ->orWhere('email', 'LIKE', "%{$query}%")
                           ->where('id', '!=', $userId)
                           ->limit(20)
                           ->get()
                           ->map(function ($user) {
                               return [
                                   'id' => $user->id,
                                   'name' => $user->name,
                                   'username' => $user->email, // Using email as username
                                   'avatar' => null, // Add avatar field to users table if needed
                                   'type' => 'user',
                                   'followers_count' => 0, // Implement user followers if needed
                                   'is_following' => false
                               ];
                           });
                $results = $results->merge($users);
            }

            return response()->json([
                'success' => true,
                'results' => $results->values(),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error searching: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get suggestions for pets/users to follow
     */
    public function getSuggestions(): JsonResponse
    {
        try {
            $userId = Auth::id();
            
            // Get pets that user is not following yet
            $followedPetIds = PetFollower::where('follower_user_id', $userId)
                                       ->pluck('followed_pet_id');

            $suggestedPets = Pet::whereNotIn('id', $followedPetIds)
                               ->where('user_id', '!=', $userId)
                               ->with('user')
                               ->inRandomOrder()
                               ->limit(6)
                               ->get()
                               ->map(function ($pet) use ($userId) {
                                   return [
                                       'id' => $pet->id,
                                       'name' => $pet->name,
                                       'breed' => $pet->breed,
                                       'avatar' => $pet->avatar ? asset('storage/' . $pet->avatar) : null,
                                       'type' => 'pet',
                                       'followers_count' => $pet->followersCount(),
                                       'is_following' => $pet->isFollowedBy($userId),
                                   ];
                               });
            return response()->json([
                'success' => true,
                'suggested_pets' => $suggestedPets,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching suggestions: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get pet social stats
     */
    public function getPetStats($petId): JsonResponse
    {
        try {
            $pet = Pet::findOrFail($petId);
            $userId = Auth::id();

            $stats = [
                'followers' => $pet->followersCount(),
                'following' => PetFollower::where('follower_user_id', $pet->user_id)->count(),
                'posts' => $pet->socialPosts()->count(),
                'is_following' => $pet->isFollowedBy($userId),
            ];

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching stats: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get detailed pet profile for social network
     */
    public function getPetProfile($petId): JsonResponse
    {
        try {
            $pet = Pet::with('user')->findOrFail($petId);
            $userId = Auth::id();

            $profile = [
                'id' => $pet->id,
                'name' => $pet->name,
                'breed' => $pet->breed,
                'age' => $pet->age,
                'weight' => $pet->weight,
                'description' => $pet->description,
                'avatar' => $pet->avatar ? asset('storage/' . $pet->avatar) : null,
                'owner' => [
                    'id' => $pet->user->id,
                    'name' => $pet->user->name,
                    'email' => $pet->user->email,
                ],
                'stats' => [
                    'followers' => $pet->followersCount(),
                    'following' => PetFollower::where('follower_user_id', $pet->user_id)->count(),
                    'posts' => $pet->socialPosts()->where('is_active', true)->count(),
                ],
                'is_following' => $pet->isFollowedBy($userId),
                'is_own_pet' => $pet->user_id == $userId,
                'created_at' => $pet->created_at->toISOString(),
            ];

            return response()->json([
                'success' => true,
                'profile' => $profile,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching pet profile: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a post's caption
     */
    public function updatePost(Request $request, SocialPost $post): JsonResponse
    {
        try {
            $request->validate([
                'caption' => 'nullable|string|max:2000',
            ]);

            // Verify that the post belongs to the authenticated user
            if ($post->user_id !== Auth::id()) {
                return response()->json([
                    'success' => false,
                    'message' => 'You can only edit your own posts',
                ], 403);
            }

            $post->update([
                'caption' => $request->caption,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Post updated successfully',
                'post' => [
                    'id' => $post->id,
                    'caption' => $post->caption,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating post: ' . $e->getMessage(),
            ], 500);
        }
    }
}
