<template>
    <div class="max-w-4xl mx-auto p-6 bg-white rounded-lg shadow-lg">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Book Your Room</h2>
            <p class="text-gray-600">Select your dates and room type to proceed with booking</p>
        </div>
        <!-- Date Selection Section -->
    <div class="mb-8">
        <h3 class="text-lg font-semibold text-gray-700 mb-4">Select Dates</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
          <!-- Check-in Date -->
        <div>
            <label class="block text-sm font-medium text-gray-700 mb-2">
            Check-in Date
          </label>
          <input
            type="date"
            
            :min="minDate"
            class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-blue-500"
            
            @change="onDateChange"
          />
         
        </div>
      </div>
    </div>
    </div>
</template>
<script setup>
import { ref, computed, onMounted } from 'vue';
import { useHotelStore } from '@/store/hotelStore';
import { useRoute } from 'vue-router';
const route = useRoute()
const bookingStore = useHotelStore()
// Props
const props = defineProps({
  hotelId: {
    type: String,
    required: true
  }
})
const checkIn = route.params.checkIn
console.log('Check-in date from route:', checkIn);

// Reactive references
const {
  checkInDate,
  checkOutDate,
  guestCount,
  availableRooms,
  selectedRoom,
  errors,
  availabilityLoading,
  nights,
  totalPrice,
  isFormValid
} = bookingStore
const minDate=computed(()=>{
    const today=new Date();
    return today.toISOString().split('T')[0]
})
const minCheckoutDate= computed(()=>{
    if(!checkInDate.value) return minDate.value
    const checkIn= new Date(checkInDate.value)
    checkIn.setDate(checkIn.getDate()+1)
    return checkIn.toISOString().split('T')[0]

})
const canCheckAvailability = computed (()=>{
    return checkInDate.value && checkOutDate.value && guestCount.value > 0
})

const onDateChange=()=>{
    bookingStore.clearErrors()
    if(availableRooms.value.length>0){
        availableRooms.value=[]
        selectedRoom.value=null
    }
}
const onGuestCountChange = () => {
  if (availableRooms.value.length > 0) {
    // Clear previous results when guest count changes
    availableRooms.value = []
    selectedRoom.value = null
  }
}
const handleCheckAvailability = async () => {
  await bookingStore.checkAvailability(props.hotelId)
}
const selectRoom = (room) => {
  bookingStore.selectRoom(room)
}
const formatDate = (dateString) => {
  if (!dateString) return ''
  const date = new Date(dateString)
  return date.toLocaleDateString('en-US', {
    weekday: 'long',
    year: 'numeric',
    month: 'long',
    day: 'numeric'
  })
}
const proceedToBooking = () => {
  // Emit event or navigate to booking confirmation
  console.log('Proceeding to booking with data:', bookingStore.bookingData)
  // You can emit an event or use router.push here
}

onMounted(() => {
  // Get query parameters and pre-populate form
  const { checkIn, checkOut, guests } = route.query
 
  
  
  if (checkIn) checkInDate.value = checkIn
  if (checkOut) checkOutDate.value = checkOut  
  if (guests) guestCount.value = parseInt(guests)
  
  // Auto-check availability if all params are present
  if (checkIn && checkOut && guests) {
    handleCheckAvailability()
  }
  
  bookingStore.resetBooking()
})
</script>