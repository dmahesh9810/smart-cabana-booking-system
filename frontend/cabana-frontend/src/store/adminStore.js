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
        successMessage: null,
    }),

    actions: {
        _setSuccess(msg) {
            this.successMessage = msg;
            setTimeout(() => { this.successMessage = null; }, 3500);
        },

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
                    // Laravel requires method spoofing for PUT with FormData
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
                // Optimistically update the local list
                const idx = this.cabanas.findIndex(c => c.id === id);
                if (idx !== -1) {
                    this.cabanas[idx] = response.data.data;
                }
                this._setSuccess(response.data.message);
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to toggle cabana status.';
                throw err;
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
