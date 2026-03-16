<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="container mx-auto px-4 max-w-4xl">

      <!-- Back Button -->
      <button @click="router.back()" class="flex items-center text-ocean-600 hover:text-ocean-800 mb-8 font-medium transition-colors group">
        <svg class="h-5 w-5 mr-1.5 group-hover:-translate-x-0.5 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
        </svg>
        Back
      </button>

      <!-- Header -->
      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Complete Your Booking</h1>
        <p class="text-slate-500 mt-1">Step 2 of 3 — Enter your details to confirm your reservation.</p>
        <!-- Progress -->
        <div class="flex gap-1 mt-4">
          <div class="h-1.5 rounded-full bg-teal-500 flex-1"></div>
          <div class="h-1.5 rounded-full bg-ocean-600 flex-1"></div>
          <div class="h-1.5 rounded-full bg-slate-200 flex-1"></div>
        </div>
      </div>

      <!-- Loading -->
      <div v-if="loadingCabana" class="text-center py-20">
        <div class="animate-spin h-12 w-12 border-b-2 border-ocean-600 rounded-full mx-auto"></div>
      </div>

      <div v-else-if="cabana" class="grid grid-cols-1 md:grid-cols-5 gap-8">

        <!-- Guest Details Form -->
        <div class="md:col-span-3">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Guest Information</h2>
            <form @submit.prevent="handleBooking" class="space-y-5">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name *</label>
                <input v-model="form.guest_name" type="text" required
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-ocean-500 focus:ring-2 focus:ring-ocean-200 py-3 px-4 text-sm transition-all"
                  placeholder="John Perera"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address *</label>
                <input v-model="form.guest_email" type="email" required
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-ocean-500 focus:ring-2 focus:ring-ocean-200 py-3 px-4 text-sm transition-all"
                  placeholder="john@example.com"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone Number</label>
                <input v-model="form.guest_phone" type="tel"
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-ocean-500 focus:ring-2 focus:ring-ocean-200 py-3 px-4 text-sm transition-all"
                  placeholder="+94 77 000 0000"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Number of Guests *</label>
                <select v-model="form.guests" required
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-ocean-500 focus:ring-2 focus:ring-ocean-200 py-3 px-4 bg-white text-sm transition-all">
                  <option v-for="n in maxGuests" :key="n" :value="n">{{ n }} {{ n === 1 ? 'Guest' : 'Guests' }}</option>
                </select>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Special Requests (optional)</label>
                <textarea v-model="form.special_requests" rows="3"
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-ocean-500 focus:ring-2 focus:ring-ocean-200 py-3 px-4 text-sm resize-none transition-all"
                  placeholder="Any special requirements, dietary needs..."></textarea>
              </div>

              <div v-if="bookingStore.error" class="bg-red-50 text-red-600 px-4 py-3 rounded-xl text-sm border border-red-200">
                {{ bookingStore.error }}
              </div>

              <button type="submit" :disabled="bookingStore.loading"
                class="w-full bg-gradient-to-r from-ocean-600 to-teal-500 hover:from-ocean-700 hover:to-teal-600 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-ocean-500/30 transition-all flex items-center justify-center gap-2 disabled:opacity-60">
                <span v-if="bookingStore.loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full"></span>
                {{ bookingStore.loading ? 'Processing...' : 'Confirm & Proceed to Payment' }}
              </button>
              <p class="text-center text-xs text-slate-400">By proceeding, you agree to our terms and booking policy.</p>
            </form>
          </div>
        </div>

        <!-- Booking Summary -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
            <!-- Cabana image -->
            <div class="h-40 rounded-xl overflow-hidden mb-5 bg-slate-100">
              <img :src="cabanaImage" :alt="cabana.name" class="w-full h-full object-cover"
                @error="e => { e.target.src = PLACEHOLDER; e.target.onerror = null; }"/>
            </div>
            <h3 class="text-base font-bold text-slate-900 mb-1">{{ cabana.name }}</h3>
            <p v-if="cabana.location" class="flex items-center text-sm text-slate-500 mb-5">
              <svg class="w-3.5 h-3.5 mr-1 text-ocean-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
              </svg>
              {{ cabana.location }}
            </p>

            <div class="space-y-3 text-sm border-t border-slate-100 pt-5">
              <div class="flex justify-between">
                <span class="text-slate-500">Check-in</span>
                <span class="font-semibold text-slate-800">{{ formatDate(checkIn) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Check-out</span>
                <span class="font-semibold text-slate-800">{{ formatDate(checkOut) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500">Duration</span>
                <span class="font-semibold text-slate-800">{{ nights }} {{ nights === 1 ? 'night' : 'nights' }}</span>
              </div>
            </div>

            <div class="border-t border-slate-100 mt-4 pt-4 space-y-2">
              <div class="flex justify-between text-sm">
                <span class="text-slate-500">{{ formatLKRShort(cabana.price_per_night) }} × {{ nights }} nights</span>
                <span class="text-slate-800">{{ formatLKR(subtotal) }}</span>
              </div>
              <div class="flex justify-between font-bold text-lg text-slate-900 border-t border-slate-100 pt-3 mt-2">
                <span>Total</span>
                <span class="text-ocean-700">{{ formatLKR(subtotal) }}</span>
              </div>
            </div>

            <div class="mt-5 flex items-center gap-2 text-xs text-slate-500 bg-ocean-50 rounded-xl p-3 border border-ocean-100">
              <svg class="w-4 h-4 text-ocean-500 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
              </svg>
              Secure payment via PayHere
            </div>
          </div>
        </div>
      </div>

      <div v-else-if="!loadingCabana" class="text-center py-16 text-slate-500">
        Unable to load cabana details. Please go back and try again.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCabanaStore } from '../store/cabanaStore';
import { useBookingStore } from '../store/bookingStore';
import { useAuthStore } from '../store/authStore';
import { formatLKR, formatLKRShort } from '../utils/currency';
import api from '../api/axios';

const route        = useRoute();
const router       = useRouter();
const cabanaStore  = useCabanaStore();
const bookingStore = useBookingStore();
const authStore    = useAuthStore();

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const checkIn  = computed(() => route.query.check_in_date || '');
const checkOut = computed(() => route.query.check_out_date || '');
const cabanaId = computed(() => route.query.cabana_id);

const cabana       = computed(() => cabanaStore.currentCabana);
const loadingCabana = ref(false);

const form = ref({
  guest_name: '',
  guest_email: '',
  guest_phone: '',
  guests: 1,
  special_requests: '',
});

const maxGuests = computed(() => {
  const max = parseInt(cabana.value?.max_guests) || 10;
  return Array.from({ length: max }, (_, i) => i + 1);
});

const nights = computed(() => {
  if (!checkIn.value || !checkOut.value) return 0;
  return Math.max(0, Math.round((new Date(checkOut.value) - new Date(checkIn.value)) / 86_400_000));
});

const subtotal = computed(() => {
  if (!cabana.value || nights.value <= 0) return 0;
  return parseFloat(cabana.value.price_per_night) * nights.value;
});

const cabanaImage = computed(() => {
  const img = cabana.value?.primary_image?.image_path || cabana.value?.image;
  return img?.startsWith('http') ? img : PLACEHOLDER;
});

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  return new Date(dateStr + 'T00:00:00').toLocaleDateString('en-LK', { weekday: 'short', month: 'short', day: 'numeric', year: 'numeric' });
};

const handleBooking = async () => {
  if (!cabanaId.value || !checkIn.value || !checkOut.value) return;
  bookingStore.error = null;
  try {
    const payload = {
      cabana_id:        parseInt(cabanaId.value),
      check_in:         checkIn.value,
      check_out:        checkOut.value,
      guests_count:     form.value.guests,
      total_amount:     subtotal.value,
      guest_name:       form.value.guest_name,
      guest_email:      form.value.guest_email,
      guest_phone:      form.value.guest_phone,
      special_requests: form.value.special_requests,
    };
    const response = await bookingStore.createBooking(payload);
    const bookingId = response?.data?.id || bookingStore.currentBooking?.id;
    if (bookingId) {
      router.push({ path: '/payment', query: { booking_id: bookingId } });
    }
  } catch { /* error shown via bookingStore.error */ }
};

onMounted(async () => {
  // Pre-fill from auth user
  if (authStore.user) {
    form.value.guest_name  = authStore.user.name  || '';
    form.value.guest_email = authStore.user.email || '';
    form.value.guest_phone = authStore.user.phone || '';
  }

  // Load cabana details
  if (cabanaId.value) {
    loadingCabana.value = true;
    await cabanaStore.fetchCabana(cabanaId.value);
    loadingCabana.value = false;
  }
  bookingStore.error = null;
});
</script>
