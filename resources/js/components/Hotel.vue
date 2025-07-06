<template>
     <div class="container mx-auto px-4 py-8">
    <div class="bg-white shadow-lg rounded-lg p-6 mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-6">Search Hotels</h2>
        <form @submit.prevent="handleSearch" class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="col-span-1 md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Location
                    </label>
                    <input v-model="searchFilters.location" type="text"
                        placeholder="Enter city, hotel name, or landmark"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                </div>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">
                        Min Rating
                    </label>
                    <select v-model.number="searchFilters.rating"
                        class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                        <option :value="0">Any Rating</option>
                        <option :value="1">1+ Stars</option>
                        <option :value="2">2+ Stars</option>
                        <option :value="3">3+ Stars</option>
                        <option :value="4">4+ Stars</option>
                        <option :value="5">5 Stars</option>
                    </select>
                </div>
            </div>
             <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                 <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Min Price (₹)
                        </label>
                        <input v-model.number="searchFilters.minPrice" type="number" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>

                    <!-- Max Price -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Max Price (₹)
                        </label>
                        <input v-model.number="searchFilters.maxPrice" type="number" min="0"
                            class="w-full px-4 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent" />
                    </div>
             </div>
            <div class="flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0 sm:space-x-4">
                <button type="button" @click="clearFilters"
                    class="w-full sm:w-auto px-6 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-50 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    Clear Filters
                </button>
                <button type="submit" :disabled="loading"
                    class="w-full sm:w-auto px-8 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 disabled:bg-blue-300 focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors">
                    <span v-if="loading" class="flex items-center justify-center">
                        <svg class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg"
                            fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4">
                            </circle>
                            <path class="opacity-75" fill="currentColor"
                                d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                            </path>
                        </svg>
                        Searching...
                    </span>
                    <span v-else>Search Hotels</span>
                </button>
            </div>
        </form>
    </div>
    <HotelList />
    </div>
</template>

<script setup>
import { computed, reactive, watch, onMounted } from 'vue'
import { useHotelStore } from '@/store/hotelStore'
import HotelList from '@/components/HotelList.vue'
const hotelStore = useHotelStore()

// Reactive search filters
const searchFilters = reactive({
    name: '',
    location: '',
    checkIn: '',
    checkOut: '',
    guests: 1,
    minPrice: 0,
    maxPrice: 1000,
    amenities: [],
    rating: 0
})


// Computed properties
const today = computed(() => {
    return new Date().toISOString().split('T')[0]
})

const minCheckoutDate = computed(() => {
    if (!searchFilters.checkIn) return today.value
    const checkIn = new Date(searchFilters.checkIn)
    checkIn.setDate(checkIn.getDate() + 1)
    return checkIn.toISOString().split('T')[0]
})

const loading = computed(() => hotelStore.loading)

// Watch for check-in date changes to validate check-out date
watch(() => searchFilters.checkIn, (newCheckIn) => {
    if (newCheckIn && searchFilters.checkOut) {
        const checkIn = new Date(newCheckIn)
        const checkOut = new Date(searchFilters.checkOut)

        if (checkOut <= checkIn) {
            const nextDay = new Date(checkIn)
            nextDay.setDate(nextDay.getDate() + 1)
            searchFilters.checkOut = nextDay.toISOString().split('T')[0]
        }
    }
})

// Methods
const handleSearch = () => {
    hotelStore.updateSearchFilters(searchFilters)
    hotelStore.searchHotels(true)
}

const clearFilters = () => {
    Object.assign(searchFilters, {
        name: '',
        location: '',
        checkIn: '',
        checkOut: '',
        guests: 1,
        minPrice: 0,
        maxPrice: 1000,
        amenities: [],
        rating: 0
    })
    hotelStore.clearFilters()
}
onMounted(()=>{
    hotelStore.loadAllHotels();
    Object.assign(searchFilters, hotelStore.searchFilters)
})
</script>