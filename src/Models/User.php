<?php

namespace CNA\Models;

class User extends BaseModel
{
    protected string $table = 'users';
    protected array $fillable = [
        'username', 'email', 'password_hash', 'first_name', 'last_name', 
        'role', 'is_active', 'email_verified_at'
    ];
    protected array $hidden = ['password_hash'];

    public function findByEmail(string $email): ?array
    {
        return $this->findBy('email', $email);
    }

    public function findByUsername(string $username): ?array
    {
        return $this->findBy('username', $username);
    }

    public function getActiveUsers(): array
    {
        $sql = "SELECT * FROM {$this->table} WHERE is_active = true ORDER BY first_name, last_name";
        $results = $this->db->fetchAll($sql);
        
        return array_map([$this, 'hideFields'], $results);
    }

    public function updateLastLogin(string $id): bool
    {
        $sql = "UPDATE {$this->table} SET last_login = CURRENT_TIMESTAMP WHERE {$this->primaryKey} = :id";
        $stmt = $this->db->query($sql, ['id' => $id]);
        
        return $stmt->rowCount() > 0;
    }

    public function isEmailTaken(string $email, string $excludeId = null): bool
    {
        return $this->exists('email', $email, $excludeId);
    }

    public function isUsernameTaken(string $username, string $excludeId = null): bool
    {
        return $this->exists('username', $username, $excludeId);
    }

    public function getFullName(array $user): string
    {
        return trim($user['first_name'] . ' ' . $user['last_name']);
    }

    public function hasRole(array $user, string $role): bool
    {
        return $user['role'] === $role;
    }

    public function isAdmin(array $user): bool
    {
        return $this->hasRole($user, 'admin');
    }

    public function deactivate(string $id): bool
    {
        return $this->update($id, ['is_active' => false]);
    }

    public function activate(string $id): bool
    {
        return $this->update($id, ['is_active' => true]);
    }
}