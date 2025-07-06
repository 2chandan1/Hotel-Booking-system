<template>
    <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8 px-4">
        <div class="max-w-7xl mx-auto">
            <!-- Header -->
            <div class="relative flex items-center mb-6 min-h-[48px]">
                <!-- Home Back Arrow (left) -->
                <RouterLink
                    to="/"
                    class="inline-flex items-center text-blue-600 hover:text-blue-800 font-medium"
                >
                    <svg class="w-6 h-6 mr-1" fill="none" stroke="currentColor" stroke-width="2"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                    </svg>
                    Home
                </RouterLink>

                <!-- Centered Header -->
                <h1 class="absolute left-1/2 transform -translate-x-1/2 text-4xl font-bold text-gray-800 mb-0 pointer-events-none select-none">
                    My Bookings
                </h1>
            </div>

            <div class="text-center mb-8">
                <p class="text-gray-600">Manage all your hotel reservations</p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="flex justify-center items-center py-20">
                <div class="relative">
                    <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-8 h-8 bg-blue-600 rounded-full animate-pulse"></div>
                    </div>
                </div>
                <span class="ml-4 text-lg text-gray-600">Loading your bookings...</span>
            </div>

            <!-- Error State -->
            <div v-else-if="error"
                class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-sm max-w-2xl mx-auto">
                <div class="flex items-center">
                    <svg class="w-6 h-6 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <div>
                        <h3 class="text-red-800 font-semibold">Error Loading Bookings</h3>
                        <p class="text-red-700">{{ error }}</p>
                    </div>
                </div>
            </div>

            <!-- No Bookings State -->
            <div v-else-if="bookings.length === 0" class="text-center py-20">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9 2a1 1 0 000 2h2a1 1 0 100-2H9z"></path>
                        <path fill-rule="evenodd"
                            d="M4 5a2 2 0 012-2 3 3 0 003 3h2a3 3 0 003-3 2 2 0 012 2v6a2 2 0 01-2 2H6a2 2 0 01-2-2V5zm3 2a1 1 0 000 2h.01a1 1 0 100-2H7zm3 0a1 1 0 000 2h3a1 1 0 100-2h-3zm-3 4a1 1 0 100 2h.01a1 1 0 100-2H7zm3 0a1 1 0 100 2h3a1 1 0 100-2h-3z"
                            clip-rule="evenodd"></path>
                    </svg>
                </div>
                <h3 class="text-2xl font-semibold text-gray-800 mb-3">No Bookings Yet</h3>
                <p class="text-gray-600 mb-6">You haven't made any hotel reservations yet.</p>
                <button
                    class="bg-blue-600 text-white px-6 py-3 rounded-lg hover:bg-blue-700 transition-colors font-medium">
                    Browse Hotels
                </button>
            </div>

            <!-- Bookings Table -->
            <div v-else class="bg-white rounded-2xl shadow-xl overflow-hidden">
                <!-- Stats Header -->
                <div class="bg-gradient-to-r from-blue-600 to-purple-600 p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-2xl font-bold">Your Reservations</h2>
                            <p class="text-blue-100">{{ bookings.length }} booking{{ bookings.length !== 1 ? 's' : '' }}
                                found</p>
                        </div>
                        <div class="text-right">
                            <div class="text-3xl font-bold">{{ bookings.length }}</div>
                            <div class="text-sm text-blue-100">Total Bookings</div>
                        </div>
                    </div>
                </div>

                <!-- Desktop Table -->
                <div class="hidden lg:block overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Hotel & Room
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Dates
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Duration
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Status
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Amount
                                </th>
                                <th
                                    class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            <tr v-for="booking in bookings" :key="booking.id"
                                class="hover:bg-gray-50 transition-colors">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <div
                                            class="flex-shrink-0 h-12 w-12 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                            <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                                </path>
                                            </svg>
                                        </div>
                                        <div class="ml-4">
                                            <div class="text-sm font-medium text-gray-900">{{ booking.hotel?.name ||
                                                'N/A' }}</div>
                                            <div class="text-sm text-gray-500">{{ booking.room?.name || 'Standard Room'
                                                }}</div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900">
                                        <div class="font-medium">{{ formatDate(booking.check_in_date) }}</div>
                                        <div class="text-gray-500">to {{ formatDate(booking.check_out_date) }}</div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ calculateNights(booking.check_in_date, booking.check_out_date) }} nights
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <span class="px-3 py-1 text-xs font-semibold rounded-full"
                                        :class="getStatusClass(booking.status)">
                                        {{ booking.status?.toUpperCase() || 'PENDING' }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-bold text-green-600">
                                        ${{ booking.total_amount || 'N/A' }}
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                    <button @click="viewBookingDetails(booking.id)"
                                        class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium shadow-sm hover:shadow-md">
                                        View Details
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Mobile Cards -->
                <div class="lg:hidden space-y-4 p-4">
                    <div v-for="booking in bookings" :key="booking.id"
                        class="bg-white border border-gray-200 rounded-xl p-4 shadow-sm">
                        <div class="flex items-center justify-between mb-3">
                            <div class="flex items-center">
                                <div
                                    class="flex-shrink-0 h-10 w-10 bg-gradient-to-r from-blue-500 to-purple-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path
                                            d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z">
                                        </path>
                                    </svg>
                                </div>
                                <div class="ml-3">
                                    <h3 class="text-sm font-medium text-gray-900">{{ booking.hotel?.name || 'N/A' }}
                                    </h3>
                                    <p class="text-xs text-gray-500">{{ booking.room?.name || 'Standard Room' }}</p>
                                </div>
                            </div>
                            <span class="px-2 py-1 text-xs font-semibold rounded-full"
                                :class="getStatusClass(booking.status)">
                                {{ booking.status?.toUpperCase() || 'PENDING' }}
                            </span>
                        </div>

                        <div class="space-y-2 mb-4">
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Check-in:</span>
                                <span class="font-medium">{{ formatDate(booking.check_in_date) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Check-out:</span>
                                <span class="font-medium">{{ formatDate(booking.check_out_date) }}</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Duration:</span>
                                <span class="font-medium">{{ calculateNights(booking.check_in_date,
                                    booking.check_out_date) }} nights</span>
                            </div>
                            <div class="flex justify-between text-sm">
                                <span class="text-gray-500">Total Amount:</span>
                                <span class="font-bold text-green-600">${{ booking.total_amount || 'N/A' }}</span>
                            </div>
                        </div>

                        <button @click="viewBookingDetails(booking.id)"
                            class="w-full bg-blue-600 text-white py-2  rounded-lg hover:bg-blue-700 transition-colors text-sm font-medium cursor-pointer">
                            View Details
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useHotelStore } from '@/store/hotelStore';

const route = useRoute();
const router = useRouter();
const hotelStore = useHotelStore();
const userId = route.params.userId;
const bookings = ref([]);
const loading = ref(false);
const error = ref(null);

// Utility function to format dates
const formatDate = (dateString) => {
    if (!dateString) return 'N/A';
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric'
    });
};

// Calculate number of nights
const calculateNights = (checkIn, checkOut) => {
    if (!checkIn || !checkOut) return 0;
    const start = new Date(checkIn);
    const end = new Date(checkOut);
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};

// Get status styling classes
const getStatusClass = (status) => {
    switch (status?.toLowerCase()) {
        case 'confirmed':
            return 'bg-green-100 text-green-800';
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'checked-in':
            return 'bg-blue-100 text-blue-800';
        case 'completed':
            return 'bg-purple-100 text-purple-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
};

// Navigate to booking details
const viewBookingDetails = (bookingId) => {
    router.push({ name: 'MyBookings', params: { bookingId } });
};

onMounted(async () => {
    loading.value = true;
    error.value = null;
    try {
        bookings.value = await hotelStore.getBookingsByUserId(userId);
    } catch (err) {
        error.value = err?.message || 'Failed to load bookings';
    } finally {
        loading.value = false;
    }
});
</script>