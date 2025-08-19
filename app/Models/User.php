<?php

namespace App\Models;

class User extends Model
{
    protected string $table = 'users';
    
    protected array $fillable = [
        'name',
        'email',
        'password',
        'created_at',
        'updated_at'
    ];

    public static function all(): array
    {
        // In a real application, this would query the database
        return [
            new self(['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com']),
            new self(['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com']),
            new self(['id' => 3, 'name' => 'Bob Johnson', 'email' => 'bob@example.com']),
        ];
    }

    public static function find(int $id): ?self
    {
        $users = self::all();
        
        foreach ($users as $user) {
            if ($user->id == $id) {
                return $user;
            }
        }
        
        return null;
    }

    public function save(): bool
    {
        // In a real application, this would save to the database
        $this->updated_at = date('Y-m-d H:i:s');
        
        if (!$this->id) {
            $this->id = rand(1000, 9999);
            $this->created_at = date('Y-m-d H:i:s');
        }
        
        return true;
    }

    public function delete(): bool
    {
        // In a real application, this would delete from the database
        return true;
    }
}
