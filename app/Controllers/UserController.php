<?php

namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;
use Apileon\Validation\ValidationException;
use App\Models\User;

class UserController
{
    public function index(Request $request): Response
    {
        try {
            $page = max(1, (int) $request->query('page', 1));
            $perPage = min(100, max(1, (int) $request->query('per_page', 10)));
            $search = $request->query('search', '');
            $status = $request->query('status', '');
            $verified = $request->query('verified', '');
            
            $query = User::newQuery();
            
            // Apply search filter
            if (!empty($search)) {
                $query->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
            }
            
            // Apply status filter
            if (!empty($status)) {
                $query->where('status', $status);
            }
            
            // Apply verification filter
            if ($verified === 'true') {
                $query->where('email_verified_at', '!=', null);
            } elseif ($verified === 'false') {
                $query->where('email_verified_at', '=', null);
            }
            
            // Get total count for pagination
            $total = $query->count();
            
            // Apply pagination
            $offset = ($page - 1) * $perPage;
            $users = $query->orderBy('created_at', 'DESC')
                          ->limit($perPage)
                          ->offset($offset)
                          ->get();
            
            // Convert to User instances
            $userInstances = array_map(function($userData) {
                return User::newInstance($userData, true);
            }, $users);
            
            return Response::json([
                'success' => true,
                'data' => array_map(fn($user) => $user->toArray(), $userInstances),
                'meta' => [
                    'total' => $total,
                    'page' => $page,
                    'per_page' => $perPage,
                    'last_page' => ceil($total / $perPage),
                    'from' => $offset + 1,
                    'to' => min($offset + $perPage, $total)
                ]
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to retrieve users',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function show(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            
            if ($id <= 0) {
                return Response::json([
                    'success' => false,
                    'error' => 'Invalid user ID',
                    'message' => 'User ID must be a positive integer'
                ], 400);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The requested user does not exist'
                ], 404);
            }

            return Response::json([
                'success' => true,
                'data' => $user->toArray()
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to retrieve user',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function store(Request $request): Response
    {
        try {
            $data = $request->all();
            
            // Create user with validation
            $user = User::createUser($data);

            return Response::json([
                'success' => true,
                'message' => 'User created successfully',
                'data' => $user->toArray()
            ], 201);
            
        } catch (ValidationException $e) {
            return Response::json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'The given data was invalid',
                'errors' => $e->getErrors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to create user',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function update(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            
            if ($id <= 0) {
                return Response::json([
                    'success' => false,
                    'error' => 'Invalid user ID',
                    'message' => 'User ID must be a positive integer'
                ], 400);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The requested user does not exist'
                ], 404);
            }

            $data = $request->all();
            
            // Update user with validation
            $user->updateUser($data);

            return Response::json([
                'success' => true,
                'message' => 'User updated successfully',
                'data' => $user->fresh()->toArray()
            ]);
            
        } catch (ValidationException $e) {
            return Response::json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'The given data was invalid',
                'errors' => $e->getErrors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to update user',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function destroy(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            
            if ($id <= 0) {
                return Response::json([
                    'success' => false,
                    'error' => 'Invalid user ID',
                    'message' => 'User ID must be a positive integer'
                ], 400);
            }
            
            $user = User::find($id);
            
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The requested user does not exist'
                ], 404);
            }

            $user->delete();

            return Response::json([
                'success' => true,
                'message' => 'User deleted successfully'
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to delete user',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function profile(Request $request): Response
    {
        try {
            // In a real application, this would get the authenticated user
            // For demo purposes, we'll use a hardcoded user ID
            $userId = $request->header('X-User-ID', 1);
            $user = User::find($userId);
            
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => 'Profile not found',
                    'message' => 'Your profile could not be found'
                ], 404);
            }

            $profile = $user->toArray();
            $profile['last_login'] = date('Y-m-d H:i:s'); // Mock data
            $profile['login_count'] = rand(1, 100); // Mock data

            return Response::json([
                'success' => true,
                'data' => $profile
            ]);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to retrieve profile',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function updateProfile(Request $request): Response
    {
        try {
            // In a real application, this would get the authenticated user
            $userId = $request->header('X-User-ID', 1);
            $user = User::find($userId);
            
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => 'Profile not found',
                    'message' => 'Your profile could not be found'
                ], 404);
            }

            $data = $request->all();
            
            // Remove sensitive fields that shouldn't be updated via profile
            unset($data['password'], $data['email_verified_at'], $data['status']);
            
            $user->updateUser($data);

            return Response::json([
                'success' => true,
                'message' => 'Profile updated successfully',
                'data' => $user->fresh()->toArray()
            ]);
            
        } catch (ValidationException $e) {
            return Response::json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => 'The given data was invalid',
                'errors' => $e->getErrors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to update profile',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function changePassword(Request $request): Response
    {
        try {
            // In a real application, this would get the authenticated user
            $userId = $request->header('X-User-ID', 1);
            $user = User::find($userId);
            
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'Your account could not be found'
                ], 404);
            }

            $data = $request->all();
            
            if (empty($data['current_password']) || empty($data['new_password'])) {
                return Response::json([
                    'success' => false,
                    'error' => 'Validation failed',
                    'message' => 'Both current and new passwords are required',
                    'errors' => [
                        'current_password' => empty($data['current_password']) ? ['Current password is required'] : [],
                        'new_password' => empty($data['new_password']) ? ['New password is required'] : []
                    ]
                ], 422);
            }

            $user->changePassword($data['current_password'], $data['new_password']);

            return Response::json([
                'success' => true,
                'message' => 'Password changed successfully'
            ]);
            
        } catch (ValidationException $e) {
            return Response::json([
                'success' => false,
                'error' => 'Validation failed',
                'message' => $e->getMessage(),
                'errors' => $e->getErrors()
            ], 422);
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to change password',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function verifyEmail(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            $user = User::find($id);
            
            if (!$user) {
                return Response::json([
                    'success' => false,
                    'error' => 'User not found',
                    'message' => 'The requested user does not exist'
                ], 404);
            }

            if ($user->isEmailVerified()) {
                return Response::json([
                    'success' => false,
                    'error' => 'Already verified',
                    'message' => 'Email is already verified'
                ], 400);
            }

            $user->markEmailAsVerified();

            return Response::json([
                'success' => true,
                'message' => 'Email verified successfully',
                'data' => $user->fresh()->toArray()
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to verify email',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function bulkDelete(Request $request): Response
    {
        try {
            $data = $request->all();
            
            if (empty($data['ids']) || !is_array($data['ids'])) {
                return Response::json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'message' => 'An array of user IDs is required'
                ], 400);
            }

            $ids = array_filter($data['ids'], 'is_numeric');
            
            if (empty($ids)) {
                return Response::json([
                    'success' => false,
                    'error' => 'Invalid data',
                    'message' => 'Valid user IDs are required'
                ], 400);
            }

            $deletedCount = User::destroy($ids);

            return Response::json([
                'success' => true,
                'message' => "Successfully deleted {$deletedCount} users",
                'data' => [
                    'deleted_count' => $deletedCount,
                    'requested_count' => count($ids)
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Failed to delete users',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }

    public function search(Request $request): Response
    {
        try {
            $query = trim($request->query('q', ''));
            $limit = min(50, max(1, (int) $request->query('limit', 10)));
            
            if (empty($query)) {
                return Response::json([
                    'success' => false,
                    'error' => 'Invalid query',
                    'message' => 'Search query is required'
                ], 400);
            }

            $users = User::newQuery()
                ->where('name', 'LIKE', "%{$query}%")
                ->orWhere('email', 'LIKE', "%{$query}%")
                ->limit($limit)
                ->get();

            $userInstances = array_map(function($userData) {
                return User::newInstance($userData, true);
            }, $users);

            return Response::json([
                'success' => true,
                'data' => array_map(fn($user) => $user->toArray(), $userInstances),
                'meta' => [
                    'query' => $query,
                    'count' => count($users),
                    'limit' => $limit
                ]
            ]);
            
        } catch (\Exception $e) {
            return Response::json([
                'success' => false,
                'error' => 'Search failed',
                'message' => app_debug() ? $e->getMessage() : 'Internal server error'
            ], 500);
        }
    }
}
