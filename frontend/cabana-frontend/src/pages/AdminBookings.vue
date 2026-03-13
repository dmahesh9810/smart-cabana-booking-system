<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Bookings Overview</h1>
        <p class="text-slate-500 text-sm mt-1">Review and manage all customer reservations.</p>
      </div>
    </div>

    <!-- Error/Loading -->
    <div v-if="adminStore.error" class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-200">
      {{ adminStore.error }}
    </div>

    <div v-if="adminStore.loading && !bookings.length" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Bookings Table -->
    <div v-else class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 text-slate-600 text-sm uppercase tracking-wider border-b border-slate-200">
                <th class="p-4 font-semibold">Booking ID</th>
                <th class="p-4 font-semibold">User</th>
                <th class="p-4 font-semibold">Cabana</th>
                <th class="p-4 font-semibold">Dates</th>
                <th class="p-4 font-semibold">Payment Status</th>
                <th class="p-4 font-semibold">Booking Status</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="booking in bookings" 
                :key="booking.id" 
                class="hover:bg-slate-50 transition-colors"
              >
                <td class="p-4">
                  <div class="font-bold text-slate-900">#{{ booking.id }}</div>
                </td>
                <td class="p-4">
                  <div class="text-sm font-semibold text-slate-800">{{ booking.user?.name || booking.guest_name || 'Guest' }}</div>
                  <div class="text-xs text-slate-500">{{ booking.user?.email || booking.guest_email || 'No email' }}</div>
                </td>
                <td class="p-4">
                  <div class="text-sm font-medium text-slate-800">{{ booking.cabana?.name || 'Unknown' }}</div>
                </td>
                <td class="p-4 text-slate-600 text-sm">
                  <div>In: <span class="font-medium text-slate-800">{{ booking.check_in_date }}</span></div>
                  <div>Out: <span class="font-medium text-slate-800">{{ booking.check_out_date }}</span></div>
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
              </tr>
              <tr v-if="!bookings.length">
                 <td colspan="6" class="py-12 text-center text-slate-500">No bookings found in the system.</td>
              </tr>
            </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue';
import { useAdminStore } from '../store/adminStore';

const adminStore = useAdminStore();
const bookings = computed(() => adminStore.bookings);

onMounted(() => {
    adminStore.fetchBookings();
});
</script>
