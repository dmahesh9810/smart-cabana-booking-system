<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-lg p-10 text-center">
      
      <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-red-50 mb-6 border-4 border-red-100 shadow-sm">
        <svg class="h-10 w-10 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
        </svg>
      </div>
      
      <h2 class="text-3xl font-extrabold text-slate-900 mb-4">Payment Cancelled</h2>
      <p class="text-slate-600 mb-8 text-lg">
        It looks like you cancelled the payment process. Your booking (Ref: #{{ bookingId }}) is currently unpaid.
      </p>

      <router-link :to="{ name: 'Payment', query: { booking_id: bookingId } }" class="w-full block bg-slate-800 hover:bg-slate-900 text-white font-bold py-3.5 px-4 rounded-xl transition duration-150 ease-in-out shadow-md mb-4">
        Try Payment Again
      </router-link>
      <router-link to="/" class="block mt-4 text-ocean-600 font-medium hover:text-ocean-800 transition-colors">
        Return to Home
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { useRoute } from 'vue-router';
const route = useRoute();
const bookingId = route.query.booking_id || 'UNKNOWN';
</script>
