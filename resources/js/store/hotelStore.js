// stores/hotelStore.js
import { defineStore } from "pinia";
import axios from "axios";
import { useAuthStore } from "@/store/modules/auth";
export const useHotelStore = defineStore("hotel", {
    state: () => ({
        hotels: [],
        searchFilters: {
            location: "",
            checkIn: "",
            checkOut: "",
            guests: 1,
            minPrice: 0,
            maxPrice: 1000,
            amenities: [],
            rating: 0,
        },
        loading: false,
        loadingMore: false,
        searchAttempted: false,
        error: null,
        currentPage: 1,
        totalPages: 0,
        totalResults: 0,
        perPage: 10,
    }),

    getters: {
        filteredHotels: (state) => state.hotels,
        isLoading: (state) => state.loading,
        hasError: (state) => state.error !== null,
        hasMore: (state) => state.currentPage < state.totalPages,
        searchParams: (state) => ({
            ...state.searchFilters,
            page: state.currentPage,
            perPage: state.perPage,
        }),
    },
    actions: {
        getAuthenticatedAxios() {
            const token = localStorage.getItem("token");
            if (token) {
                return axios.create({
                    headers: {
                        Authorization: `Bearer ${token}`,
                        Accept: "application/json",
                        "Content-Type": "application/json",
                    },
                });
            }
            return axios;
        },
        //Load All Hotels
        async loadAllHotels() {
            this.loading = true;
            this.error = null;
            try {
                const response = await axios.get("/api/hotels");
                this.hotels =
                    response.data.hotels.data || response.data.hotels || [];
                this.totalPages =
                    response.data.last_page ||
                    Math.ceil(response.data.total / this.perPage);
                this.totalResults = response.data.total || 0;
                this.currentPage = response.data.current_page || 1;
                console.log(`Loaded ${this.hotels.length} hotels`);
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to load hotels";
                console.error("Error loading hotels:", this.error);
            } finally {
                this.loading = false;
            }
        },

        // Update search filters
        updateSearchFilters(filters) {
            this.searchFilters = { ...this.searchFilters, ...filters };
            this.currentPage = 1; // Reset to first page on new search
        },

        //Clear All Filters
        clearFilters() {
            this.searchFilters = {
                location: "",
                checkIn: "",
                checkOut: "",
                guests: 1,
                minPrice: 0,
                maxPrice: 1000,
                amenities: [],
                rating: 0,
            };
            this.currentPage = 1;
            this.searchAttempted = false;
            this.error = null;
            // Load all hotels again when filters are cleared
            this.loadAllHotels();
        },
        // Search hotels with filters (no authentication needed)
        async searchHotels(resetPage = false) {
            if (resetPage) {
                this.currentPage = 1;
            }
            this.loading = true;
            this.error = null;
            this.searchAttempted = true;
            try {
                const params = {
                    ...this.searchFilters,
                    page: this.currentPage,
                    per_page: this.perPage,
                };
                //remove empty value
                Object.keys(params).forEach((key) => {
                    if (
                        params[key] === "" ||
                        params[key] === null ||
                        params[key] === undefined
                    ) {
                        delete params[key];
                    }
                });
                console.log("Searching hotels with params:", params);
                const response = await axios.get("/api/hotels/hotelsearch", {
                    params,
                });
                if (resetPage) {
                    this.hotels = [];
                }
                this.hotels =
                    response.data.hotels.data || response.data.hotels || [];
                this.totalPages =
                    response.data.last_page ||
                    Math.ceil(response.data.total / this.perPage);
                this.totalResults = response.data.total || 0;
                this.currentPage = response.data.current_page || 1;
                console.log(`Found ${this.hotels.length} hotels`);
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to search hotels";
                console.error("Error searching hotels:", this.error);
                this.hotels = [];
            } finally {
                this.loading = false;
            }
        },
        // Load more hotels (for infinite scroll)
        async loadMoreHotels() {
            if (this.currentPage >= this.totalPages || this.loadingMore) return;
            this.loadingMore = true;

            try {
                this.currentPage += 1;

                const isSearchMode =
                    this.searchAttempted &&
                    Object.values(this.searchFilters).some(
                        (value) =>
                            value !== "" &&
                            value !== 0 &&
                            value !== 1 &&
                            (!Array.isArray(value) || value.length > 0)
                    );
                let response;
                const authenticatedAxios = this.getAuthenticatedAxios();
                if (isSearchMode) {
                    const params = {
                        ...this.searchFilters,
                        page: this.currentPage,
                        per_page: this.perPage,
                    };
                    Object.keys(params).forEach((key) => {
                        if (
                            params[key] === "" ||
                            params[key] === null ||
                            params[key] === undefined
                        ) {
                            delete params[key];
                        }
                    });
                    response = await authenticatedAxios.get(
                        "/api/hotels/hotelsearch",
                        { params }
                    );
                } else {
                    response = await authenticatedAxios.get("/api/hotels", {
                        params: {
                            page: this.currentPage,
                            per_page: this.perPage,
                        },
                    });
                }
                const newHotels =
                    response.data.data ||
                    response.data.hotels?.data ||
                    response.data.hotels ||
                    [];
                this.hotels = [...this.hotels, ...newHotels];
                console.log(`Loaded ${newHotels.length} more hotels`);
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to load more hotels";
                this.currentPage -= 1;
                console.error("Load more hotels error:", error);
            } finally {
                this.loadingMore = false;
            }
        },
        // Get hotel by ID
        async getHotelById(id) {
            this.loading = true;
            this.error = null;
            try {
                const authenticatedAxios = this.getAuthenticatedAxios();
                const response = await authenticatedAxios.get(
                    `/api/hotels/${id}`
                );
                return response.data.data || response.data.hotel;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to fetch hotel details";
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async checkRoomAvailability(hotelId, filters = {}) {
            this.loading = true;
            this.error = null;

            try {
                const authenticatedAxios = this.getAuthenticatedAxios();

                const params = new URLSearchParams({
                    ...filters, // Should include check_in_date and check_out_date
                });
                console.log(
                    "Checking room availability with params:",
                    params.toString()
                );

                const response = await authenticatedAxios.get(
                    `api/hotels/${hotelId}/rooms/check-availability?${params.toString()}`
                );

                return {
                    data: response.data.data || {},
                    success: response.data.success,
                    message: response.data.message,
                };
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to check room availability";
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async checksingleRoomAvailability(roomId, checkInDate, checkOutDate) {
            this.loading = true;
            this.error = null;

            try {
                const authenticatedAxios = this.getAuthenticatedAxios();

                const params = new URLSearchParams({
                    check_in_date: checkInDate,
                    check_out_date: checkOutDate,
                });

                console.log(
                    "Checking room availability with params:",
                    params.toString()
                );

                const response = await authenticatedAxios.get(
                    `/api/rooms/${roomId}/check-availability?${params.toString()}`
                );

                return {
                    data: response.data.data || {},
                    success: response.data.success,
                    message:
                        response.data.message ||
                        "Availability checked successfully",
                };
            } catch (error) {
                console.error("Room availability check error:", error);

                const errorMessage =
                    error.response?.data?.message ||
                    "Failed to check room availability";

                this.error = errorMessage;

                return {
                    success: false,
                    message: errorMessage,
                    data: null,
                };
            } finally {
                this.loading = false;
            }
        },

        async createBooking(bookingData) {
            const authStore = useAuthStore();
            this.loading = true;
            this.error = null;
            console.log("Creating booking with data:", authStore.user.id);

            try {
                const authenticatedAxios = this.getAuthenticatedAxios();

                // Prepare booking data
                const payload = {
                    user_id: authStore.user.id,
                    hotel_id: bookingData.hotel_id,
                    room_id: bookingData.room_id,
                    check_in_date: bookingData.check_in_date,
                    check_out_date: bookingData.check_out_date,
                    guests: bookingData.guests,
                    currency: bookingData.currency || "INR", // Default to USD if not provided
                    payment_method: bookingData.payment_method || "credit_card",
                    total_amount: parseFloat(bookingData.total_amount),
                    special_requests: bookingData.special_requests || "",
                    status: bookingData.status || "pending",
                    guest_details: bookingData.guest_details,
                };

                console.log("Creating booking with data:", payload);

                const response = await authenticatedAxios.post(
                    "/api/bookings",
                    payload
                );

                return {
                    success: true,
                    data: response.data.data || response.data,
                    message:
                        response.data.message || "Booking created successfully",
                };
            } catch (error) {
                console.error("Booking creation error:", error);

                const errorMessage =
                    error.response?.data?.message ||
                    error.response?.data?.error ||
                    "Failed to create booking";

                this.error = errorMessage;

                throw {
                    success: false,
                    message: errorMessage,
                    response: error.response,
                };
            } finally {
                this.loading = false;
            }
        },
        async getBookingsById(bookingId) {
            this.loading = true;
            this.error = null;
            try {
                const authenticatedAxios = this.getAuthenticatedAxios();
                const response = await authenticatedAxios.get(
                    `/api/bookings/${bookingId}`
                );
                return response.data.data || response.data.hotel;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to fetch booking details";
                throw error;
            } finally {
                this.loading = false;
            }
        },
        async getBookingsByUserId(userId){
            this.loading=true;
            this.error=null;
            try{
                const authenticatedAxios=this.getAuthenticatedAxios();
                const response=await authenticatedAxios.get(`/api/bookings/user/${userId}`)
                return response.data.data || [];
            }catch(error){
                this.error=error.response?.data?.message || "Failed to fetch bookings";
                throw error;
            }finally{
                this.loading=false;
            
            }
        },
        // Cancel booking
        async cancelBooking(bookingId) {
            this.loading = true;
            this.error = null;
            try {
                const authenticatedAxios = this.getAuthenticatedAxios();
                await authenticatedAxios.patch(
                    `/api/bookings/${bookingId}/cancel`
                );
                // Optionally update local state
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to cancel booking";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Update booking
        async updateBooking(bookingId, updateData) {
            this.loading = true;
            this.error = null;
            try {
                const authenticatedAxios = this.getAuthenticatedAxios();
                await authenticatedAxios.put(
                    `/api/bookings/${bookingId}`,
                    updateData
                );
            } catch (error) {
                this.error =
                    error.response?.data?.message || "Failed to update booking";
                throw error;
            } finally {
                this.loading = false;
            }
        },
        // Get all rooms for a hotel (with optional filters)
        async getHotelRooms(hotelId, filters = {}) {
            this.loading = true;
            this.error = null;

            try {
                const authenticatedAxios = this.getAuthenticatedAxios();

                const params = new URLSearchParams({
                    hotel_id: hotelId,
                    ...filters,
                });

                const response = await authenticatedAxios.get(
                    `/api/rooms?${params.toString()}`
                );

                return {
                    data: response.data.data || [],
                    pagination: response.data.pagination || null,
                };
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to fetch hotel rooms";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Get specific room details
        async getRoomById(roomId) {
            this.loading = true;
            this.error = null;

            try {
                const authenticatedAxios = this.getAuthenticatedAxios();
                const response = await authenticatedAxios.get(
                    `/api/rooms/${roomId}`
                );

                return response.data.data || response.data.room;
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to fetch room details";
                throw error;
            } finally {
                this.loading = false;
            }
        },

        // Check room availability with date range
        async checkRoomAvailabilityWithDates(
            hotelId,
            checkIn,
            checkOut,
            roomType = null
        ) {
            this.loading = true;
            this.error = null;

            try {
                const authenticatedAxios = this.getAuthenticatedAxios();

                const params = new URLSearchParams({
                    hotel_id: hotelId,
                    check_in: checkIn,
                    check_out: checkOut,
                    is_available: true,
                    is_active: true,
                });

                if (roomType) {
                    params.append("room_type", roomType);
                }

                const response = await authenticatedAxios.get(
                    `/api/rooms/availability?${params.toString()}`
                );

                return {
                    data: response.data.data || [],
                    pagination: response.data.pagination || null,
                };
            } catch (error) {
                this.error =
                    error.response?.data?.message ||
                    "Failed to check room availability";
                throw error;
            } finally {
                this.loading = false;
            }
        },
        setCurrentPage(page) {
            this.currentPage = page;
        },
        resetStore() {
            this.hotels = [];
            this.searchFilters = {
                location: "",
                checkIn: "",
                checkOut: "",
                guests: 1,
                minPrice: 0,
                maxPrice: 1000,
                amenities: [],
                rating: 0,
            };
            this.loading = false;
            this.loadingMore = false;
            this.searchAttempted = false;
            this.error = null;
            this.currentPage = 1;
            this.totalPages = 0;
            this.totalResults = 0;
        },
    },
});
