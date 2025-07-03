<?php

namespace App\Models;
// use Illuminate\Database\Eloquent\Model;
// use MongoDB\Laravel\Eloquent\DocumentModel;
use MongoDB\Laravel\Eloquent\Model as EloquentModel;
class Hotel extends EloquentModel
{
     protected $connection = 'mongodb';
    protected $collection = 'hotels';
    protected $fillable=[
        'name',
        'description',
        'location',
        'rating',
        'phone',
        'email',
        'amenities',
        'images',
        'is_active',
        'coordinates'
    ];
    protected $casts=[
        'amenities' => 'array',
        'images' => 'array',
        'coordinates' => 'array',
        'is_active' => 'boolean'
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
