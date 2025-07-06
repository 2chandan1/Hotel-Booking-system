<template>
    <div class="min-h-screen flex items-center justify-center bg-gray-50 p-4">
        <div class="bg-white rounded-lg shadow-md p-8 w-full max-w-md">
            <h2 class="text-2xl font-bold text-center mb-6">Login</h2>
            <form @submit.prevent="handleLogin" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Email</label>
                    <input type="email" v-model="form.email"
                        :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500', authStore.errors.email ? 'border-red-500' : 'border-gray-300']"
                        placeholder="Enter your email" required />
                   
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Password</label>
                    <input type="password" v-model="form.password"
                        :class="['w-full px-3 py-2 border rounded-md focus:ring-2 focus:ring-blue-500', hasError &&authStore.errors ? 'border-red-500' : 'border-gray-300']"
                        placeholder="Enter your email" required />
                        <span v-if=" hasError  && authStore.errors" class="text-red-500 text-sm">
                               {{ authStore.errors }}
                           </span>
                </div>
                <button type="submit" :disabled="authStore.loading"
                    class="w-full bg-blue-500 text-white py-2 px-4 rounded-md hover:bg-blue-600 disabled:opacity-50 cursor-pointer disabled:cursor-not-allowed">
                    {{ authStore.loading ? 'Logging in...' : 'Login' }}
                </button>

            </form>
            <div class="mt-4 text-center">
                <p class="text-gray-600">
                    Don't have an account?
                    <router-link to="/register" class="text-blue-500 hover:text-blue-600">
                        Register here
                    </router-link>
                </p>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue';
import { useAuthStore } from '@/store/modules/auth';
import { useRouter } from 'vue-router'
const router = useRouter();
const authStore = useAuthStore();
const hasError  = ref(false);

const form = ref({
    email: '',
    password: ''
})
const handleLogin = async () => {
    hasError.value = false;
   
    try {
        await authStore.login(form.value)
        router.push('/')
    } catch (error) {
        hasError.value = true;
        console.error("Login failed:", error);
    }
}
</script>