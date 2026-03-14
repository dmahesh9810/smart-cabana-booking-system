<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="container mx-auto px-4 max-w-6xl">

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">My Bookings</h1>
        <p class="text-slate-500 mt-1">Manage your upcoming and past cabana reservations.</p>
      </div>

      <!-- Loading -->
      <div v-if="bookingStore.loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error -->
      <div v-else-if="bookingStore.error" class="bg-red-50 text-red-600 p-6 rounded-2xl text-center border border-red-100 shadow-sm">
        {{ bookingStore.error }}
      </div>

      <!-- Empty -->
      <div v-else-if="!bookingStore.userBookings || bookingStore.userBookings.length === 0"
        class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-indigo-50 mb-6">
          <svg class="h-10 w-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-slate-800">No bookings yet</p>
        <p class="mt-2 text-slate-500">You haven't made any cabana reservations.</p>
        <router-link to="/" class="mt-6 inline-block bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-xl shadow-md transition-all">
          Browse Cabanas
        </router-link>
      </div>

      <!-- Bookings Grid -->
      <div v-else class="space-y-4">
        <div
          v-for="booking in bookingStore.userBookings"
          :key="booking.id"
          class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-200"
        >
          <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-6">

            <!-- Cabana image -->
            <div class="w-full sm:w-28 h-24 rounded-xl overflow-hidden bg-slate-100 shrink-0">
              <img
                :src="getCabanaImage(booking)"
                :alt="booking.cabana?.name"
                class="w-full h-full object-cover"
                @error="e => { e.target.src = PLACEHOLDER; e.target.onerror = null; }"
              />
            </div>

            <!-- Main info -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                <div>
                  <h3 class="font-bold text-slate-900 text-lg">{{ booking.cabana?.name || 'Unknown Cabana' }}</h3>
                  <p class="text-xs text-slate-400 font-mono">Ref: {{ booking.booking_ref || `#${booking.id}` }}</p>
                </div>
                <span
                  class="px-3 py-1 text-xs font-bold rounded-full border capitalize shrink-0"
                  :class="statusClass(booking.status)"
                >
                  {{ booking.status || 'pending' }}
                </span>
              </div>

              <div class="flex flex-wrap gap-x-6 gap-y-1.5 text-sm text-slate-600 mb-3">
                <span class="flex items-center">
                  <svg class="w-3.5 h-3.5 mr-1 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <strong>In:</strong>&nbsp;{{ formatDisplayDate(booking.check_in) }}
                </span>
                <span class="flex items-center">
                  <svg class="w-3.5 h-3.5 mr-1 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                  <strong>Out:</strong>&nbsp;{{ formatDisplayDate(booking.check_out) }}
                </span>
                <span class="flex items-center font-semibold text-indigo-700">
                  {{ formatLKR(booking.total_amount) }}
                </span>
              </div>
            </div>

            <!-- Actions -->
            <div class="flex items-center gap-2 shrink-0">
              <router-link
                v-if="!booking.status || booking.status === 'pending'"
                :to="{ path: '/payment', query: { booking_id: booking.id } }"
                class="px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors"
              >
                Pay Now
              </router-link>
              <router-link
                :to="{ name: 'BookingDetails', params: { id: booking.id } }"
                class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors"
              >
                View Details
              </router-link>
            </div>

          </div>
        </div>
      </div>

      <!-- AI Personalized Recommendations -->
      <div class="mt-14">
        <div class="flex items-center mb-8">
          <div class="flex-1 h-px bg-slate-200"></div>
          <p class="mx-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Recommended for You</p>
          <div class="flex-1 h-px bg-slate-200"></div>
        </div>
        <RecommendedForYou />
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useBookingStore } from '../store/bookingStore';
import { formatLKR } from '../utils/currency';
import RecommendedForYou from '../components/RecommendedForYou.vue';

const bookingStore = useBookingStore();
const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

onMounted(() => {
  bookingStore.fetchUserBookings();
});

const getCabanaImage = (booking) => {
  const img = booking.cabana?.primary_image?.image_path || booking.cabana?.image;
  if (img && img.startsWith('http')) return img;
  return PLACEHOLDER;
};

const formatDisplayDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('en-LK', { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusClass = (status) => {
  const map = {
    confirmed: 'bg-green-50 text-green-700 border-green-200',
    completed:  'bg-blue-50  text-blue-700  border-blue-200',
    pending:    'bg-yellow-50 text-yellow-700 border-yellow-200',
    cancelled:  'bg-red-50   text-red-700   border-red-200',
  };
  return map[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};
</script>
