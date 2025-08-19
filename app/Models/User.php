<?php

namespace App\Models;

use Apileon\Validation\Validator;
use Apileon\Validation\ValidationException;

class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name',
        'email',
        'password',
        'email_verified_at',
        'status'
    ];

    protected array $hidden = [
        'password',
        'remember_token'
    ];

    protected array $casts = [
        'email_verified_at' => 'datetime',
        'status' => 'string'
    ];

    protected array $guarded = [
        'id',
        'created_at',
        'updated_at'
    ];

    // Automatically hash password when setting
    public function setAttribute(string $key, $value): void
    {
        if ($key === 'password' && !empty($value)) {
            $value = $this->hashPassword($value);
        }
        
        parent::setAttribute($key, $value);
    }

    protected function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_ARGON2ID, [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ]);
    }

    public function verifyPassword(string $password): bool
    {
        return password_verify($password, $this->getAttribute('password'));
    }

    public function isActive(): bool
    {
        return $this->getAttribute('status') === 'active';
    }

    public function isEmailVerified(): bool
    {
        return $this->getAttribute('email_verified_at') !== null;
    }

    public function markEmailAsVerified(): bool
    {
        $this->setAttribute('email_verified_at', date('Y-m-d H:i:s'));
        return $this->save();
    }

    public static function validateForCreation(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|min:2|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:255',
        ], [
            'name.required' => 'Name is required',
            'name.min' => 'Name must be at least 2 characters',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already registered',
            'password.required' => 'Password is required',
            'password.min' => 'Password must be at least 8 characters long',
            'password.max' => 'Password cannot exceed 255 characters',
        ]);

        return $validator->validate();
    }

    public static function validateForUpdate(array $data, int $userId = null): array
    {
        $rules = [
            'name' => 'string|min:2|max:255',
            'email' => 'email|max:255|unique:users,email,' . ($userId ?: 'NULL'),
        ];

        // Only validate password if it's being updated
        if (isset($data['password']) && !empty($data['password'])) {
            $rules['password'] = 'string|min:8|max:255';
        }

        $validator = Validator::make($data, $rules, [
            'name.min' => 'Name must be at least 2 characters',
            'name.max' => 'Name cannot exceed 255 characters',
            'email.email' => 'Please provide a valid email address',
            'email.unique' => 'This email address is already taken',
            'password.min' => 'Password must be at least 8 characters long',
            'password.max' => 'Password cannot exceed 255 characters',
        ]);

        return $validator->validate();
    }

    public static function findByEmail(string $email): ?self
    {
        $result = static::where('email', $email)->first();
        return $result ? static::newInstance($result, true) : null;
    }

    public static function createUser(array $data): self
    {
        $validatedData = static::validateForCreation($data);
        
        // Set default status
        $validatedData['status'] = 'active';
        
        return static::create($validatedData);
    }

    public function updateUser(array $data): bool
    {
        $validatedData = static::validateForUpdate($data, $this->getAttribute('id'));
        
        // Remove password if empty (don't update it)
        if (isset($validatedData['password']) && empty($validatedData['password'])) {
            unset($validatedData['password']);
        }
        
        $this->fill($validatedData);
        return $this->save();
    }

    public function changePassword(string $currentPassword, string $newPassword): bool
    {
        if (!$this->verifyPassword($currentPassword)) {
            throw new ValidationException('Current password is incorrect', [
                'current_password' => ['Current password is incorrect']
            ]);
        }

        $validator = Validator::make(['password' => $newPassword], [
            'password' => 'required|string|min:8|max:255'
        ]);

        $validator->validate();

        $this->setAttribute('password', $newPassword);
        return $this->save();
    }

    public function generateRememberToken(): string
    {
        $token = bin2hex(random_bytes(32));
        $this->setAttribute('remember_token', hash('sha256', $token));
        $this->save();
        
        return $token;
    }

    public function getFullNameAttribute(): string
    {
        return $this->getAttribute('name');
    }

    public function getDisplayEmailAttribute(): string
    {
        $email = $this->getAttribute('email');
        return $this->isEmailVerified() ? $email : $email . ' (unverified)';
    }

    // Scope methods for common queries
    public static function active(): \Apileon\Database\QueryBuilder
    {
        return static::where('status', 'active');
    }

    public static function verified(): \Apileon\Database\QueryBuilder
    {
        return static::where('email_verified_at', '!=', null);
    }

    public static function recent(int $days = 30): \Apileon\Database\QueryBuilder
    {
        $date = date('Y-m-d H:i:s', strtotime("-{$days} days"));
        return static::where('created_at', '>=', $date);
    }

    // Override toArray to include computed attributes
    public function toArray(): array
    {
        $attributes = parent::toArray();
        
        // Add computed attributes
        $attributes['full_name'] = $this->getFullNameAttribute();
        $attributes['display_email'] = $this->getDisplayEmailAttribute();
        $attributes['is_verified'] = $this->isEmailVerified();
        $attributes['is_active'] = $this->isActive();
        
        return $attributes;
    }

    // Security: prevent mass assignment of sensitive fields
    protected function isFillable(string $key): bool
    {
        // Extra security check for sensitive fields
        $sensitiveFields = ['id', 'remember_token', 'created_at', 'updated_at'];
        
        if (in_array($key, $sensitiveFields)) {
            return false;
        }
        
        return parent::isFillable($key);
    }
}
