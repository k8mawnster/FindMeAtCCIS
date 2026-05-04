<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Claim extends Model {
    protected $primaryKey = 'claim_id';
    public $timestamps = false;

    protected $fillable = [
        'item_id', 'claimed_by_user_id', 'claimer_full_name',
        'claimer_email', 'claimer_course_section', 'claim_date',
        'proof_description', 'proof_photo_url', 'claim_status',
        'pickup_schedule', 'pickup_location', 'pickup_notes'
    ];

    public function item() {
        return $this->belongsTo(Item::class, 'item_id', 'item_id');
    }

    public function claimedBy() {
        return $this->belongsTo(User::class, 'claimed_by_user_id', 'user_id');
    }
}
