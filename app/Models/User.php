<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable {
    protected $primaryKey = 'user_id';
    public $timestamps = false;

    protected $fillable = [
        'student_id', 'full_name', 'email', 'phone_number',
        'password_hash', 'course_id', 'section_name',
        'user_role', 'user_status'
    ];

    protected $hidden = ['password_hash'];

    public function course() {
        return $this->belongsTo(Course::class, 'course_id', 'course_id');
    }

    public function items() {
        return $this->hasMany(Item::class, 'reported_by_user_id', 'user_id');
    }

    public function claims() {
        return $this->hasMany(Claim::class, 'claimed_by_user_id', 'user_id');
    }
}