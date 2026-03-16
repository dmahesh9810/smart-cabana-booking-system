import { defineStore } from 'pinia';
import api from '../api/axios';

export const useCabanaStore = defineStore('cabana', {
    state: () => ({
        cabanas: [],
        filteredCabanas: [],
        currentCabana: null,
        cabanaImages: [],
        loading: false,
        error: null,
        // Search/filter state
        searchQuery: '',
        filters: {
            minPrice: '',
            maxPrice: '',
            minGuests: '',
            location: '',
        },
        sortBy: 'default',
    }),

    getters: {
        displayedCabanas(state) {
            let list = [...state.cabanas];

            // Search by name or location
            if (state.searchQuery.trim()) {
                const q = state.searchQuery.toLowerCase();
                list = list.filter(c =>
                    c.name?.toLowerCase().includes(q) ||
                    c.location?.toLowerCase().includes(q) ||
                    c.description?.toLowerCase().includes(q)
                );
            }

            // Filter by price
            if (state.filters.minPrice !== '') {
                list = list.filter(c => parseFloat(c.price_per_night) >= parseFloat(state.filters.minPrice));
            }
            if (state.filters.maxPrice !== '') {
                list = list.filter(c => parseFloat(c.price_per_night) <= parseFloat(state.filters.maxPrice));
            }

            // Filter by capacity
            if (state.filters.minGuests !== '') {
                list = list.filter(c => parseInt(c.max_guests) >= parseInt(state.filters.minGuests));
            }

            // Filter by location
            if (state.filters.location) {
                list = list.filter(c => c.location?.toLowerCase().includes(state.filters.location.toLowerCase()));
            }

            // Sort
            switch (state.sortBy) {
                case 'price_asc':
                    list.sort((a, b) => parseFloat(a.price_per_night) - parseFloat(b.price_per_night));
                    break;
                case 'price_desc':
                    list.sort((a, b) => parseFloat(b.price_per_night) - parseFloat(a.price_per_night));
                    break;
                case 'rating':
                    list.sort((a, b) => parseFloat(b.avg_rating || 0) - parseFloat(a.avg_rating || 0));
                    break;
                case 'newest':
                    list.sort((a, b) => new Date(b.created_at) - new Date(a.created_at));
                    break;
            }

            return list;
        },
    },

    actions: {
        async fetchCabanas() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/cabanas');
                this.cabanas = response.data.data ?? response.data;
            } catch (err) {
                this.error = 'Unable to load cabanas. Please try again.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async fetchCabana(id) {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get(`/cabanas/${id}`);
                this.currentCabana = response.data.data ?? response.data;
            } catch (err) {
                this.error = 'Unable to load cabana details.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        },

        async fetchCabanaImages(id) {
            try {
                const response = await api.get(`/cabanas/${id}/images`);
                this.cabanaImages = response.data.data ?? response.data ?? [];
            } catch (err) {
                this.cabanaImages = [];
            }
        },

        setSearchQuery(q) { this.searchQuery = q; },
        setFilter(key, val) { this.filters[key] = val; },
        setSortBy(val) { this.sortBy = val; },
        resetFilters() {
            this.searchQuery = '';
            this.filters = { minPrice: '', maxPrice: '', minGuests: '', location: '' };
            this.sortBy = 'default';
        },
    },
});
