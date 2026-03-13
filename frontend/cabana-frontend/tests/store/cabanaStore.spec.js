import { setActivePinia, createPinia } from 'pinia';
import { useCabanaStore } from '../../src/store/cabanaStore';
import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import api from '../../src/api/axios';
import MockAdapter from 'axios-mock-adapter';

describe('Cabana Store', () => {
    let mock;
    let cabanaStore;

    beforeEach(() => {
        setActivePinia(createPinia());
        cabanaStore = useCabanaStore();
        mock = new MockAdapter(api);
    });

    afterEach(() => {
        mock.reset();
    });

    it('fetches cabanas successfully', async () => {
        const mockData = [
            { id: 1, name: 'Ocean Cabana', price_per_night: 150 },
            { id: 2, name: 'Mountain Cabana', price_per_night: 100 }
        ];

        mock.onGet('/cabanas').reply(200, { data: mockData });

        await cabanaStore.fetchCabanas();

        expect(cabanaStore.loading).toBe(false);
        expect(cabanaStore.error).toBeNull();
        expect(cabanaStore.cabanas).toHaveLength(2);
        expect(cabanaStore.cabanas[0].name).toBe('Ocean Cabana');
    });

    it('handles error when fetching cabanas', async () => {
        mock.onGet('/cabanas').reply(500);

        await cabanaStore.fetchCabanas();

        expect(cabanaStore.loading).toBe(false);
        expect(cabanaStore.cabanas).toHaveLength(0);
        expect(cabanaStore.error).toBe('Failed to load cabanas. Please try again later.');
    });

    it('fetches a single cabana successfully', async () => {
        const mockCabana = { id: 1, name: 'Ocean Cabana', price_per_night: 150 };

        mock.onGet('/cabanas/1').reply(200, { data: mockCabana });

        await cabanaStore.fetchCabana(1);

        expect(cabanaStore.loading).toBe(false);
        expect(cabanaStore.error).toBeNull();
        expect(cabanaStore.currentCabana).toEqual(mockCabana);
    });
});
