<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Item extends Model {
    protected $primaryKey = 'item_id';
    public $timestamps = false;

    protected $fillable = [
        'name', 'description', 'image_url', 'date_reported',
        'last_known_location', 'latitude', 'longitude',
        'item_status', 'verification_status', 'rejection_reason',
        'reported_by_user_id', 'category_id'
    ];

    public function reporter() {
        return $this->belongsTo(User::class, 'reported_by_user_id', 'user_id');
    }

    public function category() {
        return $this->belongsTo(Category::class, 'category_id', 'category_id');
    }

    public function claims() {
        return $this->hasMany(Claim::class, 'item_id', 'item_id');
    }
}