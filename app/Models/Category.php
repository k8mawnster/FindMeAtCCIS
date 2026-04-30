<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model {
    protected $primaryKey = 'category_id';
    public $timestamps = false;

    protected $fillable = ['name'];

    public function items() {
        return $this->hasMany(Item::class, 'category_id', 'category_id');
    }
}