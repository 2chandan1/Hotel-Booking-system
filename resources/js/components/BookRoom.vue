<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-4xl mx-auto px-4">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-900">Complete Your Booking</h1>
                <p class="text-gray-600 mt-2">Please review your booking details and confirm</p>
            </div>

            <!-- Loading State -->
            <div v-if="loading" class="text-center py-8">
                <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600 mx-auto"></div>
                <p class="mt-4 text-gray-600">Processing your booking...</p>
            </div>

            <!-- Error State -->
            <div v-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-red-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-red-700">{{ error }}</span>
                </div>
            </div>

            <!-- Success State -->
            <div v-if="bookingSuccess" class="bg-green-50 border border-green-200 rounded-lg p-6 mb-6">
                <div class="text-center">
                    <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    <h2 class="text-2xl font-bold text-green-800 mb-2">Booking Confirmed!</h2>
                    <p class="text-green-700 mb-4">Your booking has been successfully created.</p>
                    <div class="bg-white rounded-lg p-4 border border-green-200">
                        <p class="text-sm text-gray-600">Booking ID:</p>
                        <p class="text-lg font-bold text-gray-900">{{ bookingId }}</p>
                    </div>
                    <button @click="goToBookings"
                        class="mt-4 px-6 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                        View My Bookings
                    </button>
                </div>
            </div>

            <!-- Booking Form -->
            <div v-if="!bookingSuccess" class="bg-white rounded-lg shadow-md overflow-hidden">
                <!-- Room Details Section -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Room Details</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Type</label>
                            <p class="text-lg text-gray-900">{{ roomType }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Room Number</label>
                            <p class="text-lg text-gray-900">{{ roomNumber }}</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                            <input v-model="bookingForm.checkIn" type="date" :min="today" @change="checkAvailability"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                            <input v-model="bookingForm.checkOut" type="date" :min="bookingForm.checkIn"
                                @change="checkAvailability"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                    </div>
                </div>

                <!-- Guest Information Section -->
                <div class="p-6 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Guest Information</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Number of Guests</label>
                            <select v-model="bookingForm.guests"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="1">1 Guest</option>
                                <option value="2">2 Guests</option>
                                <option value="3">3 Guests</option>
                                <option value="4">4 Guests</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Name</label>
                            <input v-model="bookingForm.name" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Email</label>
                            <input v-model="bookingForm.email" type="email"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Phone</label>
                            <input v-model="bookingForm.phone" type="text"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Mode Of Payment</label>
                            <select v-model="bookingForm.payment_method"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                required>
                                <option value="credit_card">Credit Card</option>
                                <option value="Debit_Card">Debit Card</option>
                                <option value="UPI">upi</option>
                                <option value="COD">COD</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Special Requests</label>
                            <textarea v-model="bookingForm.specialRequests" rows="3"
                                class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                                placeholder="Any special requests or notes..."></textarea>
                        </div>
                    </div>
                </div>

                <!-- Booking Summary Section -->
                <div class="p-6 bg-gray-50">
                    <h2 class="text-xl font-semibold text-gray-900 mb-4">Booking Summary</h2>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Room Rate (per night)</span>
                            <span class="text-gray-900">${{ pricePerNight }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Number of Nights</span>
                            <span class="text-gray-900">{{ calculatedNights }}</span>
                        </div>
                        <div class="flex justify-between font-semibold text-lg border-t pt-3">
                            <span class="text-gray-900">Total Amount</span>
                            <span class="text-blue-600">${{ totalAmount }}</span>
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="p-6 bg-white border-t border-gray-200">
                    <div class="flex gap-4">
                        <button @click="goBack"
                            class="flex-1 px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            Back to Room Selection
                        </button>
                        <button @click="confirmBooking" :disabled="!isFormValid || loading"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors disabled:opacity-50 disabled:cursor-not-allowed">
                            {{ loading ? 'Processing...' : 'Confirm Booking' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHotelStore } from '@/store/hotelStore'

const route = useRoute()
const router = useRouter()
const hotelStore = useHotelStore()

// Props from route
const hotelId = route.params.hotelId
const roomId = route.params.roomId || route.query.roomId

// Reactive data
const loading = ref(false)
const error = ref('')
const bookingSuccess = ref(false)
const bookingId = ref('')
const roomData = ref(null)
const availabilityData = ref(null)

// Get today's date in YYYY-MM-DD format
const today = new Date().toISOString().split('T')[0]

// Booking form data
const bookingForm = ref({
    checkIn: route.query.checkIn || '',
    checkOut: route.query.checkOut || '',
    guests: 1,
    payment_method: 'credit_card',
    name: '',        // Add this
    email: '',       // Add this
    phone: '',
    specialRequests: ''
})

// Computed properties
const roomType = computed(() => roomData.value?.room_type || '')
const roomNumber = computed(() => roomData.value?.room_number || '')
const pricePerNight = computed(() => parseFloat(roomData.value?.price_per_night) || 0)

const calculatedNights = computed(() => {
    if (!bookingForm.value.checkIn || !bookingForm.value.checkOut) return 0
    const checkIn = new Date(bookingForm.value.checkIn)
    const checkOut = new Date(bookingForm.value.checkOut)
    const diffTime = Math.abs(checkOut - checkIn)

    return Math.ceil(diffTime / (1000 * 60 * 60 * 24))
})

const totalAmount = computed(() => {
    return (calculatedNights.value * pricePerNight.value).toFixed(2)
})

const isFormValid = computed(() => {
    return bookingForm.value.checkIn &&
        bookingForm.value.checkOut &&
        bookingForm.value.guests > 0 &&
        bookingForm.value.payment_method &&
        bookingForm.value.name &&          // Add this
        bookingForm.value.email &&         // Add this
        bookingForm.value.phone &&
        new Date(bookingForm.value.checkOut) > new Date(bookingForm.value.checkIn)
})

// Methods
const loadRoomData = async () => {
    if (!roomId) return

    try {
        loading.value = true
        roomData.value = await hotelStore.getRoomById(roomId)


        // If we have check-in and check-out dates, check availability
        if (bookingForm.value.checkIn && bookingForm.value.checkOut) {
            await checkAvailability()
        }
    } catch (err) {
        error.value = 'Failed to load room details'
    } finally {
        loading.value = false
    }
}

const checkAvailability = async () => {
    if (!roomId || !bookingForm.value.checkIn || !bookingForm.value.checkOut) return

    try {
        const result = await hotelStore.checksingleRoomAvailability(
            roomId,
            bookingForm.value.checkIn,
            bookingForm.value.checkOut
        )

        availabilityData.value = result

        if (!result.success) {
            error.value = result.message || 'Room is not available for selected dates'
        } else {
            error.value = '' // Clear any previous errors
        }
    } catch (err) {
        error.value = 'Failed to check room availability'
    }
}

const confirmBooking = async () => {
    if (!isFormValid.value) {
        error.value = 'Please fill in all required fields correctly'
        return
    }

    loading.value = true
    error.value = ''

    try {
        // Check room availability first
        await checkAvailability()

        if (!availabilityData.value?.success) {
            error.value = availabilityData.value?.message || 'Room is not available for selected dates'
            return
        }

        // Create booking data
        const bookingData = {
            hotel_id: hotelId,
            room_id: roomId,
            check_in_date: bookingForm.value.checkIn,
            check_out_date: bookingForm.value.checkOut,
            currency: 'INR', // Assuming USD, can be dynamic based on hotel
            payment_method: bookingForm.value.payment_method,
            guests: bookingForm.value.guests,
            total_amount: totalAmount.value,
            guest_details: {                    // Add this object
                name: bookingForm.value.name,
                email: bookingForm.value.email,
                phone: bookingForm.value.phone
            },
            special_requests: bookingForm.value.specialRequests,
            status: 'pending'
        }

        // Create the booking
        const result = await hotelStore.createBooking(bookingData)

        if (result.success) {
            bookingSuccess.value = true
            bookingId.value = result.data.booking_id || result.data.id
        } else {
            error.value = result.message || 'Failed to create booking'
        }

    } catch (err) {
        error.value = err.response?.data?.message || 'Failed to process booking'
    } finally {
        loading.value = false
    }
}

const goBack = () => {
    router.go(-1)
}

const goToBookings = () => {
    // router.push({ name: 'MyBookings' })
    router.push({
        name: 'MyBookings',
        params: { bookingId: bookingId.value },
        
    })
}

onMounted(async () => {
    // Validate required data
    if (!hotelId || !roomId) {
        error.value = 'Missing booking information. Please select a room again.'
        return
    }

    // Load room data on component mount
    await loadRoomData()
})
</script>