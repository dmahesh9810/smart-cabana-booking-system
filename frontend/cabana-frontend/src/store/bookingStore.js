import { defineStore } from 'pinia';
import api from '../api/axios';

export const useBookingStore = defineStore('booking', {
    state: () => ({
        isAvailable: null,
        availabilityMessage: '',
        loading: false,
        error: null,
        bookingDetails: {
            cabana_id: null,
            check_in_date: null,
            check_out_date: null,
            price_per_night: 0,
        },
        userBookings: [],
        currentBooking: null,
    }),

    actions: {
        async checkAvailability(cabanaId, checkIn, checkOut) {
            this.loading = true;
            this.error = null;
            this.isAvailable = null;
            this.availabilityMessage = '';

            try {
                // Determine whether it's sent via query params or payload.
                // It's a POST request, so we use payload
                const response = await api.post(`/cabanas/${cabanaId}/check-availability`, {
                    check_in: checkIn,
                    check_out: checkOut
                });

                // Assuming backend returns { available: true/false } or something similar
                // We'll trust a successful 200 optionally means available, or explicitly read response.data.available
                this.isAvailable = response.data.available ?? true;
                this.availabilityMessage = response.data.message || 'Available for booking';

                return this.isAvailable;
            } catch (err) {
                this.isAvailable = false;
                if (err.response && err.response.data && err.response.data.message) {
                    this.error = err.response.data.message;
                    this.availabilityMessage = this.error;
                } else {
                    this.error = 'Not available for selected dates (or error checking).';
                    this.availabilityMessage = this.error;
                }
                return false;
            } finally {
                this.loading = false;
            }
        },

        setBookingDetails(details) {
            this.bookingDetails = details;
        },

        async createBooking(bookingData) {
            this.loading = true;
            this.error = null;

            try {
                const response = await api.post('/bookings', bookingData);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to create booking. Please try again.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async fetchUserBookings() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/bookings');
                this.userBookings = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load bookings.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async fetchBookingDetails(id) {
            this.loading = true;
            this.error = null;
            this.currentBooking = null;
            try {
                const response = await api.get(`/bookings/${id}`);
                this.currentBooking = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load booking details.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async submitReview(bookingId, payload) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post(`/bookings/${bookingId}/reviews`, payload);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to submit review.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async cancelBooking(id) {
            this.error = null;
            try {
                const response = await api.delete(`/bookings/${id}`);
                // Update local list
                const idx = this.userBookings.findIndex(b => b.id === id);
                if (idx !== -1) this.userBookings[idx].status = 'cancelled';
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to cancel booking.';
                throw err;
            }
        }
    }
});
