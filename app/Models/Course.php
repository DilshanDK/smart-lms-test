<?php

namespace App\Models;

class Course
{
    public $id;
    public $title;
    public $description;
    public $department; // New property
    public $status; // New property
    public $created_at;
    public $updated_at;

    public function __construct(array $data)
    {
        $this->id = isset($data['_id']) ? (string) $data['_id'] : null;
        $this->title = $data['title'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->department = $data['department'] ?? null; // Initialize new property
        $this->status = $data['status'] ?? 'active'; // Default status is 'active'
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
    }

    public static function fromRequest(array $validated): self
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);

        return new self([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'department' => $validated['department'] ?? null, // Handle department input
            'status' => $validated['status'] ?? 'active', // Handle status input
            'created_at' => $currentTime->format('Y-m-d H:i:s'),
            'updated_at' => $currentTime->format('Y-m-d H:i:s'),
        ]);
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'description' => $this->description,
            'department' => $this->department, // Include new property
            'status' => $this->status, // Include new property
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
