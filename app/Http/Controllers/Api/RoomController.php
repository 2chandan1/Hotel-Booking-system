<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Models\Hotel;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;
use Carbon\Carbon;
use Exception;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;
class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        try {
            $query = Room::query();
            // Filter by hotel
            if ($request->has('hotel_id')) {
                $query->byHotel($request->hotel_id);
            }
            if ($request->has('room_type')) {
                $query->byRoomType($request->room_type);
            }
            if ($request->has('hotel_id')) {
                $query->byHotel($request->hotel_id);
            }
            // Filter by capacity
            if ($request->has('capacity')) {
                $query->byCapacity($request->capacity);
            }

            // Filter by price range
            if ($request->has('min_price') && $request->has('max_price')) {
                $query->byPriceRange($request->min_price, $request->max_price);
            }

            // Filter by availability status
            if ($request->has('available') && $request->available === 'true') {
                $query->available();
            }

            // Filter by active status
            if ($request->has('active')) {
                $query->where('is_active', $request->active === 'true');
            }
            // Filter by availability for specific dates
            if ($request->has('check_in_date') && $request->has('check_out_date')) {
                $checkIn = Carbon::parse($request->check_in_date);
                $checkOut = Carbon::parse($request->check_out_date);

                $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                    $q->where('status', 'confirmed')
                        ->where(function ($query) use ($checkIn, $checkOut) {
                            $query->whereBetween('check_in_date', [$checkIn, $checkOut])
                                ->orWhereBetween('check_out_date', [$checkIn, $checkOut])
                                ->orWhere(function ($q) use ($checkIn, $checkOut) {
                                    $q->where('check_in_date', '<=', $checkIn)
                                        ->where('check_out_date', '>=', $checkOut);
                                });
                        });
                });
            }
                $query->with(['hotel:_id,name,location']);
                // Sorting
                $sortBy = $request->get('sort_by', 'room_number');
                $sortOrder = $request->get('sort_order', 'asc');
                $query->orderBy($sortBy, $sortOrder);
                // Pagination
                $perPage = $request->get('per_page', 15);
                $rooms = $query->paginate($perPage);
                return response()->json([
                    'success' => true,
                    'message' => 'Rooms retrieved successfully',
                    'data' => $rooms->items(),
                    'pagination' => [
                        'current_page' => $rooms->currentPage(),
                        'per_page' => $rooms->perPage(),
                        'total' => $rooms->total(),
                        'last_page' => $rooms->lastPage(),
                        'from' => $rooms->firstItem(),
                        'to' => $rooms->lastItem(),
                    ]
                ], 200);
            
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error retrieving rooms',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request){
        try {
           $validated = $request->validate([
                'hotel_id' => 'required|string',
                'room_number' => 'required|string|max:20',
                'room_type' => 'required|string|in:single,double,suite,family,deluxe,presidential',
                'description' => 'nullable|string|max:1000',
                'price_per_night' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'capacity' => 'required|integer|min:1|max:20',
                'beds' => 'required|integer|min:1|max:10',
                'bed_type' => 'required|string|in:single,double,queen,king,twin,sofa',
                'size_sqm' => 'nullable|integer|min:1',
                'amenities' => 'required|array',
                'amenities.*' => 'string|max:100',
                'images' => 'required|array',
                'images.*' => 'url',
                'is_available' => 'required|boolean',
                'is_active' => 'required|boolean',
                'floor' => 'nullable|integer',
                'view_type' => 'nullable|string|in:city,ocean,mountain,garden,pool,courtyard',
                'smoking_allowed' => 'required|boolean',
                'accessibility_features' => 'nullable|array',
                'accessibility_features.*' => 'string|max:100',
                'special_features' => 'nullable|array',
                'special_features.*' => 'string|max:100',
                'maintenance_status' => 'required|string|in:good,needs_attention,under_maintenance',
            ]);
             $hotel = Hotel::find($validated['hotel_id']);
            if (!$hotel) {
                return response()->json([
                    'message' => 'Hotel not found'
                ], 404);
            }
              // Check for duplicate room number in the same hotel
            $existingRoom = Room::where('hotel_id', $validated['hotel_id'])
                               ->where('room_number', $validated['room_number'])
                               ->first();
            
            if ($existingRoom) {
                return response()->json([
                    'message' => 'Room number already exists for this hotel'
                ], 422);
            }
            $room = Room::create($validated);

            return response()->json([
                'message' => 'Room created successfully',
                'room' => $room,
            ], 201);
       } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Room creation error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while creating the room',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     public function show(string $id): JsonResponse
    {
        try {
            $room = Room::with(['hotel:_id,name,location,rating', 'bookings' => function($query) {
                $query->where('status', 'confirmed')
                      ->where('check_out_date', '>=', Carbon::now())
                      ->orderBy('check_in_date');
            }])->find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            return response()->json([
                'success' => true,
                'message' => 'Room retrieved successfully',
                'data' => $room
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving room',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     public function update(Request $request, string $id): JsonResponse
    {
        try {
            $room = Room::find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            $validatedData = $request->validate([
                'hotel_id' => 'sometimes|required|string',
                'room_number' => 'sometimes|required|string|max:20',
                'room_type' => 'sometimes|required|string|in:single,double,suite,family,deluxe,presidential',
                'description' => 'nullable|string',
                'price_per_night' => 'sometimes|required|numeric|min:0',
                'currency' => 'sometimes|required|string|max:3',
                'capacity' => 'sometimes|required|integer|min:1|max:20',
                'beds' => 'sometimes|required|integer|min:1|max:10',
                'bed_type' => 'sometimes|required|string|in:single,double,queen,king,twin,sofa',
                'size_sqm' => 'nullable|integer|min:1',
                'amenities' => 'nullable|array',
                'amenities.*' => 'string',
                'images' => 'nullable|array',
                'images.*' => 'string',
                'is_available' => 'boolean',
                'is_active' => 'boolean',
                'floor' => 'nullable|integer',
                'view_type' => 'nullable|string|in:city,ocean,mountain,garden,pool,courtyard',
                'smoking_allowed' => 'boolean',
                'accessibility_features' => 'nullable|array',
                'accessibility_features.*' => 'string',
                'special_features' => 'nullable|array',
                'special_features.*' => 'string',
                'maintenance_status' => 'nullable|string|in:good,needs_attention,under_maintenance',
            ]);

            // Check if hotel exists (if hotel_id is being updated)
            if (isset($validatedData['hotel_id'])) {
                $hotel = Hotel::find($validatedData['hotel_id']);
                if (!$hotel) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Hotel not found'
                    ], 404);
                }
            }

            // Check for duplicate room number (if room_number or hotel_id is being updated)
            if (isset($validatedData['room_number']) || isset($validatedData['hotel_id'])) {
                $hotelId = $validatedData['hotel_id'] ?? $room->hotel_id;
                $roomNumber = $validatedData['room_number'] ?? $room->room_number;
                
                $existingRoom = Room::where('hotel_id', $hotelId)
                                   ->where('room_number', $roomNumber)
                                   ->where('_id', '!=', $id)
                                   ->first();
                
                if ($existingRoom) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Room number already exists for this hotel'
                    ], 422);
                }
            }

            $room->update($validatedData);

            return response()->json([
                'success' => true,
                'message' => 'Room updated successfully',
                'data' => $room->load('hotel:_id,name,location')
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating room',
                'error' => $e->getMessage()
            ], 500);
        }
    }
     public function destroy(string $id): JsonResponse
    {
        try {
            $room = Room::find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            // Check if room has active bookings
            $activeBookings = $room->bookings()
                ->where('status', 'confirmed')
                ->where('check_out_date', '>=', Carbon::now())
                ->count();

            if ($activeBookings > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Cannot delete room with active bookings. Please cancel all bookings first.'
                ], 422);
            }

            $room->delete();

            return response()->json([
                'success' => true,
                'message' => 'Room deleted successfully'
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting room',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    /**
     * Check room availability for specific dates
     */
    public function checkAvailability(Request $request, string $id): JsonResponse
    {
        try {
            $request->validate([
                'check_in_date' => 'required|date|after_or_equal:today',
                'check_out_date' => 'required|date|after:check_in_date',
            ]);

            $room = Room::find($id);

            if (!$room) {
                return response()->json([
                    'success' => false,
                    'message' => 'Room not found'
                ], 404);
            }

            $checkIn = Carbon::parse($request->check_in_date);
            $checkOut = Carbon::parse($request->check_out_date);

            $isAvailable = $room->isAvailableForDates($checkIn, $checkOut);

            return response()->json([
                'success' => true,
                'message' => 'Room availability checked successfully',
                'data' => [
                    'room_id' => $room->_id,
                    'room_number' => $room->room_number,
                    'is_available' => $isAvailable,
                    'check_in_date' => $checkIn->format('Y-m-d'),
                    'check_out_date' => $checkOut->format('Y-m-d'),
                    'nights' => $checkIn->diffInDays($checkOut),
                    'total_amount' => $isAvailable ? $room->price_per_night * $checkIn->diffInDays($checkOut) : null,
                    'currency' => $room->currency
                ]
            ], 200);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error checking availability',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function statistics(Request $request): JsonResponse
    {
        try {
            $hotelId = $request->get('hotel_id');
            
            $query = Room::query();
            if ($hotelId) {
                $query->where('hotel_id', $hotelId);
            }

            $totalRooms = $query->count();
            $activeRooms = $query->where('is_active', true)->count();
            $availableRooms = $query->available()->count();
            $occupiedRooms = $query->where('is_available', false)->count();
            $maintenanceRooms = $query->where('maintenance_status', 'under_maintenance')->count();

            $roomTypeStats = Room::selectRaw('room_type, count(*) as count')
                ->when($hotelId, function($q) use ($hotelId) {
                    return $q->where('hotel_id', $hotelId);
                })
                ->groupBy('room_type')
                ->get()
                ->pluck('count', 'room_type');

            $averagePrice = $query->avg('price_per_night');

            return response()->json([
                'success' => true,
                'message' => 'Room statistics retrieved successfully',
                'data' => [
                    'total_rooms' => $totalRooms,
                    'active_rooms' => $activeRooms,
                    'available_rooms' => $availableRooms,
                    'occupied_rooms' => $occupiedRooms,
                    'maintenance_rooms' => $maintenanceRooms,
                    'room_types' => $roomTypeStats,
                    'average_price' => round($averagePrice, 2),
                    'occupancy_rate' => $totalRooms > 0 ? round(($occupiedRooms / $totalRooms) * 100, 2) : 0
                ]
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error retrieving statistics',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
