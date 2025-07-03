<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;
use Carbon\Carbon;

class Booking extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'bookings';
    //
    protected $fillable = [
        'user_id',
        'hotel_id',
        'room_id',
        'booking_reference',
        'check_in_date',
        'check_out_date',
        'guests',
        'nights',
        'total_amount',
        'currency',
        'payment_method',
        'payment_status',
        'status',
        'special_requests',
        'guest_details',
        'created_at',
        'updated_at'
    ];
     /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'check_in_date' => 'date',
        'check_out_date' => 'date',
        'guests' => 'integer',
        'nights' => 'integer',
        'total_amount' => 'decimal:2',
        'guest_details' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    // protected $appends = [
    //     'duration_text',
    //     'is_upcoming',
    //     'is_active'
    // ];
 protected $dates = ['created_at', 'updated_at'];
    public function hotel(){
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }
    /**
     * Define relationship with Room model
     */
    public function room()
    {
        return $this->belongsTo(Room::class, 'room_id');
    }
    /**
     * Define relationship with User model
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

