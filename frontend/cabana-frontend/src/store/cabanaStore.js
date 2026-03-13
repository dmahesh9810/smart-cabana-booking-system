import { defineStore } from 'pinia';
import api from '../api/axios';

export const useCabanaStore = defineStore('cabana', {
    state: () => ({
        cabanas: [],
        currentCabana: null,
        loading: false,
        error: null,
    }),

    actions: {
        async fetchCabanas() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/cabanas');
                // Laravel typically wraps resources in a `data` key. 
                // We handle both wrapper and root array gracefully.
                this.cabanas = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load cabanas. Please try again later.';
                console.error('Error fetching cabanas:', err);
            } finally {
                this.loading = false;
            }
        },

        async fetchCabana(id) {
            this.loading = true;
            this.error = null;
            this.currentCabana = null;
            try {
                const response = await api.get(`/cabanas/${id}`);
                this.currentCabana = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load cabana details. Please try again later.';
                console.error(`Error fetching cabana ${id}:`, err);
            } finally {
                this.loading = false;
            }
        }
    }
});
