<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
        <div class="bg-white rounded-lg shadow-md p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6"> Register</h2>

            <form @submit.prevent="handleRegister" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Name</label>
                    <input type="text" v-model="form.name"
                        :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500', authStore.errors.name ? 'border-red-500' : 'border-gray-300']"
                        placeholder="Enter your name" required>
                    <span v-if="authStore.errors.name" class="text-red-500 text-sm">
                        {{ authStore.errors.name[0] }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" v-model="form.email"
                        :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500', authStore.errors.email ? 'border-red-500' : 'border-gray-300']"
                        placeholder="Enter your email" required />
                    <span v-if="authStore.errors.email" class="text-red-500 text-sm">
                        {{ authStore.errors.email[0] }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Phone</label>
                    <input type="text" v-model="form.phone"
                        :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500', authStore.errors.phone ? 'border-red-500' : 'border-gray-300']"
                        placeholder="Enter your phone" required />
                    <span v-if="authStore.errors.phone" class="text-red-500 text-sm">
                        {{ authStore.errors.phone[0] }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" v-model="form.password"
                        :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500', authStore.errors.password ? 'border-red-500' : 'border-gray-300']"
                        placeholder="Enter your password" required />
                    <span v-if="authStore.errors.password" class="text-red-500 text-sm">
                        {{ authStore.errors.password[0] }}
                    </span>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Confirm Password</label>
                    <input type="password" v-model="form.password_confirmation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md focus:ring-2 focus:ring-blue-500"
                        placeholder="Confirm your password" required />
                </div>
                <button type="submit" :disabled="authStore.loading"
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 disabled:opacity-50 cursor-pointer">{{
                        authStore.loading ? 'Registering...' : 'Register' }}</button>
            </form>
            <div class="mt-4 text-center">
                <p class="text-gray-600">
                    Already have an account?
                    <router-link to="/login" class="text-blue-500 hover:text-blue-600">
                        Login here
                    </router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../../store/modules/auth';
const router= useRouter();
const authStore = useAuthStore();
console.log("Auth Store:", authStore.errors);

const form = ref({
  name: '',
  email: '',
  phone:'',
  password: '',
  password_confirmation: ''
})

const handleRegister=async()=>{
    try{
        await authStore.register(form.value);
        // Redirect to login page after successful registration
        router.push('/');
    }catch(error){
        console.error("Registration failed:", error);
    }
}
</script>