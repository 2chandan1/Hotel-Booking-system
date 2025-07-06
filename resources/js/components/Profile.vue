<template>
    <div class="min-h-screen bg-gray-50 py-8">
        <div class="max-w-2xl mx-auto px-4">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-2xl font-bold text-gray-800">User Profile</h1>
                <p class="text-gray-600 mt-2">Manage your account information</p>
            </div>
            <div class="bg-white rounded-lg shadow-md p-6">
                <form @submit.prevent="updateProfile" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Full Name</label>
                        <input type="text" v-model="form.name" :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                            errors.name ? 'border-red-500' : 'border-gray-300']" placeholder="Enter your full name"
                            required />
                        <span v-if="errors.name" class="text-red-500 text-sm mt-1">
                            {{ errors.name[0] }}
                        </span>
                    </div>
                    <!-- Email Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Email Address</label>
                        <input type="email" v-model="form.email" :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                            errors.email ? 'border-red-500' : 'border-gray-300']" placeholder="Enter your email"
                            required />
                        <span v-if="errors.email" class="text-red-500 text-sm mt-1">
                            {{ errors.email[0] }}
                        </span>
                    </div>
                    <!-- Phone Field -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Phone Number</label>
                        <input type="text" v-model="form.phone" :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                            errors.phone ? 'border-red-500' : 'border-gray-300']" placeholder="Enter your phone number"
                            required />
                        <span v-if="errors.phone" class="text-red-500 text-sm mt-1">
                            {{ errors.phone[0] }}
                        </span>
                    </div>
                    <!-- Password Section -->
                    <div class="border-t pt-6">
                        <h3 class="text-lg font-medium text-gray-800 mb-4">Change Password</h3>
                        <div class="space-y-4">

                            <!-- Current Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Current Password</label>
                                <input type="password" v-model="form.current_password" :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                    errors.current_password ? 'border-red-500' : 'border-gray-300']"
                                    placeholder="Enter current password">
                                <span v-if="errors.current_password" class="text-red-500 text-sm mt-1">
                                    {{ errors.current_password[0] }}
                                </span>
                            </div>
                            <!-- New Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">New Password</label>
                                <input type="password" v-model="form.password" :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent',
                                    errors.password ? 'border-red-500' : 'border-gray-300']"
                                    placeholder="Enter new password" />
                                <span v-if="errors.password" class="text-red-500 text-sm mt-1">
                                    {{ errors.password[0] }}
                                </span>
                            </div>
                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2 ">Confirm Password</label>
                                <input type="password" v-model="form.password_confirmation"
                                    :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500 focus:border-transparent', errors.password_confirmation ? 'border-red-500' : 'border-gray-300']"
                                    placeholder="Confirm new password">
                                <span v-if="errors.password_confirmation" class="text-red-500 text-sm mt-1">
                                    {{ errors.password_confirmation[0] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div class="flex gap-4 pt-6">
                        <button type="submit" :disabled="loading"
                            class="flex-1 bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                            {{ loading ? 'Updating...' : 'Update Profile' }}
                        </button>
                        <button type="button" @click="resetForm"
                            class="px-6 py-2 bg-gray-200 text-gray-700 rounded-md hover:bg-gray-300 transition-colors">
                            Reset
                        </button>
                    </div>
                </form>
                <!-- Success Message -->
                <div v-if="successMessage" class="mt-4 p-4 bg-green-100 border border-green-400 text-green-700 rounded-md">
                    {{ successMessage }}
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useAuthStore } from '@/store/modules/auth';
import axios from 'axios';
const authStore = useAuthStore();
// Form data
const form = ref({
    name: '',
    email: '',
    phone: '',
    current_password: '',
    password: '',
    password_confirmation: ''
})
// Component state
const loading = ref(false)
const errors = ref({})
const successMessage = ref('')

const loadProfile = async () => {
    try {
        const response = await axios.get('/api/user');
        console.log(response.data);
        form.value.name = response.data.name;
        form.value.email = response.data.email;
        form.value.phone = response.data.phone;
        authStore.user = response.data; // Update the auth store with user data
    } catch (error) {
        console.error("Failed to load profile:", error);
    }

}
const updateProfile=async()=>{
    loading.value=true;
     errors.value = {}
    successMessage.value = ''
    try{
        const userId=authStore.user.id;
       const updateData={
        name:form.value.name,
        email:form.value.email, 
        phone:form.value.phone,
       }
       if(form.value.current_password || form.value.password){
        updateData.current_password=form.value.current_password;
        updateData.password=form.value.password;
        updateData.password_confirmation=form.value.password_confirmation;
       }
       const response=await axios.put(`/api/user/${userId}`,updateData);
       authStore.user=response.data.user || response.data

       //clear password fields
       form.value.current_password = '';
       form.value.password = '';
        form.value.password_confirmation = '';
        successMessage.value = 'Profile updated successfully!'
         // Clear success message after 3 seconds
        setTimeout(() => {
            successMessage.value = ''
        }, 3000)
    }catch(error){
         if (error.response?.data?.errors) {
            errors.value = error.response.data.errors
        } else {
            errors.value = { general: ['An error occurred while updating profile'] }
        }
        console.error('Profile update failed:', error)
    }finally{
        loading.value = false
    }
}
// Reset original form values
const resetForm=()=>{
    if(authStore.user){
        form.value.name=authStore.user.name
        form.value.email=authStore.user.email
        form.value.phone=authStore.user.phone || '';
    }
    form.value.current_password = ''
    form.value.password = ''
    form.value.password_confirmation = ''
    errors.value = {}
    successMessage.value = ''
}
onMounted(() => {
    if (authStore.user) {
        form.value.name = authStore.user.name
        form.value.email = authStore.user.email
    } else {
        // Otherwise, fetch from API
        loadProfile()
    }
})
</script>