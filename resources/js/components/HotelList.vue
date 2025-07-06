<template>
  <div class="mt-8">
    <!-- Results Header -->
    <div class="flex justify-between items-center mb-6" v-if="hotels.length > 0 || loading">
      <h3 class="text-xl font-semibold text-gray-800">
        {{ loading ? 'Searching...' : `Found ${hotels.length} hotel${hotels.length !== 1 ? 's' : ''}` }}
      </h3>
      
      <div class="flex items-center space-x-4" v-if="!loading && hotels.length > 0">
        <label class="text-sm font-medium text-gray-700">Sort by:</label>
        <select
          v-model="sortBy"
          @change="handleSort"
          class="px-3 py-1 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent"
        >
          <option value="name">Name</option>
          <option value="price">Price (Low to High)</option>
          <option value="rating">Rating (High to Low)</option>
          <option value="location">Location</option>
        </select>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="text-center py-12">
      <svg class="animate-spin h-12 w-12 text-blue-600 mx-auto mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
      </svg>
      <p class="text-gray-600">Searching for hotels...</p>
    </div>

    <!-- No Results -->
    <div v-else-if="searchAttempted && hotels.length === 0" class="text-center py-12">
      <div class="text-gray-400 mb-4">
        <svg class="h-16 w-16 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"/>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">No hotels found</h3>
      <p class="text-gray-600 mb-4">Try adjusting your search criteria or location</p>
      <button
        @click="clearFilters"
        class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
      >
        Clear Filters
      </button>
    </div>

    <!-- Hotel Results Grid -->
    <div v-else-if="hotels.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
      <div
        v-for="hotel in sortedHotels"
        :key="hotel.id"
        class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-shadow duration-300"
      >
        <!-- Hotel Image -->
        <div class="relative h-48 bg-gray-200">
          <img
            v-if="hotel.images"
            :src="hotel.images"
            :alt="hotel.name"
            class="w-full h-full object-cover"
          />
          <div v-else class="w-full h-full flex items-center justify-center bg-gray-100">
            <svg class="h-16 w-16 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-4m-5 0H3m2 0h4M9 7h6m-6 4h6m-6 4h6"/>
            </svg>
          </div>
          
          <!-- Rating Badge -->
          <div class="absolute top-3 right-3 bg-white rounded-full px-2 py-1 text-sm font-medium shadow-sm">
            <span class="text-yellow-500">★</span>
            {{ hotel.rating || 'N/A' }}
          </div>
        </div>

        <!-- Hotel Info -->
        <div class="p-4">
          <div class="flex justify-between items-start mb-2">
            <h4 class="text-lg font-semibold text-gray-900 line-clamp-1">{{ hotel.name }}</h4>
            <div class="text-right">
              <p class="text-2xl font-bold text-blue-600">₹{{ hotel.base_price }}</p>
              <p class="text-sm text-gray-500">per night</p>
            </div>
          </div>

          <div class="flex items-center text-gray-600 mb-3">
            <svg class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <span class="text-sm">{{ hotel.location }}</span>
          </div>

          <!-- Amenities -->
          <div v-if="hotel.amenities && hotel.amenities.length > 0" class="mb-3">
            <div class="flex flex-wrap gap-1">
              <span
                v-for="amenity in hotel.amenities.slice(0, 3)"
                :key="amenity"
                class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full"
              >
                {{ amenity }}
              </span>
              <span
                v-if="hotel.amenities.length > 3"
                class="text-xs bg-gray-100 text-gray-700 px-2 py-1 rounded-full"
              >
                +{{ hotel.amenities.length - 3 }} more
              </span>
            </div>
          </div>

          <!-- Description -->
          <p v-if="hotel.description" class="text-gray-600 text-sm mb-4 line-clamp-2">
            {{ hotel.description }}
          </p>

          <!-- Action Buttons -->
          <div class="flex space-x-2">
            <button
              @click="viewDetails(hotel)"
              class="flex-1 px-4 py-2 border border-blue-600 text-blue-600 rounded-md hover:bg-blue-50 transition-colors"
            >
              View Details
            </button>
            <button
              @click="bookNow(hotel)"
              class="flex-1 px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors"
            >
              Book Now
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- Load More Button -->
    <div v-if="hotels.length > 0 && hasMore" class="text-center mt-8">
      <button
        @click="loadMore"
        :disabled="loadingMore"
        class="px-6 py-3 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 disabled:opacity-50 transition-colors"
      >
        <span v-if="loadingMore">Loading...</span>
        <span v-else>Load More Hotels</span>
      </button>
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue'
import { useHotelStore } from '@/store/hotelStore'
import { useRouter } from 'vue-router'
const router = useRouter()
const hotelStore = useHotelStore()

// Reactive data
const sortBy = ref('name')

// Computed properties
const hotels = computed(() => hotelStore.hotels || [])


const loading = computed(() => hotelStore.loading)
const loadingMore = computed(() => hotelStore.loadingMore)
const hasMore = computed(() => hotelStore.hasMore)
const searchAttempted = computed(() => hotelStore.searchAttempted)

// Sorted hotels
const sortedHotels = computed(() => {
  const hotelsCopy = [...hotels.value]
  
  switch (sortBy.value) {
    case 'price':
      return hotelsCopy.sort((a, b) => (a.price || 0) - (b.price || 0))
    case 'rating':
      return hotelsCopy.sort((a, b) => (b.rating || 0) - (a.rating || 0))
    case 'location':
      return hotelsCopy.sort((a, b) => (a.location || '').localeCompare(b.location || ''))
    case 'name':
    default:
      return hotelsCopy.sort((a, b) => (a.name || '').localeCompare(b.name || ''))
  }
})

// Methods
const handleSort = () => {
  // Sorting is handled by the computed property
}

const viewDetails = (hotel) => {
  // Emit event or navigate to hotel details

  console.log('View details for:', hotel.id)
  console.log('View details for:', hotel)
  router.push({name:'HotelDetail',

  params:{id:hotel.id}
  })
  // You can emit an event or use router to navigate
  // emit('view-details', hotel)
}

const bookNow = (hotel) => {
  // Emit event or navigate to booking
  console.log('Book now for:', hotel.name)
  console.log('Book now for:', hotel.name)
  // You can emit an event or use router to navigate
  // emit('book-now', hotel)
}

const clearFilters = () => {
  hotelStore.clearFilters()
}

const loadMore = () => {
  hotelStore.loadMoreHotels()
}
</script>

<style scoped>
.line-clamp-1 {
  display: -webkit-box;
  -webkit-line-clamp: 1;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}
</style>