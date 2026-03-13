import { setActivePinia, createPinia } from 'pinia';
import { useBookingStore } from '../../src/store/bookingStore';
import { describe, it, expect, beforeEach, afterEach } from 'vitest';
import api from '../../src/api/axios';
import MockAdapter from 'axios-mock-adapter';

describe('Booking Store', () => {
    let mock;
    let bookingStore;

    beforeEach(() => {
        setActivePinia(createPinia());
        bookingStore = useBookingStore();
        mock = new MockAdapter(api);
    });

    afterEach(() => {
        mock.reset();
    });

    it('checks availability successfully', async () => {
        const cabanaId = 1;
        const checkIn = '2026-03-10';
        const checkOut = '2026-03-15';

        mock.onGet(`/cabanas/${cabanaId}/availability`, {
            params: { check_in_date: checkIn, check_out_date: checkOut }
        }).reply(200, { available: true, message: 'Available for booking' });

        const isAvailable = await bookingStore.checkAvailability(cabanaId, checkIn, checkOut);

        expect(isAvailable).toBe(true);
        expect(bookingStore.isAvailable).toBe(true);
        expect(bookingStore.availabilityMessage).toBe('Available for booking');
        expect(bookingStore.loading).toBe(false);
    });

    it('handles unavailability successfully', async () => {
        const cabanaId = 1;
        const checkIn = '2026-03-10';
        const checkOut = '2026-03-15';

        mock.onGet(`/cabanas/${cabanaId}/availability`, {
            params: { check_in_date: checkIn, check_out_date: checkOut }
        }).reply(400, { message: 'Not available for selected dates' });

        const isAvailable = await bookingStore.checkAvailability(cabanaId, checkIn, checkOut);

        expect(isAvailable).toBe(false);
        expect(bookingStore.isAvailable).toBe(false);
        expect(bookingStore.availabilityMessage).toBe('Not available for selected dates');
    });

    it('creates booking successfully', async () => {
        const payload = {
            cabana_id: 1,
            check_in_date: '2026-03-10',
            check_out_date: '2026-03-15',
            guest_name: 'John Doe',
            guest_email: 'john@example.com',
            guests: 2
        };

        mock.onPost('/bookings', payload).reply(201, {
            message: 'Booking created successfully',
            data: { id: 101, ...payload }
        });

        const response = await bookingStore.createBooking(payload);

        expect(response.message).toBe('Booking created successfully');
        expect(response.data.id).toBe(101);
        expect(bookingStore.loading).toBe(false);
    });

    it('handles booking creation validation errors', async () => {
        const payload = { cabana_id: 1 };

        mock.onPost('/bookings', payload).reply(422, {
            message: 'The given data was invalid.',
            errors: {
                guest_name: ['The guest name field is required.']
            }
        });

        await expect(bookingStore.createBooking(payload)).rejects.toThrow();
        expect(bookingStore.error).toBe('The given data was invalid.');
    });
});
