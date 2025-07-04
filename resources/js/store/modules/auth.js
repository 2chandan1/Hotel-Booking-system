import { defineStore } from "pinia";
import axios from "axios";
// Set up axios defaults - ADJUST THESE TO MATCH YOUR API ENDPOINTS
axios.defaults.baseURL = "http://localhost:8000/api";
axios.defaults.withCredentials = true;

export const useAuthStore = defineStore("auth", {
    state: () => ({
        user: null,
        token: localStorage.getItem("token") || null,
        loading: false,
        errors: {},
    }),
    getters: {
        isAuthrnticated: (state) => !!state.token,
    },
    actions: {
        setToken(token) {
            this.token = token;
            localStorage.setItem("token", token);
            axios.defaults.headers.common["Authorization"] = `Bearer ${token}`;
        },

        ClearAuth() {
            this.user = null;
            this.token = null;
            localStorage.removeItem("token");
            delete axios.defaults.headers.common["Authorization"];
        },

        //register
        async register(userData) {
            try {
                this.loading = true;
                this.errors = {};
                const response = await axios.post("/api/register", userData,{
                    withCredentials: false
                });
                this.setToken(response.data.token);
                this.user = response.data.user;
                return response.data;
            } catch (error) {
                this.errors = error.response?.data?.errors || {};
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async login(credentials) {
            try {
                this.loading=true
                this.erroe={}
                const response=await axios.post("/login",credentials)
                this.setToken(response.data.token)
                this.user=response.data.user;
                return response.data;
            } catch (error) {
                this.errors = error.response?.data?.errors || {};
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async logout(){
            try{
                await axios.post('/logout')
            }catch(error){
                console.error("Logout failed:", error);
            }finally {
                this.ClearAuth();
            }
        }
    },
});
