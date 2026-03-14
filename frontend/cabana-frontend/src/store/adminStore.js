import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAdminStore = defineStore('admin', {
    state: () => ({
        dashboardStats: null,
        cabanas: [],
        bookings: [],
        bookingsMeta: null,
        selectedBooking: null,
        payments: [],
        loading: false,
        error: null,
        successMessage: null,
    }),

    actions: {
        _setSuccess(msg) {
            this.successMessage = msg;
            setTimeout(() => { this.successMessage = null; }, 3500);
        },

        // ───────────── Dashboard ────────────────────────────────────────
        async fetchDashboardStats() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/dashboard/stats');
                this.dashboardStats = response.data.data ?? response.data;
            } catch (err) {
                this.error = 'Failed to load dashboard statistics.';
            } finally {
                this.loading = false;
            }
        },

        // ───────────── Cabanas ──────────────────────────────────────────
        async fetchCabanas() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/cabanas');
                this.cabanas = response.data.data ?? response.data;
            } catch (err) {
                this.error = 'Failed to load cabanas.';
            } finally {
                this.loading = false;
            }
        },

        async createCabana(payload) {
            this.loading = true;
            this.error = null;
            try {
                const isForm = payload instanceof FormData;
                const headers = isForm ? { 'Content-Type': 'multipart/form-data' } : {};
                const response = await api.post('/admin/cabanas', payload, { headers });
                await this.fetchCabanas();
                this._setSuccess('Cabana created successfully!');
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to create cabana.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async updateCabana(id, payload) {
            this.loading = true;
            this.error = null;
            try {
                if (payload instanceof FormData) {
                    payload.append('_method', 'PUT');
                    const response = await api.post(`/admin/cabanas/${id}`, payload, {
                        headers: { 'Content-Type': 'multipart/form-data' },
                    });
                    await this.fetchCabanas();
                    this._setSuccess('Cabana updated successfully!');
                    return response.data;
                } else {
                    const response = await api.put(`/admin/cabanas/${id}`, payload);
                    await this.fetchCabanas();
                    this._setSuccess('Cabana updated successfully!');
                    return response.data;
                }
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to update cabana.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async deleteCabana(id) {
            this.loading = true;
            this.error = null;
            try {
                await api.delete(`/admin/cabanas/${id}`);
                await this.fetchCabanas();
                this._setSuccess('Cabana deleted successfully!');
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to delete cabana.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async toggleCabanaStatus(id) {
            this.error = null;
            try {
                const response = await api.patch(`/admin/cabanas/${id}/status`);
                const idx = this.cabanas.findIndex(c => c.id === id);
                if (idx !== -1) this.cabanas[idx] = response.data.data;
                this._setSuccess(response.data.message);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to toggle cabana status.';
                throw err;
            }
        },

        // ───────────── Bookings ─────────────────────────────────────────
        async fetchBookings(params = {}) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/bookings', { params });
                this.bookings = response.data.data ?? response.data;
                this.bookingsMeta = response.data.meta ?? null;
            } catch (err) {
                this.error = 'Failed to load bookings.';
            } finally {
                this.loading = false;
            }
        },

        async fetchBookingById(id) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get(`/admin/bookings/${id}`);
                this.selectedBooking = response.data.data ?? response.data;
                return this.selectedBooking;
            } catch (err) {
                this.error = 'Failed to load booking details.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async updateBookingStatus(id, status) {
            this.error = null;
            try {
                const response = await api.patch(`/admin/bookings/${id}/status`, { status });
                // Patch the local list item in-place
                const idx = this.bookings.findIndex(b => b.id === id);
                if (idx !== -1) this.bookings[idx] = response.data.data;
                this._setSuccess(response.data.message);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to update booking status.';
                throw err;
            }
        },

        async deleteBooking(id) {
            this.loading = true;
            this.error = null;
            try {
                await api.delete(`/admin/bookings/${id}`);
                this.bookings = this.bookings.filter(b => b.id !== id);
                this._setSuccess('Booking deleted successfully!');
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to delete booking.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        // ───────────── Payments ─────────────────────────────────────────
        async fetchPayments() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/payments');
                this.payments = response.data.data ?? response.data;
            } catch (err) {
                this.error = 'Failed to load payments.';
            } finally {
                this.loading = false;
            }
        },
    },
});
