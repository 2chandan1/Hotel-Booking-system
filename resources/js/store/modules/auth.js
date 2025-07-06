import { defineStore } from "pinia";
import axios from "axios";

axios.defaults.baseURL = "http://127.0.0.1:8000";
axios.defaults.withCredentials = true;

export const useAuthStore = defineStore("auth", {
    state: () => {
        const token = localStorage.getItem("token") || null;
        const user = JSON.parse(localStorage.getItem("user")) || null;
        // Initialize axios header if token exists (fixes page refresh issue)
        if (token) {
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        }
        return {
            user,
            token,
            loading: false,
            errors: {},
        };
    },
    getters: {
        isAuthenticated: (state) => !!state.token,
        userId: (state) => state.user?.id || null,
    },
    actions: {
        setToken(token) {
            this.token = token;
            localStorage.setItem("token", token);
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        },
        setUser(user){
            this.user=user;
            localStorage.setItem("user", JSON.stringify(user));
        },
        ClearAuth() {
            this.user = null;
            this.token = null;
            localStorage.removeItem("token");
             localStorage.removeItem("user"); 
            delete axios.defaults.headers.common["Authorization"];
        },

        //register
        async register(userData) {
             this.loading = true;
                this.errors = {};
            try {
                const response = await axios.post("api/register", userData);
                this.setToken(response.data.token);
                 this.setUser(response.data.user); 
                this.user = response.data.user;
                return response.data;
            } catch (error) {
                this.errors = error.response?.data?.error || {};
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async login(credentials) {
                
                try {
                this.loading=true
                this.errors={}
                
                const response=await axios.post("api/login",credentials)
                this.setToken(response.data.token)
                 this.setUser(response.data.user); 
                this.user=response.data.user;
                return response.data;
            } catch (error) {
                this.errors = error.response?.data?.message || {};
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async logout(){
            try{
                await axios.post('api/logout')
            }catch(error){
                console.error("Logout failed:", error);
            }finally {
                this.ClearAuth();
            }
        },
        async fetchUser(){
            try {
                if(!this.token) return null;
                const response = await axios.get('api/user');
                this.setUser(response.data);
                return response.data
            } catch (error) {
                console.error("Failed to fetch user:", error);
                
            }
        }

    },
});
