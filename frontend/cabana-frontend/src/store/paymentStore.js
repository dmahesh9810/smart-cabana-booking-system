import { defineStore } from 'pinia';
import api from '../api/axios';

export const usePaymentStore = defineStore('payment', {
    state: () => ({
        paymentPayload: null,
        userPayments: [],
        loading: false,
        error: null,
    }),

    actions: {
        async initiatePayment(bookingId) {
            this.loading = true;
            this.error = null;
            this.paymentPayload = null;

            try {
                const response = await api.post('/payments/initiate', {
                    booking_id: bookingId
                });

                // Expecting backend to return PayHere configuration payload
                // { merchant_id, return_url, cancel_url, notify_url, order_id, items, currency, amount, hash, ... }
                this.paymentPayload = response.data.data ? response.data.data : response.data;
                return this.paymentPayload;
            } catch (err) {
                this.error = err.response?.data?.message || 'Failed to initialize payment.';
                throw err;
            } finally {
                this.loading = false;
            }
        },

        async fetchUserPayments() {
            this.loading = true;
            this.error = null;
            try {
                const response = await api.get('/payments/my-payments');
                this.userPayments = response.data.data ? response.data.data : response.data;
            } catch (err) {
                this.error = 'Failed to load user payments.';
                console.error(err);
            } finally {
                this.loading = false;
            }
        }
    }
});
