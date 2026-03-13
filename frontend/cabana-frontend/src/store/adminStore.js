import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAdminStore = defineStore('admin', {
    state: () => ({
        dashboardStats: null,
        cabanas: [],
        bookings: [],
        payments: [],
        loading: false,
        error: null,
    }),

    actions: {
        async fetchDashboardStats() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/dashboard/stats');
                this.dashboardStats = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load dashboard statistics.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async fetchCabanas() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/cabanas');
                this.cabanas = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load cabanas.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async createCabana(payload) {
            this.loading = true;
            this.error = null;
            try {
                // If the payload contains an image file, headers will be properly managed automatically if it's FormData.
                const headers = payload instanceof FormData ? { 'Content-Type': 'multipart/form-data' } : {};
                const response = await api.post('/admin/cabanas', payload, { headers });
                await this.fetchCabanas(); // Refresh list automatically
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
                // HTML Forms don't support PUT with multipart/form-data natively in typical setups without method spoofing
                // In Laravel, if using FormData, append `_method: 'PUT'` and send as POST.
                let config = {};
                let submitPayload = payload;

                if (payload instanceof FormData) {
                    payload.append('_method', 'PUT');
                    config.headers = { 'Content-Type': 'multipart/form-data' };
                    // We must use POST to spoof PUT due to PHP constraints with multipart form overrides
                    const response = await api.post(`/admin/cabanas/${id}`, payload, config);
                    await this.fetchCabanas();
                    return response.data;
                } else {
                    const response = await api.put(`/admin/cabanas/${id}`, payload);
                    await this.fetchCabanas();
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
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to delete cabana.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async fetchBookings() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/bookings');
                this.bookings = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load bookings.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async fetchPayments() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/admin/payments');
                this.payments = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load payments.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        }
    }
});
