import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "../store/modules/auth";
import Home from "../components/Home.vue";
import Register from "../components/auth/Register.vue";
import Login from "../components/auth/Login.vue";
import Profile from "../components/Profile.vue";
import Hotel from "../components/Hotel.vue";
import HotelDetail from "../components/HotelDetail.vue";
import BookRoom from "../components/BookRoom.vue";
import BookingDeatils from "../components/BookingDeatils.vue";
import UserBookings from "../components/UserBookings.vue";


const requireAuth = (to, from, next) => {
    const authStore = useAuthStore();
    if (authStore.isAuthenticated) {
        next(); // User is authenticated, allow access
    } else {
        next("/"); // Redirect to home
    }
};
const routes = [
    { path: "/", name: "Home", component: Home},
    {
        path: "/register",
        name: "Register",
        component: Register,
    },
    {
        path: "/login",
        name: "Login",
        component: Login,
    },
    {
        path: "/profile",
        name: "Profile",
        component:Profile,
        beforeEnter: requireAuth // Protect this route
    },
    {
        path:"/hotel",
        name:"Hotel",
        component:Hotel
    },
    {
        path: "/hotel/:id",
        name: "HotelDetail",    
        component:HotelDetail
    },
    {
       path: '/hotels/:hotelId/book',
        name:"BookingPage",
        component:BookRoom,
         props: true
    },
    {
        path: "/bookings/:bookingId",
        name: "MyBookings",
        component:BookingDeatils
    },
    {
        path:'/bookings/user/:userId',
        name:"UserBookings",
        component:UserBookings
    }

];
const router = createRouter({
    history: createWebHistory(),
    routes,
});

export default router;
