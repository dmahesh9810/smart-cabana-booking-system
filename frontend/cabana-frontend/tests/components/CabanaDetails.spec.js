import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import CabanaDetails from '../../src/pages/CabanaDetails.vue';
import { createTestingPinia } from '@pinia/testing';
import { useCabanaStore } from '../../src/store/cabanaStore';
import { useBookingStore } from '../../src/store/bookingStore';

// Mock vue-router
vi.mock('vue-router', () => ({
    useRoute: () => ({
        params: { id: 1 }
    }),
    useRouter: () => ({
        push: vi.fn(),
        back: vi.fn()
    })
}));

describe('CabanaDetails.vue', () => {
    it('renders cabana details from store', () => {
        const wrapper = mount(CabanaDetails, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        cabana: {
                            currentCabana: { id: 1, name: 'Ocean Cabana', price_per_night: 150, capacity: 4 },
                            loading: false,
                            error: null
                        },
                        booking: {
                            isAvailable: null,
                            loading: false,
                            error: null
                        }
                    }
                })],
                stubs: { 'router-link': true }
            }
        });

        expect(wrapper.text()).toContain('Ocean Cabana');
        expect(wrapper.text()).toContain('Up to 4 Guests');
    });

    it('triggers availability check on button click', async () => {
        const wrapper = mount(CabanaDetails, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        cabana: {
                            currentCabana: { id: 1, name: 'Ocean Cabana', price_per_night: 150, capacity: 4 },
                            loading: false
                        },
                        booking: { loading: false }
                    }
                })],
                stubs: { 'router-link': true }
            }
        });

        const bookingStore = useBookingStore();

        // Set dates in the form
        const inputs = wrapper.findAll('input[type="date"]');
        await inputs[0].setValue('2026-03-10'); // Check In
        await inputs[1].setValue('2026-03-15'); // Check Out

        // Click check availability
        await wrapper.find('button.bg-slate-200').trigger('click');

        expect(bookingStore.checkAvailability).toHaveBeenCalledWith(1, '2026-03-10', '2026-03-15');
    });
});
