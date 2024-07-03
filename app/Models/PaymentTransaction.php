<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PaymentTransaction extends Model {
    protected $fillable = [
        'user_id',
        'amount',
        'payment_gateway',
        'order_id',
        'payment_id',
        'payment_signature',
        'payment_status',
        'created_at',
        'updated_at'
    ];
    use HasFactory;

    public function user() {
        return $this->belongsTo(User::class);
    }
}
