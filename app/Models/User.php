<?php

namespace App\Models;

class User
{
    public $id;
    public $name;
    public $email;
    public $phone;
    public $role;
    public $role_status; // New field: 'approved', 'request_pending', 'declined'
    public $password;
    public $created_at;
    public $updated_at;
    // Student properties
    public $dob;
    public $course;
    public $nic;
    public $gender;
    public $address;
    public $emergencyContact;
    public $institute;
    // Lecturer properties
    public $department;
    public $module;
    public $experience_years;

    public function __construct(array $data)
    {
        $this->id         = isset($data['_id']) ? (string) $data['_id'] : null;
        $this->name       = $data['name'] ?? null;
        $this->email      = $data['email'] ?? null;
        $this->phone      = $data['phone'] ?? null;
        $this->role       = $data['role'] ?? null;
        $this->role_status = $data['role_status'] ?? 'approved'; // Default for existing users
        $this->password   = $data['password'] ?? null;
        $this->created_at = $data['created_at'] ?? null;
        $this->updated_at = $data['updated_at'] ?? null;
        
        // Student properties
        $this->dob = $data['dob'] ?? null;
        $this->course = $data['course'] ?? null;
        $this->nic = $data['nic'] ?? null;
        $this->gender = $data['gender'] ?? null;
        $this->address = $data['address'] ?? null;
        $this->emergencyContact = $data['emergencyContact'] ?? [
            'relation' => null,
            'contactNo' => null
        ];
        $this->institute = $data['institute'] ?? null;
        
        // Lecturer properties
        $this->department = $data['department'] ?? null;
        $this->module = $data['module'] ?? null;
        $this->experience_years = $data['experience_years'] ?? null;
    }

    public static function fromRequest(array $validated)
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);
        
        return new self([
            'name'       => $validated['name'],
            'email'      => $validated['email'],
            'phone'      => $validated['phone'],
            'role'       => $validated['role'],
            'password'   => $validated['password'], // Already hashed!
            'created_at' => $currentTime->format('Y-m-d H:i:s'),
            'updated_at' => $currentTime->format('Y-m-d H:i:s'),
        ]);
    }

    public static function fromStudentRequest(array $data): self
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);
        $timestamp = $currentTime->format('Y-m-d H:i:s');

        return new self([
            'name' => $data['name'],
            'dob' => $data['dob'],
            'course' => $data['course'],
            'nic' => $data['nic'],
            'gender' => $data['gender'],
            'phone' => $data['phoneNo'],
            'address' => $data['address'],
            'emergencyContact' => [
                'relation' => $data['emergency_relation'] ?? null,
                'contactNo' => $data['emergency_contact'] ?? null
            ],
            'institute' => $data['institute'],
            'updated_at' => $timestamp
        ]);
    }

    public static function fromLecturerRequest(array $data): self
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);
        $timestamp = $currentTime->format('Y-m-d H:i:s');

        return new self([
            'name' => $data['name'],
            'department' => $data['department'],
            'module' => $data['module'],
            'experience_years' => (int)$data['experience_years'],
            'phone' => $data['phoneNo'],
            'nic' => $data['nic'],
            'address' => $data['address'],
            'institute' => $data['institute'],
            'updated_at' => $timestamp
        ]);
    }

    public function toArray()
    {
        return [
            'name'       => $this->name,
            'email'      => $this->email,
            'phone'      => $this->phone,
            'role'       => $this->role,
            'role_status' => $this->role_status,
            'password'   => $this->password,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            // Student properties
            'dob' => $this->dob,
            'course' => $this->course,
            'nic' => $this->nic,
            'gender' => $this->gender,
            'address' => $this->address,
            'emergencyContact' => $this->emergencyContact,
            'institute' => $this->institute,
            // Lecturer properties
            'department' => $this->department,
            'module' => $this->module,
            'experience_years' => $this->experience_years,
        ];
    }

    public function toResponseArray()
    {
        $arr = $this->toArray();
        unset($arr['password']);
        $arr['_id'] = $this->id;
        return $arr;
    }

    /**
     * Check if user has full access to their role features
     */
    public function hasFullAccess(): bool
    {
        return $this->role_status === 'approved';
    }

    /**
     * Check if user's request is pending
     */
    public function isRequestPending(): bool
    {
        return $this->role_status === 'request_pending';
    }

    /**
     * Check if user's request was declined
     */
    public function isRequestDeclined(): bool
    {
        return $this->role_status === 'declined';
    }

    public function updateTimestamp(): void
    {
        $sriLankaTimezone = new \DateTimeZone('Asia/Colombo');
        $currentTime = new \DateTime('now', $sriLankaTimezone);
        $this->updated_at = $currentTime->format('Y-m-d H:i:s');
    }
}