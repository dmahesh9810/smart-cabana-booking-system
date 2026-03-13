import { mount } from '@vue/test-utils';
import { describe, it, expect, vi } from 'vitest';
import Booking from '../../src/pages/Booking.vue';
import { createTestingPinia } from '@pinia/testing';
import { useCabanaStore } from '../../src/store/cabanaStore';
import { useBookingStore } from '../../src/store/bookingStore';

// Mock vue-router
const pushMock = vi.fn();
vi.mock('vue-router', () => ({
    useRoute: () => ({
        query: { cabana_id: 1, check_in_date: '2026-03-10', check_out_date: '2026-03-12' }
    }),
    useRouter: () => ({
        push: pushMock,
        back: vi.fn()
    })
}));

describe('Booking.vue', () => {
    it('renders booking summary correctly', () => {
        const wrapper = mount(Booking, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        cabana: {
                            currentCabana: { id: 1, name: 'Ocean Cabana', price_per_night: 150, capacity: 4 }
                        },
                        booking: {}
                    }
                })],
                stubs: { 'router-link': true }
            }
        });

        // Subtotal should be $150 * 2 nights = 300
        expect(wrapper.text()).toContain('Ocean Cabana');
        expect(wrapper.text()).toContain('$300');
    });

    it('submits booking successfully', async () => {
        const wrapper = mount(Booking, {
            global: {
                plugins: [createTestingPinia({
                    initialState: {
                        cabana: {
                            currentCabana: { id: 1, name: 'Ocean Cabana', price_per_night: 150, capacity: 4 }
                        },
                        booking: {}
                    }
                })],
                stubs: { 'router-link': true }
            }
        });

        const bookingStore = useBookingStore();

        // Fill out form
        await wrapper.find('input[type="text"]').setValue('John Doe');
        await wrapper.find('input[type="email"]').setValue('john@example.com');
        await wrapper.find('input[type="number"]').setValue(2);

        // Mock store action
        bookingStore.createBooking = vi.fn().mockResolvedValue({});

        // Submit form
        await wrapper.find('form').trigger('submit.prevent');

        expect(bookingStore.createBooking).toHaveBeenCalledWith({
            cabana_id: 1,
            check_in_date: '2026-03-10',
            check_out_date: '2026-03-12',
            guest_name: 'John Doe',
            guest_email: 'john@example.com',
            guests: 2
        });

        // Check success message
        await wrapper.vm.$nextTick(); // Wait for state change
        expect(wrapper.text()).toContain('Your booking has been created successfully!');
    });
});
