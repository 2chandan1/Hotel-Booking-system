<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        try {
            $totalBookings = Booking::count();
            
            // Safely cast Decimal128 to float before rounding
            $revenueRaw = Booking::where('payment_status', 'paid')->get(['total_amount']);
            $totalRevenue = $revenueRaw->reduce(function ($carry, $item) {
                return $carry +  $item->total_amount;
            }, 0);

            $totalHotels = Hotel::count();
            $totalRooms = Room::count();
            $totalUsers = User::count();

            $bookingsByStatus = Booking::raw(function ($collection) {
                return $collection->aggregate([
                    ['$group' => ['_id' => '$status', 'count' => ['$sum' => 1]]]
                ]);
            });

            $revenueByPayment = Booking::raw(function ($collection) {
                return $collection->aggregate([
                    ['$match' => ['payment_status' => 'paid']],
                    ['$group' => [
                        '_id' => '$payment_method',
                        'total' => ['$sum' => '$total_amount']
                    ]]
                ]);
            });

            // Format revenueByPayment totals to float
            $formattedRevenueByPayment = [];
            foreach ($revenueByPayment as $item) {
                $formattedRevenueByPayment[] = [
                    'payment_method' => $item->_id,
                   'total' => round((float) (string) $item->total, 2),

                ];
            }

            return response()->json([
                'total_bookings' => $totalBookings,
                'total_revenue' => round($totalRevenue, 2),
                'total_hotels' => $totalHotels,
                'total_rooms' => $totalRooms,
                'total_users' => $totalUsers,
                'bookings_by_status' => $bookingsByStatus,
                'revenue_by_payment_method' => $formattedRevenueByPayment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error fetching dashboard data',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
        'file' => $e->getFile(),
        'trace' => $e->getTraceAsString(), // optional: full stack trace
            ], 500);
        }
    }
}
