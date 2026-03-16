import { defineStore } from 'pinia';
import api from '../api/axios';

export const useAuthStore = defineStore('auth', {
    state: () => ({
        user: null,
        token: localStorage.getItem('token') || null,
        loading: false,
        error: null,
    }),

    getters: {
        isAuthenticated: (state) => !!state.token,
        isAdmin: (state) => state.user?.role?.name === 'admin',
        isStaff: (state) => state.user?.role?.name === 'staff',
        canManage: (state) => ['admin', 'staff'].includes(state.user?.role?.name),
    },

    actions: {
        async login(credentials) {
            this.loading = true;
            this.error = null;
            try {
                // First initialize CSRF cookie for Sanctum
                await api.get('http://localhost:8000/sanctum/csrf-cookie');

                const response = await api.post('/login', credentials);
                this.token = response.data.data.token;
                this.user = response.data.data.user;

                if (this.token) {
                    localStorage.setItem('token', this.token);
                }
            } catch (err) {
                if (err.response?.status === 422 && err.response?.data?.errors) {
                    const firstError = Object.values(err.response.data.errors)[0][0];
                    this.error = firstError;
                } else {
                    this.error = err.response?.data?.message || 'Login failed';
                }
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async register(userData) {
            this.loading = true;
            this.error = null;
            try {
                // Initialize CSRF cookie
                await api.get('http://localhost:8000/sanctum/csrf-cookie');

                const response = await api.post('/register', userData);
                this.token = response.data.data.token;
                this.user = response.data.data.user;

                if (this.token) {
                    localStorage.setItem('token', this.token);
                }
            } catch (err) {
                if (err.response?.status === 422 && err.response?.data?.errors) {
                    const firstError = Object.values(err.response.data.errors)[0][0];
                    this.error = firstError;
                } else {
                    this.error = err.response?.data?.message || 'Registration failed';
                }
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async fetchUser() {
            if (!this.token) return;
            try {
                const response = await api.get('/user');
                this.user = response.data.data?.user || response.data.user || response.data;
            } catch (error) {
                this.logout();
            }
        },

        async logout() {
            try {
                if (this.token) {
                    await api.post('/logout');
                }
            } finally {
                this.user = null;
                this.token = null;
                localStorage.removeItem('token');
            }
        },

        async updateProfile(profileData) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.put('/user/profile', profileData);
                this.user = response.data.data?.user || response.data.user || response.data;
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to update profile.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async resendVerification() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.post('/email/resend');
                return response.data;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to resend verification link.';
                throw err;
            } finally {
                this.loading = false;
            }
        }
    }
});
