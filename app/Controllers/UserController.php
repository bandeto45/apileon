<?php

namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;
use App\Models\User;

class UserController
{
    public function index(Request $request): Response
    {
        $users = User::all();
        
        return Response::json([
            'data' => array_map(fn($user) => $user->toArray(), $users),
            'meta' => [
                'total' => count($users),
                'page' => (int) $request->query('page', 1),
                'per_page' => (int) $request->query('per_page', 10)
            ]
        ]);
    }

    public function show(Request $request): Response
    {
        $id = (int) $request->param('id');
        $user = User::find($id);
        
        if (!$user) {
            return abort(404, 'User not found');
        }

        return Response::json(['data' => $user->toArray()]);
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        
        // Validate required fields
        if (empty($data['name']) || empty($data['email'])) {
            return Response::json([
                'error' => 'Validation failed',
                'message' => 'Name and email are required',
                'errors' => [
                    'name' => empty($data['name']) ? ['Name is required'] : [],
                    'email' => empty($data['email']) ? ['Email is required'] : []
                ]
            ], 422);
        }

        // Validate email format
        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return Response::json([
                'error' => 'Validation failed',
                'message' => 'Invalid email format',
                'errors' => [
                    'email' => ['Email must be a valid email address']
                ]
            ], 422);
        }

        $user = new User($data);
        $user->save();

        return Response::json([
            'message' => 'User created successfully',
            'data' => $user->toArray()
        ], 201);
    }

    public function update(Request $request): Response
    {
        $id = (int) $request->param('id');
        $user = User::find($id);
        
        if (!$user) {
            return abort(404, 'User not found');
        }

        $data = $request->all();
        
        // Validate email if provided
        if (isset($data['email']) && !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            return Response::json([
                'error' => 'Validation failed',
                'message' => 'Invalid email format',
                'errors' => [
                    'email' => ['Email must be a valid email address']
                ]
            ], 422);
        }

        $user->fill($data);
        $user->save();

        return Response::json([
            'message' => 'User updated successfully',
            'data' => $user->toArray()
        ]);
    }

    public function destroy(Request $request): Response
    {
        $id = (int) $request->param('id');
        $user = User::find($id);
        
        if (!$user) {
            return abort(404, 'User not found');
        }

        $user->delete();

        return Response::json([
            'message' => 'User deleted successfully'
        ]);
    }

    public function profile(Request $request): Response
    {
        // In a real application, this would get the authenticated user
        $user = User::find(1);
        
        if (!$user) {
            return abort(404, 'Profile not found');
        }

        return Response::json([
            'data' => array_merge($user->toArray(), [
                'profile' => [
                    'bio' => 'This is my bio',
                    'avatar' => 'https://example.com/avatar.jpg',
                    'last_login' => now()
                ]
            ])
        ]);
    }

    public function updateProfile(Request $request): Response
    {
        $data = $request->all();
        
        return Response::json([
            'message' => 'Profile updated successfully',
            'data' => $data,
            'updated_at' => now()
        ]);
    }
}
