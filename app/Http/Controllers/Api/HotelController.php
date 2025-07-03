<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Hotel;
// use App\Models\Booking;
use MongoDB\BSON\Regex;
// use Carbon\Carbon;
use Illuminate\Validation\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;

class HotelController extends Controller
{
    public function index(Request $request)
    {
        try {

            $query = Hotel::query();
            $perPage = $request->get('per_page', 15);
            $perPage = min($perPage, 100);
            // if ($request->has('is_active')) {
            //     $query->where('is_active', $request->boolean('is_active'));
            // }
            $query->orderBy('created_at', 'desc');
            $hotels = $query->paginate($perPage);
            return response()->json([
                'hotels' => $hotels,
                'message' => 'Hotels retrieved successfully',
            ], 200);
        } catch (Exception $e) {
            Log::error('Hotel index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving hotels',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'description' => 'required|string|max:1000',
                'location' => 'required|string|max:255',
                'rating' => 'required|numeric|min:0|max:5',
                'phone' => 'required|string|max:20',
                'email' => 'required|email|max:255',
                'base_price' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'amenities' => 'required|array',
                'amenities.*' => 'string|max:100',
                'images' => 'required|array',
                'images.*' => 'url',
                'is_active' => 'required|boolean',
                'coordinates' => 'required|array',
                'coordinates.type' => 'required|in:Point',
                'coordinates.coordinates' => 'required|array|size:2',
                'coordinates.coordinates.*' => 'numeric',
            ]);

            $hotel = Hotel::create($validated);

            return response()->json([
                'message' => 'Hotel created successfully',
                'hotel' => $hotel,
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Hotel creation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while creating the hotel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(string $id)
    {
        try {
            $hotel = Hotel::find($id);
            if (!$hotel) {
                return response()->json([
                    'message' => 'Hotel bg not found',
                ], 404);
            }
            return response()->json([
                'hotel' => $hotel,
                'message' => 'Hotel retrieved successfully',
            ], 200);
        } catch (\Exception $e) {
            Log::error('Hotel retrieval error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving the hotel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, string $id)
    {
        try {
            $hotel = Hotel::find($id);
            if (!$hotel) {
                return response()->json([
                    'message' => 'Hotel not found',
                ], 404);
            }
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'description' => 'sometimes|string|max:1000',
                'location' => 'sometimes|string|max:255',
                'rating' => 'sometimes|numeric|min:0|max:5',
                'phone' => 'sometimes|string|max:20',
                'email' => 'sometimes|email|max:255',
                'base_price' => 'sometimes|required|numeric|min:0',
                'currency' => 'sometimes|required|string|size:3',
                'amenities' => 'sometimes|array',
                'amenities.*' => 'string|max:100',
                'images' => 'sometimes|array',
                'images.*' => 'url',
                'is_active' => 'sometimes|boolean',
                'coordinates' => 'sometimes|array',
                'coordinates.type' => 'required_with:coordinates|in:Point',
                'coordinates.coordinates' => 'required_with:coordinates|array|size:2',
                'coordinates.coordinates.*' => 'numeric',
            ]);
            $hotel->update($validated);

            return response()->json([
                'message' => 'Hotel updated successfully',
                'data' => $hotel->fresh()
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Hotel update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the hotel',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(string $id)
    {
        try {
            $hotel = Hotel::find($id);
            if (!$hotel) {
                return response()->json([
                    'message' => 'Hotel not found',
                ], 404);
            }
            $hotel->delete();
            return response()->json([
                'message' => 'Hotel deleted successfully',
                'hotel_id' => $id
            ], 200);
        } catch (Exception $e) {
            Log::error('Hotel deletion error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while deleting the hotel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function search(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'location' => 'sometimes|string|max:255',
                'name' => 'sometimes|string|max:255',
                'min_rating' => 'sometimes|numeric|min:0|max:5',
                'max_rating' => 'sometimes|numeric|min:0|max:5',
                'min_price' => 'sometimes|numeric|min:0',
                'max_price' => 'sometimes|numeric|min:0|gte:min_price',
                'price_range' => 'sometimes|string|in:budget,mid-range,luxury,premium',
                'amenities' => 'sometimes|array',
                'amenities.*' => 'string',
                'latitude' => 'sometimes|numeric|between:-90,90',
                'longitude' => 'sometimes|numeric|between:-180,180',
                'radius' => 'sometimes|numeric|min:0.1|max:100',
                'sort_by' => 'sometimes|string|in:price_asc,price_desc,rating_asc,rating_desc,name_asc,name_desc',
                'per_page' => 'sometimes|integer|min:1|max:100'
            ]);
            if ($validator->fails()) {
                return response()->json([
                    'message' => 'Validation failed',
                    'errors' => $validator->errors()
                ], 422);
            }
            $query = Hotel::where('is_active', true);
            // Location search with MongoDB regex (case-insensitive)
            if ($request->location) {
                $query->where('location', 'regex', new Regex($request->location, 'i'));
            }
            if ($request->name) {
                $query->where('name', 'regex', new Regex($request->name, 'i'));
            }
            if ($request->min_rating) {
                $query->where('rating', '>=', $request->min_rating);
            }
            if ($request->max_rating) {
                $query->where('rating', '<=', $request->max_rating);
            }
            // Price range filter
            if ($request->min_price) {
                $query->where('base_price', '>=', $request->min_price);
            }
            if ($request->max_price) {
                $query->where('base_price', '<=', $request->max_price);
            }

            if ($request->price_range) {
                switch ($request->price_range) {
                    case 'budget':
                        $query->where('base_price', '<=', 2000);
                        break;
                    case 'mid-range':
                        $query->whereBetween('base_price', [2000, 4000]);
                        break;
                    case 'luxury':
                        $query->whereBetween('base_price', [4000, 6000]);
                        break;
                    case 'premium':
                        $query->where('base_price', '>', 6000);
                        break;
                    default:
                        return response()->json([
                            'message' => 'Invalid price range specified',
                        ], 400);
                }
            }

            if ($request->amenities && count($request->amenities) > 0) {
                $query->where('amenities', '$all', $request->amenities);
            }
            if ($request->latitude && $request->longitude && $request->radius) {
                $radius = $request->radius ?? 10;
                $radiusInMeters = $radius * 1000; // converting into km
                $query->where('coordinates', 'near', [
                    '$geometry' => [
                        'type' => 'Point',
                        'coordinates' => [(float)$request->longitude, (float)$request->latitude]
                    ],
                    '$maxDistance' => $radiusInMeters
                ]);
            }

            $sortBy = $request->sort_by ?? 'rating_desc';
            switch ($sortBy) {
                case 'price_asc':
                    $query->orderBy('base_price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('base_price', 'desc');
                    break;
                case 'rating_asc':
                    $query->orderBy('rating', 'asc');
                    break;
                case 'rating_desc':
                    $query->orderBy('rating', 'desc');
                    break;
                case 'name_asc':
                    $query->orderBy('name', 'asc');
                    break;
                case 'name_desc':
                    $query->orderBy('name', 'desc');
                    break;
                default:
                    $query->orderBy('rating', 'desc')->orderBy('name', 'asc');; // Default sort by rating descending
                    break;
            }
            $perPage = $request->get('per_page', 15);
            $hotel = $query->paginate($perPage);

            return response()->json([
                'hotels' => $hotel,
                'message' => 'Hotels retrieved successfully',
                'search_params' => $request->only([
                    'location',
                    'name',
                    'min_rating',
                    'max_rating',
                    'amenities',
                    'latitude',
                    'longitude',
                    'radius'
                ])
            ], 200);
        } catch (Exception $e) {
            Log::error('Hotel search error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while searching hotels',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function toggleStatus(string $id)
    {
        try {
            $hotel = Hotel::find($id);
            if (!$hotel) {
                return response()->json([
                    'message' => 'Hotel not found',

                ], 404);
            }
            $hotel->is_active = !$hotel->is_active;
            $hotel->save();
            return response()->json([
                'message' => 'Hotel status toggled successfully',
                'data' => $hotel
            ], 200);
        } catch (Exception $e) {
            Log::error('Hotel status toggle error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while toggling hotel status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getStats()
    {
        try {
            $totalHotels = Hotel::count();
            $activeHotels = Hotel::where('is_active', true)->count();
            $inactiveHotels = Hotel::where('is_active', false)->count();
            $averageRating = Hotel::where('is_active', true)->avg('rating');
            $topRatedCount = Hotel::where('is_active', true)
                ->where('rating', '>=', 4.5)
                ->count();
            return response()->json([
                'message' => 'Statistics retrieved successfully',
                'data' => [
                    'total_hotels' => $totalHotels,
                    'active_hotels' => $activeHotels,
                    'inactive_hotels' => $inactiveHotels,
                    'average_rating' => round($averageRating, 2),
                    'top_rated_hotels' => $topRatedCount
                ]
            ], 200);
        } catch (Exception $e) {
            Log::error('Hotel stats retrieval error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving hotel stats',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
