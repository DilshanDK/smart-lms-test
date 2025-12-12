<?php

namespace App\Models;

class Institute
{
    public $id;
    public $institute;
    public $description;
    public $status;

    public function __construct(array $data)
    {
        $this->id = isset($data['_id']) ? (string)$data['_id'] : null;
        $this->institute = $data['institute'] ?? null;
        $this->description = $data['description'] ?? null;
        $this->status = $data['status'] ?? 'active';
    }

    public static function fromRequest(array $validated)
    {
        return new self([
            'institute' => $validated['institute'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? 'active',
        ]);
    }

    public function toArray()
    {
        return [
            'institute' => $this->institute,
            'description' => $this->description,
            'status' => $this->status,
        ];
    }
}
