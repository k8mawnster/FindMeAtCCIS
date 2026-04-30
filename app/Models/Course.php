<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model {
    protected $primaryKey = 'course_id';
    public $timestamps = false;

    protected $fillable = ['course_code', 'course_name'];

    public function users() {
        return $this->hasMany(User::class, 'course_id', 'course_id');
    }
}