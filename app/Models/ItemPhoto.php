<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ItemPhoto extends Model {
    protected $primaryKey = 'photo_id';
    public $timestamps = false;

    protected $fillable = ['item_id', 'image_url'];

    public function item() {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }
}
