<template>
  <div class="min-h-screen bg-gray-50">
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between items-center h-16">
          <h1 class="text-xl font-bold text-gray-800  ">Hotel Booking System</h1>
          <div class="flex items-center space-x-4">
            <div v-if="authStore.isAuthenticated" class="relative">
              <button
                @click="dropdownOpen = !dropdownOpen"
                class="flex items-center cursor-pointer space-x-2 px-4 py-2 bg-gray-100 rounded-md hover:bg-gray-200 focus:outline-none"
              >
                <span class="text-gray-600 cursor-pointer">Welcome, {{ authStore.user?.name }}</span>
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                </svg>
              </button>
              <div
                v-if="dropdownOpen"
                class="absolute right-0 mt-2 w-48 bg-white border border-gray-200 rounded-md shadow-lg z-50"
              >
                <RouterLink
                  :to="{ name: 'UserBookings', params: { userId: authStore.user?.id } }"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100"
                  @click="dropdownOpen = false"
                >
                  My Bookings
                </RouterLink>
                <RouterLink
                  to="/profile"
                  class="block px-4 py-2 text-gray-700 hover:bg-gray-100"
                  @click="dropdownOpen = false"
                >
                  Profile
                </RouterLink>
                <button
                  @click="handleLogout; dropdownOpen = false"
                  class="block w-full  px-4 py-2 text-red-600 hover:bg-gray-100 cursor-pointer"
                >
                  Logout
                </button>
              </div>
            </div>
            <div v-else class="flex items-center space-x-4">
              <RouterLink to="/login" class="text-blue-500 hover:text-blue-600 font-medium">Login</RouterLink>
              <router-link to="/register" class="bg-blue-500 text-white px-4 py-2 rounded-md hover:bg-blue-600">
                Register
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </nav>

    <!-- MAIN CONTENT -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <div class="text-center">
        <h2 class="text-3xl font-bold text-gray-900 mb-4">
          Welcome to Hotel Booking System
        </h2>
        <p class="text-xl text-gray-600 mb-8">
          Find and book your perfect hotel stay
        </p>
        <div v-if="!authStore.isAuthenticated" class="space-x-4">
          <router-link to="/register"
            class="bg-blue-500 text-white px-6 py-3 rounded-md hover:bg-blue-600 inline-block">
            Get Started
          </router-link>
          <router-link to="/login" class="bg-gray-500 text-white px-6 py-3 rounded-md hover:bg-gray-600 inline-block">
            Sign In
          </router-link>

        </div>
        <div v-else class="text-green-600 font-medium">
          <Hotel />
        </div>
      </div>
    </div>

  </div>
</template>

<script setup>
import { useRouter } from 'vue-router';
import { useAuthStore } from '@/store/modules/auth';
import Hotel from './Hotel.vue';
import { ref, onMounted ,onUnmounted} from 'vue';

const router = useRouter();
const authStore = useAuthStore();
const dropdownOpen = ref(false);

const handleLogout = async () => {
  try {
    await authStore.logout();
    router.push('/')
  } catch (error) {
    console.error("Logout failed:", error);
  }
}

// Optionally, close dropdown when clicking outside
function handleClickOutside(event) {
  if (!event.target.closest('.relative')) {
    dropdownOpen.value = false;
  }
}

onMounted(() => {
  document.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  document.removeEventListener('click', handleClickOutside);
});
</script>

<style scoped>
.home {
  max-width: 600px;
  margin: 0 auto;
  padding: 20px;
}

h1 {
  color: #42b983;
}

p {
  font-size: 18px;
  margin-bottom: 15px;
}

.btn {
  background-color: #42b983;
  color: white;
  padding: 10px 20px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 16px;
}

.btn:hover {
  background-color: #369870;
}
</style>