<template>
  <div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">My Bookings</h1>
        <p class="text-slate-600 mt-2">Manage all your upcoming and past cabana reservations.</p>
      </div>

      <!-- Loading State -->
      <div v-if="bookingStore.loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="bookingStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg text-center shadow-sm">
        {{ bookingStore.error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="!bookingStore.userBookings || bookingStore.userBookings.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-slate-100 mb-4">
          <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
          </svg>
        </div>
        <p class="text-xl font-medium text-slate-900">No bookings yet</p>
        <p class="mt-2 text-slate-500">You haven't made any cabana reservations.</p>
        <router-link to="/" class="mt-6 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-colors">
          Browse Cabanas
        </router-link>
      </div>

      <!-- Bookings List -->
      <div v-else class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-100">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 text-slate-600 text-sm uppercase tracking-wider border-b border-slate-200">
                <th class="p-4 font-semibold">Cabana</th>
                <th class="p-4 font-semibold">Dates</th>
                <th class="p-4 font-semibold">Total Price</th>
                <th class="p-4 font-semibold">Payment Status</th>
                <th class="p-4 font-semibold">Booking Status</th>
                <th class="p-4 font-semibold text-right">Action</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="booking in bookingStore.userBookings" 
                :key="booking.id" 
                class="hover:bg-slate-50 transition-colors"
              >
                <td class="p-4">
                  <div class="font-bold text-slate-900">{{ booking.cabana?.name || 'Unknown Cabana' }}</div>
                  <div class="text-xs text-slate-500">Booking #{{ booking.id }}</div>
                </td>
                <td class="p-4 text-slate-600 text-sm">
                  <div>In: <span class="font-medium text-slate-800">{{ booking.check_in_date }}</span></div>
                  <div>Out: <span class="font-medium text-slate-800">{{ booking.check_out_date }}</span></div>
                </td>
                <td class="p-4 text-slate-900 font-medium">
                  ${{ booking.total_price }}
                </td>
                <td class="p-4">
                  <span 
                    class="px-3 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-700': booking.payment_status === 'paid',
                      'bg-yellow-100 text-yellow-700': booking.payment_status === 'pending',
                      'bg-red-100 text-red-700': booking.payment_status === 'failed' || booking.payment_status === 'cancelled'
                    }"
                  >
                    {{ booking.payment_status || 'Pending' }}
                  </span>
                </td>
                <td class="p-4">
                  <span 
                    class="px-3 py-1 text-xs font-semibold rounded-full"
                    :class="{
                      'bg-green-100 text-green-700': booking.booking_status === 'confirmed',
                      'bg-blue-100 text-blue-700': booking.booking_status === 'completed',
                      'bg-yellow-100 text-yellow-700': booking.booking_status === 'pending',
                      'bg-red-100 text-red-700': booking.booking_status === 'cancelled'
                    }"
                  >
                    {{ booking.booking_status || 'Pending' }}
                  </span>
                </td>
                <td class="p-4 text-right space-x-2">
                  <router-link 
                    v-if="booking.payment_status === 'pending'"
                    :to="{ path: '/payment', query: { booking_id: booking.id } }" 
                    class="inline-block text-white font-medium text-sm transition-colors bg-indigo-600 hover:bg-indigo-700 px-4 py-2 rounded-lg shadow-sm"
                  >
                    Pay Now
                  </router-link>

                  <router-link 
                    :to="{ name: 'BookingDetails', params: { id: booking.id } }" 
                    class="inline-block text-indigo-600 hover:text-indigo-800 font-medium text-sm transition-colors bg-indigo-50 hover:bg-indigo-100 px-4 py-2 rounded-lg"
                  >
                    View Details
                  </router-link>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useBookingStore } from '../store/bookingStore';

const bookingStore = useBookingStore();

onMounted(() => {
    bookingStore.fetchUserBookings();
});
</script>
