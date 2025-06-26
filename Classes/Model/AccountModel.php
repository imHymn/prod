<?php

namespace Model;

class AccountModel
{
    private \DatabaseClass $db;

    public function __construct(\DatabaseClass $db)

    {
        $this->db = $db;
    }
    public function getUserByUserId(string $user_id): ?array
    {
        $result = $this->db->SelectOne("SELECT * FROM users WHERE user_id = :user_id", [
            ':user_id' => $user_id
        ]);
        return $result === false ? null : $result;
    }

    public function createUser(array $data): int|false
    {
        $sql = "INSERT INTO users 
                (name, user_id, password, production, role, production_location, created_at)
                VALUES 
                (:name, :user_id, :password, :production, :role, :production_location, :created_at)";

        return $this->db->Insert($sql, [
            ':name' => $data['name'],
            ':user_id' => $data['user_id'],
            ':password' => $data['password'], // must be hashed before passing
            ':production' => $data['production'] ?? null,
            ':role' => $data['role'] ?? null,
            ':production_location' => $data['production_location'] ?? null,
            ':created_at' => $data['created_at']
        ]);
    }

    public function getUserById(int $id): ?array
    {
        return $this->db->SelectOne("SELECT * FROM users WHERE id = :id", [
            ':id' => $id
        ]);
    }
    public function deleteUser(int $id): int
    {
        return $this->db->Update("DELETE FROM users WHERE id = :id", [
            ':id' => $id
        ]);
    }



    public function getAllUsers(): array
    {
        return $this->db->Select("SELECT * FROM users");
    }





    public function updateUser(int $id, array $data): int
    {
        $sql = "UPDATE users SET 
                    name = :name,
                    user_id = :user_id,
                    password = :password,
                    production = :production,
                    role = :role,
                    production_location = :production_location
                WHERE id = :id";

        return $this->db->Update($sql, [
            ':id' => $id,
            ':name' => $data['name'],
            ':user_id' => $data['user_id'],
            ':password' => $data['password'],
            ':production' => $data['production'] ?? null,
            ':role' => $data['role'] ?? null,
            ':production_location' => $data['production_location'] ?? null
        ]);
    }
}
