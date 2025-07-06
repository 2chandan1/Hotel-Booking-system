<template>
  <div class="bg-white rounded-lg shadow-lg p-6 mt-8">
    <h3 class="text-xl font-semibold text-gray-900 mb-6">Available Rooms</h3>
    
    <!-- Date Selection for Room Availability -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6 p-4 bg-gray-50 rounded-lg">
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Check-in Date</label>
        <input
          type="date"
          v-model="checkInDate"
          :min="minDate"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>
      <div>
        <label class="block text-sm font-medium text-gray-700 mb-2">Check-out Date</label>
        <input
          type="date"
          v-model="checkOutDate"
          :min="checkInDate || minDate"
          class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        />
      </div>
      <div class="flex items-end">
        <button
          @click="checkAvailability"
          :disabled="!checkInDate || !checkOutDate || loading"
          class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <span v-if="loading">Checking...</span>
          <span v-else>Check Availability</span>
        </button>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-8">
      <svg class="animate-spin h-8 w-8 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="text-gray-600">Checking room availability...</p>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 border border-red-200 rounded-lg p-4 mb-6">
      <div class="flex items-center">
        <svg class="h-5 w-5 text-red-400 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.96-.833-2.73 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"/>
        </svg>
        <p class="text-red-800">{{ error }}</p>
      </div>
    </div>

    <!-- No Rooms Available -->
    <div v-else-if="searchAttempted && availableRooms.available_rooms_count === 0" class="text-center py-8">
      <svg class="h-16 w-16 text-gray-400 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"/>
      </svg>
      <h4 class="text-lg font-medium text-gray-900 mb-2">No Rooms Available</h4>
      <p class="text-gray-600">Sorry, no rooms are available for the selected dates. Please try different dates.</p>
    </div>

    <!-- Available Rooms List -->
    
    <div v-else-if="availableRooms.available_rooms_count > 0" class="space-y-6">
      <div class="mb-4">
        <p class="text-sm text-gray-600">
          Showing {{ availableRooms.length }} available room{{ availableRooms.length !== 1 ? 's' : '' }} 
          for {{ formatDate(checkInDate) }} to {{ formatDate(checkOutDate) }}
          ({{ totalNights }} night{{ totalNights !== 1 ? 's' : '' }})
        </p>
      </div>

      <div 
        v-for="room in availableRooms.available_rooms" 
        :key="room.id"
        class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-md transition-shadow"
      >
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 p-6">
          <!-- Room Image -->
          <div class="lg:col-span-1">
            <div class="relative h-48 bg-gray-200 rounded-lg overflow-hidden">
              <img
                v-if="room.images && room.images.length > 0"
                :src="room.images[0]"
                :alt="`${room.room_type} room ${room.room_number}`"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
                <svg class="h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"/>
                </svg>
              </div>
              
              <!-- Room Number Badge -->
              <div class="absolute top-3 left-3 bg-blue-600 text-white px-2 py-1 rounded-md text-sm font-medium">
                Room {{ room.room_number }}
              </div>
            </div>
          </div>

          <!-- Room Details -->
          <div class="lg:col-span-1 space-y-4">
            <div>
              <h4 class="text-lg font-semibold text-gray-900 capitalize mb-1">
                {{ room.room_type.replace('_', ' ') }} Room
              </h4>
              <p class="text-gray-600 text-sm">{{ room.description }}</p>
            </div>

            <!-- Room Specifications -->
            <div class="space-y-2">
              <div class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span>{{ room.capacity }} guests</span>
              </div>
              
              <div class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2v0a2 2 0 002-2h6l2 2h6a2 2 0 012 2v0a2 2 0 00-2 2z"/>
                </svg>
                <span>{{ room.beds }} {{ room.bed_type }} bed</span>
              </div>
              
              <div class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                </svg>
                <span>{{ room.size_sqm }} sqm</span>
              </div>
              
              <div class="flex items-center text-sm text-gray-600">
                <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"/>
                </svg>
                <span>Floor {{ room.floor }} • {{ room.view_type }} view</span>
              </div>
            </div>

            <!-- Room Amenities -->
            <div v-if="room.amenities && room.amenities.length > 0">
              <h5 class="text-sm font-medium text-gray-700 mb-2">Room Amenities:</h5>
              <div class="flex flex-wrap gap-1">
                <span
                  v-for="amenity in room.amenities.slice(0, 4)"
                  :key="amenity"
                  class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full"
                >
                  {{ amenity.replace('_', ' ') }}
                </span>
                <span
                  v-if="room.amenities.length > 4"
                  class="text-xs bg-gray-100 text-gray-600 px-2 py-1 rounded-full"
                >
                  +{{ room.amenities.length - 4 }} more
                </span>
              </div>
            </div>

            <!-- Special Features -->
            <div v-if="room.special_features && room.special_features.length > 0">
              <div class="flex flex-wrap gap-1 mt-2">
                <span
                  v-for="feature in room.special_features"
                  :key="feature"
                  class="text-xs bg-green-100 text-green-800 px-2 py-1 rounded-full"
                >
                  {{ feature.replace('_', ' ') }}
                </span>
              </div>
            </div>
          </div>

          <!-- Pricing and Booking -->
          <div class="lg:col-span-1 flex flex-col justify-between">
            <div class="space-y-4">
              <!-- Price -->
              <div class="text-right">
                <div class="text-2xl font-bold text-blue-600">
                  ${{ room.price_per_night }}
                </div>
                <div class="text-sm text-gray-500">per night</div>
              </div>

              <!-- Total Price Breakdown -->
              <div class="bg-gray-50 rounded-lg p-4 space-y-2">
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">${{ room.price_per_night }} × {{ totalNights }} nights</span>
                  <span class="text-gray-900">${{ (parseFloat(room.price_per_night) * totalNights).toFixed(2) }}</span>
                </div>
                <div class="flex justify-between text-sm">
                  <span class="text-gray-600">Service fee</span>
                  <span class="text-gray-900">${{ (parseFloat(room.price_per_night) * totalNights * 0.1).toFixed(2) }}</span>
                </div>
                <div class="border-t pt-2">
                  <div class="flex justify-between font-semibold">
                    <span>Total</span>
                    <span>${{ (parseFloat(room.price_per_night) * totalNights * 1.1).toFixed(2) }}</span>
                  </div>
                </div>
              </div>

              <!-- Accessibility & Features -->
              <div class="space-y-2">
                <div v-if="room.accessibility_features && room.accessibility_features.length > 0" class="flex items-center text-sm text-green-600">
                  <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  <span>Wheelchair accessible</span>
                </div>
                
                <div v-if="!room.smoking_allowed" class="flex items-center text-sm text-green-600">
                  <svg class="h-4 w-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728L5.636 5.636m12.728 12.728L5.636 5.636"/>
                  </svg>
                  <span>Non-smoking</span>
                </div>
              </div>
            </div>

            <!-- Booking Button -->
            <div class="mt-6 space-y-3">
              <button
                @click="bookRoom(room)"
                class="w-full px-4 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors font-medium"
              >
                Book This Room
              </button>
              
              <button
                @click="viewRoomDetails(room)"
                class="w-full px-4 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors"
              >
                View Details
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Pagination -->
    <div v-if="pagination && pagination.last_page > 1" class="mt-8 flex justify-center">
      <nav class="flex items-center space-x-2">
        <button
          v-for="page in paginationPages"
          :key="page"
          @click="changePage(page)"
          :class="[
            'px-3 py-2 rounded-md text-sm font-medium transition-colors',
            page === pagination.current_page
              ? 'bg-blue-600 text-white'
              : 'bg-white text-gray-700 hover:bg-gray-50 border border-gray-300'
          ]"
        >
          {{ page }}
        </button>
      </nav>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useHotelStore } from '@/store/hotelStore'

const props = defineProps({
  hotelId: {
    type: String,
    required: true
  }
})

const router = useRouter()
const hotelStore = useHotelStore()

// Reactive data
const availableRooms = ref([])
const loading = ref(false)
const error = ref(null)
const searchAttempted = ref(false)
const checkInDate = ref('')
const checkOutDate = ref('')
const pagination = ref(null)
const currentPage = ref(1)

// Computed properties
const minDate = computed(() => {
  return new Date().toISOString().split('T')[0]
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

const paginationPages = computed(() => {
  if (!pagination.value) return []
  
  const pages = []
  const current = pagination.value.current_page
  const last = pagination.value.last_page
  
  // Show first page
  if (current > 3) pages.push(1)
  if (current > 4) pages.push('...')
  
  // Show pages around current
  for (let i = Math.max(1, current - 2); i <= Math.min(last, current + 2); i++) {
    pages.push(i)
  }
  
  // Show last page
  if (current < last - 3) pages.push('...')
  if (current < last - 2) pages.push(last)
  
  return pages
})

// Methods
const checkAvailability = async () => {
  if (!checkInDate.value || !checkOutDate.value) {
    error.value = 'Please select both check-in and check-out dates'
    return
  }

  loading.value = true
  error.value = null
  searchAttempted.value = true

  try {
    const response = await hotelStore.checkRoomAvailability(props.hotelId, {
      check_in_date: checkInDate.value,
      check_out_date: checkOutDate.value,
      page: currentPage.value
    })
console.log('Response from checkAvailability:', response);

    availableRooms.value = response.data || []
    console.log('Available Rooms:', availableRooms.value);
    
    pagination.value = response.pagination || null
  } catch (err) {
    error.value = err.message || 'Failed to check room availability'
    availableRooms.value = []
  } finally {
    loading.value = false
  }
}

const bookRoom = (room) => {
  console.log('Booking room:', room.room_id);
  
  // Navigate to booking page with room details
  router.push({
    name: 'BookingPage',
    params: { hotelId: props.hotelId, roomId: room.room_id },
    query: {
      roomId: room.room_id,
      checkIn: checkInDate.value,
      checkOut: checkOutDate.value,
    }
  })
}

const viewRoomDetails = (room) => {
  // Navigate to room details page or show modal
  router.push({
    name: 'RoomDetail',
    params: { hotelId: props.hotelId, roomId: room.id }
  })
}

const changePage = (page) => {
  if (page === '...' || page === pagination.value.current_page) return
  
  currentPage.value = page
  checkAvailability()
}

const formatDate = (dateString) => {
  return new Date(dateString).toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  })
}

// Set default dates (today and tomorrow)
onMounted(() => {
  const today = new Date()
  const tomorrow = new Date(today)
  tomorrow.setDate(tomorrow.getDate() + 1)
  
  checkInDate.value = today.toISOString().split('T')[0]
  checkOutDate.value = tomorrow.toISOString().split('T')[0]
})
</script>

<style scoped>
/* Custom styles for better visual hierarchy */
.room-card {
  transition: all 0.3s ease;
}

.room-card:hover {
  transform: translateY(-2px);
}

/* Responsive image aspect ratio */
.room-image {
  aspect-ratio: 16 / 9;
  object-fit: cover;
}

/* Custom scrollbar for amenities */
.amenities-scroll::-webkit-scrollbar {
  height: 4px;
}

.amenities-scroll::-webkit-scrollbar-track {
  background: #f1f1f1;
}

.amenities-scroll::-webkit-scrollbar-thumb {
  background: #888;
  border-radius: 2px;
}
</style>