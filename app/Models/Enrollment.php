<?php

namespace App\Models;

class Enrollment
{
    public $id;
    public $user_id;
    public $course_id;
    public $status; // 'pending', 'enrolled', 'declined'
    public $requested_at;
    public $processed_at;
    public $created_at;
    public $updated_at;

    public function __construct(array $data)
    {
        $this->id = isset($data['_id']) ? (string) $data['_id'] : null;
        $this->user_id = $data['user_id'] ?? null;
        $this->course_id = $data['course_id'] ?? null;
        $this->status = $data['status'] ?? 'pending';
        $this->requested_at = $data['requested_at'] ?? null;
        $this->processed_at = $data['processed_at'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public static function fromRequest(array $data): self
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);
        $timestamp = $currentTime->format('Y-m-d H:i:s');

        return new self([
            'user_id' => $data['user_id'],
            'course_id' => $data['course_id'],
            'status' => 'pending',
            'requested_at' => $timestamp,
            'created_at' => $timestamp,
            'updated_at' => $timestamp
        ]);
    }

    public function toArray()
    {
        return [
            'user_id' => $this->user_id,
            'course_id' => $this->course_id,
            'status' => $this->status,
            'requested_at' => $this->requested_at,
            'processed_at' => $this->processed_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at
        ];
    }
}
