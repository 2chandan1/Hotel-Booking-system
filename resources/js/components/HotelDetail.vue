<template>
    <div class="min-h-screen bg-gray-50">
        <!-- loading state/ -->
        <div v-if="loading" class="flex justify-center items-center h-64">
            <div class="text-center">
                <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg"
                    fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor"
                        d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                    </path>
                </svg>
                <p class="text-gray-600">Loading hotel details...</p>
            </div>
        </div>
        <!-- Error state -->
        <div v-else-if="error" class="max--w-4xl mx-auto px-4 py-8">
            <div class="bg-red-50 border border-red-200 rounded-lg p-6 text-center  ">
                <svg class="h-12 w-12 text-red-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.96-.833-2.73 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                </svg>
                <h3 class="text-lg font-medium text-red-800 mb-2">Error Loading Hotel</h3>
                <p>{{ error }}</p>
                <button @click="fetchHotelDetails"
                    class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 transition-colors">
                    Try Again
                </button>
            </div>
        </div>
        <!-- Hotel Details -->

        <div v-else-if="hotel" class="max-w-7xl mx-auto px-4 py-8">
            <div class="mb-6">
                <button @click="goBack" class="flex items-center text-gray-600 hover:text-gray-800 transition-colors">
                    <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Back to Search Results
                </button>
            </div>

            <!-- Hotel Header  -->
            <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-8">
                <!-- Image Gallery -->
                <div class="relative h-96 bg-gray-200">
                    <img v-if="hotel.images && hotel.images.length > 0" :alt="hotel.name" :src="currentImage"
                        class="w-full h-full object-cover" />
                    <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                        <svg class="h-24 w-24 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6" />
                        </svg>
                    </div>
                    <!-- Image Navigation -->
                    <div v-if="hotel.images && hotel.images.length > 1" class="absolute bottom-4 left-4 right-4">
                        <div class="flex space-x-2 justify-center">
                            <button v-for="(image, index) in hotel.images" :key="index"
                                @click="currentImageIndex = index" class="w-3 h-3 rounded-full transition-colors"
                                :class="currentImageIndex === index ? 'bg-white' : 'bg-white/50'"></button>
                        </div>
                    </div>
                    <!-- Rating Badge -->
                    <div class="absolute top-4 right-4 bg-white rounded-full px-3 py-2 shadow-lg">
                        <div class="flex items-center">
                            <span class="text-yellow-500 mr-1">★</span>
                            <span class="font-medium">{{ hotel.rating || 'N/A' }}</span>
                        </div>
                    </div>
                </div>
                <!-- Hotel Info -->
                <div class="p-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-900 mb-2">{{ hotel.name }}</h1>
                            <div class="flex items-center text-gray-600">
                                <svg class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>{{ hotel.location?.address }}, </span>
                                <span>{{ hotel.location?.city }}, </span>
                                <span>{{ hotel.location?.state }}, </span>
                                <span>{{ hotel.location?.country }}, </span>
                                <span>{{ hotel.location?.postal_code }}, </span>
                            </div>
                        </div>
                        <div class="text-right">
                            <p class="text-4xl font-bold text-blue-600">₹{{ hotel.base_price || hotel.price }}</p>
                            <p class="text-gray-500">per night</p>
                        </div>
                    </div>
                    <!-- Description -->
                    <div v-if="hotel.description" class="mb-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-3">About This Hotel</h3>
                        <p class="text-gray-700 leading-relaxed">{{ hotel.description }}</p>

                    </div>
                    <!-- Quick Actions -->
                    <div class="flex space-x-4 mb-6">
                        <button @click="bookNow"
                            class="flex-1 px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium">
                            Book Now
                        </button>
                        <button @click="shareHotel"
                            class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            <!-- Booking Details -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Main Content -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Amenities -->
                    <div v-if="hotel.amenities && hotel.amenities.length > 0" class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Hotel Amenities</h3>
                        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
                            <div v-for="amenity in hotel.amenities" :key="amenity"
                                class="flex item-center p-3 bg-gray-50 rounded-lg ">
                                <svg class="h-5 w-5 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 13l4 4L19 7" />
                                </svg>
                                <span class="text-gray-700">{{ amenity }}</span>
                            </div>
                        </div>
                    </div>
                    <!-- Hotel Policies -->

                    <div v-if="hotel.policies" class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Hotel Policies</h3>
                        <div class="space-y-4">
                            <div v-if="hotel.policies.check_in_time" class="flex items-start">
                                <svg class="h-5 w-5 text-green-600 mr-3 mt-1" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">Check-in Time</p>
                                    <p class="text-gray-600">{{ hotel.policies.check_in_time }}</p>
                                </div>
                            </div>
                            <div v-if="hotel.policies.check_out_time" class="flex items-start">
                                <svg class="h-5 w-5 text-red-600 mr-3 mt-1" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">Check-out Time</p>
                                    <p class="text-gray-600">{{ hotel.policies.check_out_time }}</p>
                                </div>
                            </div>
                            <div v-if="hotel.policies.cancellation_policy" class="flex items-start">
                                <svg class="h-5 w-5 text-yellow-600 mr-3 mt-1" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.96-.833-2.73 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div>
                                    <p class="font-medium text-gray-900">Cancellation Policy</p>
                                    <p class="text-gray-600">{{ hotel.policies.cancellation_policy }}</p>
                                    <p class="text-gray-600">{{ hotel.policies.pet_policy }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Contact Information -->
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Contact Information</h3>
                        <div class="space-y-3">
                            <div v-if="hotel.phone" class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span class="text-gray-700">{{ hotel.phone }}</span>
                            </div>
                            <div v-if="hotel.email" class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span class="text-gray-700">{{ hotel.email }}</span>
                            </div>
                            <div v-if="hotel.website" class="flex items-center">
                                <svg class="h-5 w-5 text-blue-600 mr-3" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9v-9m0-9v9m0 9c-5 0-9-4-9-9s4-9 9-9" />
                                </svg>
                                <a :href="hotel.website" target="_blank" class="text-blue-600 hover:underline">
                                    {{ hotel.website }}
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Sidebar -->
                <div class="space-y-8">
                    <!-- Booking Card -->
                    <div class="bg-white rounded-lg shadow-lg p-6 sticky top-8">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Book Your Stay</h3>
                        <!-- Date Selection -->
                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
                                <input type="date" v-model="checkInDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
                                <input type="date" v-model="checkOutDate"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Guests</label>
                                <select v-model="guests"
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                                    <option value="1">1 Guest</option>
                                    <option value="2">2 Guests</option>
                                    <option value="3">3 Guests</option>
                                    <option value="4">4 Guests</option>
                                    <option value="5">5+ Guests</option>
                                </select>
                            </div>
                        </div>
                        <!-- Price Breakdown -->

                        <div v-if="totalNights > 0" class="border-t pt-4 mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">₹{{ hotel.base_price || hotel.price }} × {{ totalNights }}
                                    nights</span>
                                <span class="text-gray-900">₹{{ baseTotal }}</span>
                            </div>
                            <div class="flex justify-between items-center mb-2">
                                <span class="text-gray-600">Service fee</span>
                                <span class="text-gray-900">₹{{ serviceFee }}</span>
                            </div>
                            <div class="flex justify-between items-center text-lg font-semibold border-t pt-2">
                                <span>Total</span>
                                <span>₹{{ totalPrice }}</span>
                            </div>
                        </div>
                        <button @click="proceedToBooking" :disabled="!checkInDate || !checkOutDate"
                            class="w-full px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium">
                            Reserv Now
                        </button>
                    </div>
                    <div class="bg-white rounded-lg shadow-lg p-6">
                        <h3 class="text-xl font-semibold text-gray-900 mb-4">Quick Info</h3>
                        <div class="space-y-3">
                            <div class="flex justify-between">
                                <span class="text-gray-600">Property Type</span>
                                <span class="font-medium">{{ hotel.property_type || 'Hotel' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Rating</span>
                                <span class="font-medium">{{ hotel.rating || 'N/A' }} / 5</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-600">Rooms Available</span>
                                <span class="font-medium">{{ hotel.total_rooms || 'Contact Hotel' }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Room Availability -->
            <div class="mt-8">
                <RoomAvailability :hotelId="hotel.id" />
            </div>
        </div>
    </div>
</template>


<script setup>
import { ref, computed, onMounted, watch } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useHotelStore } from '@/store/hotelStore'
import RoomAvailability from './RoomAvailability.vue'
const route = useRoute();
const router = useRouter();
const hotelStore = useHotelStore();
// Reactive data
const hotel = ref(null)
const loading = ref(false)
const error = ref(null)
const currentImageIndex = ref(0)
const checkInDate = ref('')
const checkOutDate = ref('')
const guests = ref('2')

// coumputed properties 
const currentImage = computed(() => {
    if (hotel.value?.images && hotel.value.images.length > 0) {
        return hotel.value.images[currentImageIndex.value]
    }
    return null
})
const totalNights = computed(() => {
    if (checkInDate.value && checkOutDate.value) {
        const checkIn = new Date(checkInDate.value)
        const checkOut = new Date(checkOutDate.value)
        const timeDiff = checkOut.getTime() - checkIn.getTime()
        const nights = Math.ceil(timeDiff / (1000 * 3600 * 24))
        return nights > 0 ? nights : 0
    }
    return 0
})
const baseTotal = computed(() => {
    return (hotel.value?.price || 0) * totalNights.value
})
const serviceFee = computed(() => {
    return Math.round(baseTotal.value * 0.1) // 10% service fee
})
const totalPrice = computed(() => {
    return baseTotal.value + serviceFee.value
})

// Methods 
const fetchHotelDetails = async () => {
    const hotelId = route.params.id
    if (!hotelId) {
        error.value = 'Hotel ID not found'
        return
    }

    loading.value = true
    error.value = null
    try {
        const response = await hotelStore.getHotelById(hotelId)
        hotel.value = response

    } catch (error) {
        error.value = error.message || 'Failed to fetch hotel details';
    } finally {
        loading.value = false;
    }
}
const goBack = () => {
    router.go(-1)
}
const bookNow = () => {
    router.push({
        name: 'BookingPage',
        params: { hotelId: hotel.value.id },
        query: {
            checkIn: checkInDate.value,
            checkOut: checkOutDate.value,
            guests: guests.value
        }
    })
}
const proceedToBooking = () => {
    if (!checkInDate.value || !checkOutDate.value) {
        alert('Please select check-in and check-out dates');
        return;
    }
    bookNow();
}
const addToFavorites = () => {
    console.log('Adding to favorites:', hotel.value.id)
}
const shareHotel = () => {
    // Share hotel functionality
    if (navigator.share) {
        navigator.share({
            title: hotel.value.name,
            text: `Check out this amazing hotel: ${hotel.value.name}`,
            url: window.location.href
        })
    } else {
        // Fallback: copy to clipboard
        navigator.clipboard.writeText(window.location.href)
        alert('Hotel link copied to clipboard!')
    }
}

// Set minimum date to today
const setMinDate = () => {
    const today = new Date().toISOString().split('T')[0]
    const checkInInput = document.querySelector('input[type="date"]')
    if (checkInInput) {
        checkInInput.min = today
    }
}
//watch for checkin date changes
// Watch for check-in date changes to set minimum check-out date
watch(checkInDate, (newDate) => {
    if (newDate) {
        const checkOutInput = document.querySelectorAll('input[type="date"]')[1]
        if (checkOutInput) {
            checkOutInput.min = newDate
        }
    }
})
// Lifecycle
onMounted(() => {
    fetchHotelDetails()
    setMinDate()
})

</script>

<style scoped>
.sticky {
    position: sticky;
}

/* Custom scrollbar for image gallery */
.overflow-x-auto::-webkit-scrollbar {
    height: 4px;
}

.overflow-x-auto::-webkit-scrollbar-track {
    background: #f1f1f1;
}

.overflow-x-auto::-webkit-scrollbar-thumb {
    background: #888;
    border-radius: 2px;
}

.overflow-x-auto::-webkit-scrollbar-thumb:hover {
    background: #555;
}
</style>