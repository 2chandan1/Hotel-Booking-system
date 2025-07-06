<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use Carbon\Carbon;
use Dotenv\Exception\ValidationException;
use Exception;
use Illuminate\Support\Facades\Log;


class BookingController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Booking::with(['hotel', 'room', 'user']);
            if ($request->user_id) {
                $query->where('user_id', $request->user_id);
            }
            if ($request->hotel_id) {
                $query->where('hotel_id', $request->hotel_id);
            }
            if ($request->staus) {
                $query->where('staus', $request->staus);
            }
            if ($request->start_date && $request->end_date) {
                $query->whereBetween('check_in_date', [
                    Carbon::parse($request->start_date),
                    Carbon::parse($request->end_date)
                ]);
            }
            $query->orderBy('created_at', 'desc');
            $perPage = $request->get('per_page', 15);
            $bookings = $query->paginate($perPage);
            return response()->json([
                'message' => 'Bookings retrieved successfully',
                'data' => $bookings
            ], 200);
        } catch (Exception $e) {
            Log::error('Booking index error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving bookings',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function getBookingsByUser($userId)
    {
        try {
            $bookings = Booking::where('user_id', $userId)->with(['hotel', 'room'])->get();
            return response()->json(['data' => $bookings]);
        } catch (Exception $e) {
            Log::error('Booking get bookings by user error: ' . $e->getMessage());
            return response()->json(['message' => 'An error occurred while retrieving bookings', 'error' => $e->getMessage()], 500);
        }
    }
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'user_id' => 'required|string',
                'hotel_id' => 'required|string|exists:hotels,_id',
                'room_id' => 'required|string|exists:rooms,_id',
                'check_in_date' => 'required|date|after_or_equal:today',
                'check_out_date' => 'required|date|after:check_in_date',
                'guests' => 'required|integer|min:1|max:10',
                'total_amount' => 'required|numeric|min:0',
                'currency' => 'required|string|size:3',
                'payment_method' => 'required|string|in:credit_card,debit_card,paypal,bank_transfer,cash',
                'special_requests' => 'sometimes|string|max:500',
                'guest_details' => 'required|array',
                'guest_details.name' => 'required|string|max:255',
                'guest_details.email' => 'required|email|max:255',
                'guest_details.phone' => 'required|string|max:20',
                'guest_details.address' => 'sometimes|string|max:500',
            ]);
            //check room availability
            $isRoomAvailable = Room::where('_id', $validated['room_id'])
                ->where('hotel_id', $validated['hotel_id'])
                ->where('is_available', true)
                ->exists();

            if (!$isRoomAvailable) {
                return response()->json([
                    'message' => 'Room is not available for the selected dates',
                    'available' => false
                ], 409);
            }
            // Calculate number of nights
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $nights = $checkIn->diffInDays($checkOut);
            $bookingReference = $this->generateBookingReference();
            // Create booking
            $bookingData = array_merge($validated, [
                'booking_refrence' => $bookingReference,
                'nights' => $nights,
                'status' => 'pending',
                'payment_status' => 'pending',
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            $booking = Booking::create($bookingData);
            Room::where('_id', $validated['room_id'])->update(['is_available' => false]);
            $booking->load(['hotel', 'room']);
            return response()->json([
                'message' => 'Booking created successfully',
                'data' => $booking
            ], 201);
        } catch (Exception $e) {
            Log::error('Booking store error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while creating the booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function update(Request $request, $id)
    {
        try {
            // Find the booking
            $booking = Booking::findOrFail($id);

            // Validate input
            $validated = $request->validate([
                'check_in_date' => 'sometimes|date|after_or_equal:today',
                'check_out_date' => 'sometimes|date|after:check_in_date',
                'guests' => 'sometimes|integer|min:1|max:10',
                'total_amount' => 'sometimes|numeric|min:0',
                'currency' => 'sometimes|string|size:3',
                'payment_method' => 'sometimes|string|in:credit_card,debit_card,paypal,bank_transfer,cash',
                'special_requests' => 'sometimes|string|max:500',
                'guest_details' => 'sometimes|array',
                'guest_details.name' => 'sometimes|string|max:255',
                'guest_details.email' => 'sometimes|email|max:255',
                'guest_details.phone' => 'sometimes|string|max:20',
                'guest_details.address' => 'sometimes|string|max:500',
                'room_id' => 'sometimes|string|exists:rooms,_id',
                'hotel_id' => 'sometimes|string|exists:hotels,_id',
                'status' => 'sometimes|string|in:pending,confirmed,cancelled,checked-in,checked-out',
            ]);

            // If room_id or hotel_id is being changed, check room availability
            if (isset($validated['room_id']) && isset($validated['hotel_id'])) {
                $isRoomAvailable = Room::where('_id', $validated['room_id'])
                    ->where('hotel_id', $validated['hotel_id'])
                    ->where('is_available', true)
                    ->exists();

                if (!$isRoomAvailable) {
                    return response()->json([
                        'message' => 'Room is not available for the selected dates',
                        'available' => false
                    ], 409);
                }
            }

            // If dates are being changed, recalculate nights
            if (isset($validated['check_in_date']) && isset($validated['check_out_date'])) {
                $checkIn = Carbon::parse($validated['check_in_date']);
                $checkOut = Carbon::parse($validated['check_out_date']);
                $validated['nights'] = $checkIn->diffInDays($checkOut);
            }

            // Update booking
            $booking->update($validated);

            // If room_id is changed, update room availability
            if (isset($validated['room_id'])) {
                // Set previous room as available
                Room::where('_id', $booking->room_id)->update(['is_available' => true]);
                // Set new room as unavailable
                Room::where('_id', $validated['room_id'])->update(['is_available' => false]);
            }

            $booking->load(['hotel', 'room']);

            return response()->json([
                'message' => 'Booking updated successfully',
                'data' => $booking
            ]);
        } catch (\Exception $e) {
            \Log::error('Booking update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating the booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function show(string $id)
    {
        try {
            $booking = Booking::with(['hotel', 'room'])->find($id);

            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found'
                ], 404);
            }

            return response()->json([
                'message' => 'Booking retrieved successfully',
                'data' => $booking
            ], 200);
        } catch (Exception $e) {
            Log::error('Booking show error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while retrieving the booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    private function generateBookingReference()
    {
        do {
            $reference = 'BK' . strtoupper(substr(uniqid(), -8));
        } while (Booking::where('booking_reference', $reference)->exists());

        return $reference;
    }
    public function cancel(Request $request, string $id)
    {
        try {
            $booking = Booking::find($id);
            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found'
                ], 404);
            }
            if ($booking->status === 'cancelled') {
                return response()->json([
                    'message' => 'Booking is already cancelled'
                ], 400);
            }
            if ($booking->status === 'completed') {
                return response()->json([
                    'message' => 'Cannot cancel a completed booking'
                ], 400);
            }
            $checkInDate = Carbon::parse($booking->check_in_date);
            $currentDate = Carbon::now();
            if ($currentDate->diffInHours($checkInDate) < 24) {
                return response()->json([
                    'message' => 'Cannot cancel booking within 24 hours of check-in'
                ], 400);
            }
            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => Carbon::now(),
                'updated_at' => Carbon::now()
            ]);
            Room::where('_id', $booking->room_id)->update(['is_available' => true]);
            return response()->json([
                'message' => 'Booking cancelled successfully',
                'data' => $booking->fresh(['hotel', 'room'])
            ], 200);
        } catch (Exception $e) {
            Log::error('Booking cancel error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while cancelling the booking',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    public function updateStatus(Request $request, string $id)
    {
        try {
            $booking = Booking::find($id);
            if (!$booking) {
                return response()->json([
                    'message' => 'Booking not found'
                ], 404);
            }
            $validated = $request->validate([
                'status' => 'required|string|in:pending,confirmed,checked_in,checked_out,completed,cancelled',
                'payment_status' => 'sometimes|string|in:pending,paid,refunded,failed',
                'notes' => 'sometimes|string|max:500'
            ]);
            $updates = [
                'status' => $validated['status'],
                'updated_at' => Carbon::now()
            ];
            if (isset($validated['payment_status'])) {
                $updates['payment_status'] = $validated['payment_status'];
            }

            if (isset($validated['notes'])) {
                $updates['admin_notes'] = $validated['notes'];
            }
            switch ($validated['status']) {
                case 'confirmed':
                    $updates['confirmed_at'] = Carbon::now();
                    break;
                case 'checked_in':
                    $updates['checked_in_at'] = Carbon::now();
                    break;
                case 'checked_out':
                    $updates['checked_out_at'] = Carbon::now();
                    break;
                case 'completed':
                    $updates['completed_at'] = Carbon::now();
                    break;
                case 'cancelled':
                    $updates['cancelled_at'] = Carbon::now();
                    break;
            }
            $booking->update($updates);
            return response()->json([
                'message' => 'Booking status updated successfully',
                'data' => $booking->fresh(['hotel', 'room'])
            ], 200);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->getMessage()
            ], 422);
        } catch (Exception $e) {
            Log::error('Booking status update error: ' . $e->getMessage());
            return response()->json([
                'message' => 'An error occurred while updating booking status',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
