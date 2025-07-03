<?php

namespace App\Models;
use MongoDB\Laravel\Eloquent\Model as EloquentModel;
use Carbon\Carbon;
class Room extends EloquentModel
{
    protected $connection = 'mongodb';
    protected $collection = 'rooms';
     protected $fillable = [
        'hotel_id',
        'room_number',
        'room_type',
        'description',
        'price_per_night',
        'currency',
        'capacity',
        'beds',
        'bed_type',
        'size_sqm',
        'amenities',
        'images',
        'is_available',
        'is_active',
        'floor',
        'view_type',
        'smoking_allowed',
        'accessibility_features',
        'special_features',
        'maintenance_status',
        'last_cleaned',
        'created_at',
        'updated_at'
    ];
     protected $casts = [
        'price_per_night' => 'decimal:2',
        'capacity' => 'integer',
        'beds' => 'integer',
        'size_sqm' => 'integer',
        'floor' => 'integer',
        'amenities' => 'array',
        'images' => 'array',
        'accessibility_features' => 'array',
        'special_features' => 'array',
        'is_available' => 'boolean',
        'is_active' => 'boolean',
        'smoking_allowed' => 'boolean',
        'last_cleaned' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    public function hotel()
    {
        return $this->belongsTo(Hotel::class, 'hotel_id');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'room_id');
    }
}
