<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-10 text-center">
      
      <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 mb-6 border-4 border-green-50 shadow-sm">
        <svg class="h-10 w-10 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
        </svg>
      </div>
      
      <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Payment Successful!</h2>
      <p class="text-slate-600 mb-8 text-lg">
        Thank you for your booking. Your reservation is now fully confirmed.
      </p>
      
      <div class="bg-slate-50 rounded-xl p-4 mb-8 border border-slate-100">
        <p class="text-sm text-slate-500 mb-1">Booking Reference</p>
        <p class="font-mono font-bold text-slate-800 text-lg">#{{ bookingId }}</p>
      </div>

      <router-link to="/dashboard" class="w-full block bg-gradient-to-r from-ocean-600 to-teal-500 hover:from-ocean-700 hover:to-teal-600 text-white font-bold py-3.5 px-4 rounded-xl transition duration-150 ease-in-out shadow-md">
        View My Bookings
      </router-link>
      <router-link to="/" class="block mt-4 text-ocean-600 font-medium hover:text-ocean-800 transition-colors">
        Return to Home
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useRoute } from 'vue-router';
import { useBookingStore } from '../store/bookingStore';

const route = useRoute();
const bookingStore = useBookingStore();
const bookingId = route.query.booking_id || 'UNKNOWN';

onMounted(async () => {
    // Refresh the user's bookings list immediately after a successful payment
    // so that the dashboard reflects 'Paid' and 'Confirmed' when they navigate back.
    await bookingStore.fetchUserBookings();
});
</script>
