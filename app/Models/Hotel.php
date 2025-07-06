<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model as EloquentModel;

class Hotel extends EloquentModel
{
    protected $connection = 'mongodb';
    protected $collection = 'hotels';
    
    protected $fillable = [
        'name',
        'description',
        'location',
        'rating',
        'phone',
        'email',
        'Website',  // Added - note the capital 'W' to match your validation
        'total_rooms',  // Added
        'establishment_year',  // Added
        'policies',  // Added
        'amenities',
        'images',
        'base_price',
        'is_active',
        'coordinates'
    ];
    
    protected $casts = [
        'amenities' => 'array',
        'images' => 'array',
        'coordinates' => 'array',
        'policies' => 'array',  // Added
        'location' => 'array',  // Added since you're validating nested location fields
        'is_active' => 'boolean',
        'rating' => 'decimal:2',  // Added for better decimal handling
        'base_price' => 'decimal:2',  // Added for better decimal handling
        'total_rooms' => 'integer',  // Added
        'establishment_year' => 'integer'  // Added
    ];
    
    protected $dates = ['created_at', 'updated_at'];
    
    // Relationships
    public function rooms()
    {
        return $this->hasMany(Room::class, 'hotel_id');
    }
    
    public function bookings()
    {
        return $this->hasMany(Booking::class, 'hotel_id');
    }
}