<template>
  <div class="min-h-screen bg-gradient-to-br from-blue-50 via-white to-purple-50 py-8 px-4">
    <div class="max-w-4xl mx-auto">
      <!-- Header -->
      <div class="text-center mb-8">
        <h1 class="text-4xl font-bold text-gray-800 mb-2">Booking Details</h1>
        <p class="text-gray-600">Manage your reservation</p>
      </div>

      <!-- Loading State -->
      <div v-if="loading" class="flex justify-center items-center py-20">
        <div class="relative">
          <div class="w-16 h-16 border-4 border-blue-200 border-t-blue-600 rounded-full animate-spin"></div>
          <div class="absolute inset-0 flex items-center justify-center">
            <div class="w-8 h-8 bg-blue-600 rounded-full animate-pulse"></div>
          </div>
        </div>
        <span class="ml-4 text-lg text-gray-600">Loading booking details...</span>
      </div>

      <!-- Error State -->
      <div v-else-if="error" class="bg-red-50 border-l-4 border-red-500 p-6 rounded-lg shadow-sm">
        <div class="flex items-center">
          <svg class="w-6 h-6 text-red-500 mr-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
          <p class="text-red-700 font-medium">{{ error }}</p>
        </div>
      </div>

      <!-- Booking Details -->
      <div v-else-if="booking" class="space-y-6">
        <!-- Main Booking Card -->
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
          <!-- Status Banner -->
          <div class="px-6 py-4" :class="{
            'bg-green-500': booking.status === 'confirmed',
            'bg-yellow-500': booking.status === 'pending',
            'bg-red-500': booking.status === 'cancelled',
            'bg-blue-500': booking.status === 'checked-in'
          }">
            <div class="flex items-center justify-between">
              <div class="flex items-center text-white">
                <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                  <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span class="font-semibold capitalize">{{ booking.status }}</span>
              </div>
              <span class="text-white/90 text-sm">Booking #{{ booking.id }}</span>
            </div>
          </div>

          <!-- Booking Information -->
          <div class="p-6">
            <div class="grid md:grid-cols-2 gap-6">
              <!-- Hotel & Room Info -->
              <div class="space-y-4">
                <div class="flex items-start space-x-3">
                  <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M10.707 2.293a1 1 0 00-1.414 0l-7 7a1 1 0 001.414 1.414L4 10.414V17a1 1 0 001 1h2a1 1 0 001-1v-2a1 1 0 011-1h2a1 1 0 011 1v2a1 1 0 001 1h2a1 1 0 001-1v-6.586l.293.293a1 1 0 001.414-1.414l-7-7z"></path>
                    </svg>
                  </div>
                  <div>
                    <h3 class="font-semibold text-gray-800">{{ booking.hotel?.name }}</h3>
                    <p class="text-gray-600 text-sm">{{ booking.room?.name }}</p>
                  </div>
                </div>

                <!-- Dates -->
                <div class="bg-gray-50 rounded-lg p-4">
                  <div class="flex justify-between items-center mb-2">
                    <span class="text-sm font-medium text-gray-600">Check-in</span>
                    <span class="text-sm font-medium text-gray-600">Check-out</span>
                  </div>
                  <div class="flex justify-between items-center">
                    <div class="text-center">
                      <p class="font-semibold text-gray-800">{{ formatDate(booking.check_in_date) }}</p>
                      <p class="text-xs text-gray-500">After 3:00 PM</p>
                    </div>
                    <div class="flex-1 mx-4">
                      <div class="h-px bg-gray-300 relative">
                        <div class="absolute inset-0 flex items-center justify-center">
                          <div class="bg-white px-2">
                            <svg class="w-4 h-4 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                              <path fill-rule="evenodd" d="M7.293 14.707a1 1 0 010-1.414L10.586 10 7.293 6.707a1 1 0 011.414-1.414l4 4a1 1 0 010 1.414l-4 4a1 1 0 01-1.414 0z" clip-rule="evenodd"></path>
                            </svg>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="text-center">
                      <p class="font-semibold text-gray-800">{{ formatDate(booking.check_out_date) }}</p>
                      <p class="text-xs text-gray-500">Before 11:00 AM</p>
                    </div>
                  </div>
                </div>
              </div>

              <!-- Additional Info -->
              <div class="space-y-4">
                <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-lg p-4">
                  <h4 class="font-semibold text-gray-800 mb-2">Booking Summary</h4>
                  <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                      <span class="text-gray-600">Duration</span>
                      <span class="font-medium">{{ calculateNights(booking.check_in_date, booking.check_out_date) }} nights</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Guest</span>
                      <span class="font-medium">{{ booking.guest_name || 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between">
                      <span class="text-gray-600">Total Amount</span>
                      <span class="font-semibold text-lg text-green-600">${{ booking.total_amount || 'N/A' }}</span>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Buttons -->
        <div class="flex flex-wrap gap-4 justify-center" v-if="booking.status !== 'cancelled'">
          <button
            @click="startEdit"
            :disabled="loading"
            class="flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z"></path>
            </svg>
            Edit Booking
          </button>
          
          <button
            @click="cancelBooking"
            :disabled="loading"
            class="flex items-center px-6 py-3 bg-red-600 text-white rounded-lg hover:bg-red-700 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5"
          >
            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
            </svg>
            Cancel Booking
          </button>
        </div>

        <!-- Edit Form Modal -->
        <div v-if="editing" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
          <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full max-h-[90vh] overflow-y-auto">
            <div class="p-6">
              <div class="flex items-center justify-between mb-6">
                <h3 class="text-2xl font-bold text-gray-800">Edit Booking</h3>
                <button @click="editing = false" class="text-gray-400 hover:text-gray-600">
                  <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                  </svg>
                </button>
              </div>

              <form @submit.prevent="updateBooking" class="space-y-4">
                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Check-in Date
                  </label>
                  <input
                    type="date"
                    v-model="editForm.check_in_date"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  />
                </div>

                <div>
                  <label class="block text-sm font-medium text-gray-700 mb-2">
                    Check-out Date
                  </label>
                  <input
                    type="date"
                    v-model="editForm.check_out_date"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-colors"
                  />
                </div>

                <div class="flex space-x-3 pt-4">
                  <button
                    type="submit"
                    :disabled="loading"
                    class="flex-1 bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium"
                  >
                    <span v-if="loading">Saving...</span>
                    <span v-else>Save Changes</span>
                  </button>
                  <button
                    type="button"
                    @click="editing = false"
                    class="flex-1 bg-gray-300 text-gray-700 py-3 rounded-lg hover:bg-gray-400 transition-colors font-medium"
                  >
                    Cancel
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>

      <!-- No Booking Found -->
      <div v-else class="text-center py-20">
        <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-4">
          <svg class="w-12 h-12 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
          </svg>
        </div>
        <h3 class="text-xl font-semibold text-gray-800 mb-2">No Booking Found</h3>
        <p class="text-gray-600">The booking you're looking for doesn't exist or has been removed.</p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useHotelStore } from '@/store/hotelStore';

const route = useRoute();
const hotelStore = useHotelStore();

const bookingId = route.params.bookingId;
const booking = ref(null);
const loading = ref(false);
const error = ref(null);
const editing = ref(false);
const editForm = ref({
  check_in_date: '',
  check_out_date: '',
  room_id: ''
});

// Utility function to format dates
const formatDate = (dateString) => {
  const date = new Date(dateString);
  return date.toLocaleDateString('en-US', {
    weekday: 'short',
    year: 'numeric',
    month: 'short',
    day: 'numeric'
  });
};

// Calculate number of nights
const calculateNights = (checkIn, checkOut) => {
  const start = new Date(checkIn);
  const end = new Date(checkOut);
  const diffTime = Math.abs(end - start);
  const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
  return diffDays;
};

const cancelBooking = async () => {
  if (!confirm('Are you sure you want to cancel this booking?')) return;
  loading.value = true;
  error.value = null;
  try {
    // Check if the method exists in your store
    if (typeof hotelStore.cancelBooking === 'function') {
      await hotelStore.cancelBooking(bookingId);
      booking.value.status = 'cancelled';
    } else {
      // Fallback: Make direct API call if store method doesn't exist
      const response = await fetch(`/api/bookings/${bookingId}/cancel`, {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        }
      });
      
      if (!response.ok) {
        throw new Error('Failed to cancel booking');
      }
      
      booking.value.status = 'cancelled';
    }
  } catch (err) {
    error.value = err?.message || 'Failed to cancel booking';
  } finally {
    loading.value = false;
  }
};

const startEdit = () => {
  editing.value = true;
  editForm.value = {
    check_in_date: booking.value.check_in_date,
    check_out_date: booking.value.check_out_date,
    room_id: booking.value.room?.id || ''
  };
};

const updateBooking = async () => {
  loading.value = true;
  error.value = null;
  try {
    // Check if the method exists in your store
    if (typeof hotelStore.updateBooking === 'function') {
      await hotelStore.updateBooking(bookingId, editForm.value);
      booking.value = await hotelStore.getBookingsById(bookingId);
      editing.value = false;
    } else {
      // Fallback: Make direct API call if store method doesn't exist
      const response = await fetch(`/api/bookings/${bookingId}`, {
        method: 'PUT',
        headers: {
          'Content-Type': 'application/json',
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify(editForm.value)
      });
      
      if (!response.ok) {
        throw new Error('Failed to update booking');
      }
      
      // Reload booking details
      booking.value = await hotelStore.getBookingsById(bookingId);
      editing.value = false;
    }
  } catch (err) {
    error.value = err?.message || 'Failed to update booking';
  } finally {
    loading.value = false;
  }
};

onMounted(async () => {
  loading.value = true;
  error.value = null;
  try {
    booking.value = await hotelStore.getBookingsById(bookingId);
  } catch (err) {
    error.value = err?.message || 'Failed to load booking details';
  } finally {
    loading.value = false;
  }
});
</script>