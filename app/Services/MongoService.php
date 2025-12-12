<?php

namespace App\Services;

use MongoDB\Client;
use MongoDB\Collection;
use MongoDB\BSON\UTCDateTime;
use MongoDB\BSON\ObjectId;
use Exception;

class MongoService
{
    protected Client $client;
    protected $db;

    public function __construct()
    {
        try {
            $uri = config('database.connections.mongodb.dsn', env('DB_DSN'));
            $dbName = config('database.connections.mongodb.database', env('DB_DATABASE'));
            $this->client = new Client($uri);
            $this->db = $this->client->selectDatabase($dbName);
        } catch (Exception $e) {
            throw new Exception('Failed to connect to MongoDB: ' . $e->getMessage());
        }
    }

    public function collection(string $name): Collection
    {
        return $this->db->selectCollection($name);
    }

    public function insertOne(string $collection, array $document)
    {
        return $this->collection($collection)->insertOne($document);
    }

    public function findOne(string $collection, array $filter = [])
    {
        return $this->collection($collection)->findOne($filter);
    }

    public function find(string $collection, array $filter = [], array $options = [])
    {
        return $this->collection($collection)->find($filter, $options);
    }

    public function updateOne(string $collection, array $filter, array $update, array $options = [])
    {
        return $this->collection($collection)->updateOne($filter, $update, $options);
    }

    public function deleteOne(string $collection, array $filter)
    {
        return $this->collection($collection)->deleteOne($filter);
    }

    /**
     * Delete multiple documents from a collection
     *
     * @param string $collection Collection name
     * @param array $filter Filter criteria (empty array = delete all)
     * @return \MongoDB\DeleteResult
     */
    public function deleteMany(string $collection, array $filter = [])
    {
        try {
            return $this->db->selectCollection($collection)->deleteMany($filter);
        } catch (\Exception $e) {
            \Log::error('MongoDB deleteMany error', [
                'collection' => $collection,
                'filter' => $filter,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Update multiple documents in a collection
     *
     * @param string $collection Collection name
     * @param array $filter Filter criteria
     * @param array $update Update operations
     * @param array $options Optional parameters
     * @return \MongoDB\UpdateResult
     */
    public function updateMany(string $collection, array $filter, array $update, array $options = [])
    {
        try {
            return $this->db->selectCollection($collection)->updateMany($filter, $update, $options);
        } catch (\Exception $e) {
            \Log::error('MongoDB updateMany error', [
                'collection' => $collection,
                'filter' => $filter,
                'update' => $update,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    // Session management methods
    public function createSession(string $userId, string $token, int $expiresIn = 86400)
    {
        // First, deactivate all existing sessions for this user
        $this->collection('sessions')->updateMany(
            [
                'user_id' => $userId,
                'is_active' => true
            ],
            ['$set' => ['is_active' => false]]
        );

        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $createdAt = new \DateTime('now', $sriLankaTimezone);
        $expiresAt = new \DateTime('now', $sriLankaTimezone);
        $expiresAt->add(new \DateInterval('PT' . $expiresIn . 'S'));

        $sessionData = [
            'user_id' => $userId,
            'token' => $token,
            'created_at' => $createdAt->format('Y-m-d H:i:s'),
            'expires_at' => $expiresAt->format('Y-m-d H:i:s'),
            'is_active' => true
        ];

        return $this->insertOne('sessions', $sessionData);
    }

    public function findSessionByToken(string $token)
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);

        return $this->findOne('sessions', [
            'token' => $token,
            'is_active' => true,
            'expires_at' => ['$gt' => $currentTime->format('Y-m-d H:i:s')]
        ]);
    }

    public function invalidateSession(string $token)
    {
        return $this->updateOne('sessions',
            ['token' => $token],
            ['$set' => ['is_active' => false]]
        );
    }

    public function cleanExpiredSessions()
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);

        return $this->collection('sessions')->deleteMany([
            'expires_at' => ['$lt' => $currentTime->format('Y-m-d H:i:s')]
        ]);
    }

    // Google sign-in user management
    public function createGoogleUser(string $name, string $email)
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);
        $timestamp = $currentTime->format('Y-m-d H:i:s');

        $userData = [
            'name' => $name,
            'email' => $email,
            'role' => 'pending',
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ];

        return $this->insertOne('users', $userData);
    }

    public function findUserByEmail(string $email)
    {
        return $this->findOne('users', ['email' => $email]);
    }

    public function updateUserRole(string $email, string $role)
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);

        return $this->updateOne('users',
            ['email' => $email],
            ['$set' => [
                'role' => $role,
                'updated_at' => $currentTime->format('Y-m-d H:i:s')
            ]]
        );
    }


}
