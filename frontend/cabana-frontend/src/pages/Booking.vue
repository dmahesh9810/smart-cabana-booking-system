<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="container mx-auto px-4 max-w-5xl">

      <button @click="router.back()"
        class="flex items-center text-indigo-600 hover:text-indigo-800 mb-8 font-medium transition-colors group"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 group-hover:-translate-x-0.5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back
      </button>

      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Complete Your Booking</h1>
        <p class="text-slate-500 mt-2">Review your trip details and confirm the reservation.</p>
      </div>

      <!-- Loading State -->
      <div v-if="cabanaStore.loading && !cabana" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <div v-else-if="cabana" class="grid grid-cols-1 md:grid-cols-5 gap-8">

        <!-- Left: Guest Form col-span-3 -->
        <div class="md:col-span-3">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Guest Information</h2>

            <form @submit.prevent="submitBooking" class="space-y-5">
              <!-- Name -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name</label>
                <input type="text" v-model="form.guest_name" required
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-2.5 px-4 transition-all text-sm"
                  placeholder="John Doe" />
              </div>

              <!-- Email -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address</label>
                <input type="email" v-model="form.guest_email" required
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-2.5 px-4 transition-all text-sm"
                  placeholder="john@example.com" />
              </div>

              <!-- Guests -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Number of Guests</label>
                <input type="number" v-model.number="form.guests" required min="1" :max="cabana.max_guests"
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-2.5 px-4 transition-all text-sm" />
                <p class="text-xs text-slate-500 mt-1.5 flex items-center">
                  <svg class="w-3 h-3 mr-1 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                  </svg>
                  Maximum capacity: {{ cabana.max_guests }} persons
                </p>
              </div>

              <!-- Error -->
              <div v-if="bookingStore.error" class="bg-red-50 text-red-600 p-4 rounded-xl text-sm border border-red-200 flex items-start space-x-2">
                <svg class="w-4 h-4 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ bookingStore.error }}</span>
              </div>

              <!-- Success -->
              <div v-if="successMessage" class="bg-green-50 text-green-700 p-4 rounded-xl text-sm border border-green-200 flex items-center space-x-2 font-semibold">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ successMessage }}</span>
              </div>

              <!-- Submit -->
              <button
                type="submit"
                :disabled="!isFormValid || bookingStore.loading"
                class="w-full bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 disabled:cursor-not-allowed text-white font-bold py-4 rounded-xl shadow-md hover:shadow-indigo-500/30 transition-all duration-200 flex justify-center items-center mt-4"
              >
                <span v-if="bookingStore.loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                {{ successMessage ? 'Booking Confirmed ✓' : 'Confirm & Proceed to Payment' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Right: Summary col-span-2 -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
            <h3 class="text-lg font-bold text-slate-900 mb-5 pb-4 border-b border-slate-100">Booking Summary</h3>

            <!-- Cabana identity -->
            <div class="flex items-start space-x-4 mb-5">
              <div class="w-20 h-16 rounded-xl overflow-hidden bg-slate-100 shrink-0">
                <img :src="summaryImage" :alt="cabana.name" class="w-full h-full object-cover" @error="onSummaryImgError" />
              </div>
              <div class="flex-1 min-w-0">
                <h4 class="font-bold text-slate-900 truncate">{{ cabana.name }}</h4>
                <p class="text-sm text-slate-500 flex items-center mt-0.5">
                  <svg class="w-3.5 h-3.5 mr-1 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                  {{ cabana.location || 'N/A' }}
                </p>
              </div>
            </div>

            <!-- Dates -->
            <div class="space-y-3 text-sm mb-5">
              <div class="flex justify-between text-slate-600">
                <span class="font-medium">Check-in</span>
                <span class="text-slate-900 font-semibold">{{ formatDate(form.check_in_date) }}</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span class="font-medium">Check-out</span>
                <span class="text-slate-900 font-semibold">{{ formatDate(form.check_out_date) }}</span>
              </div>
              <div class="flex justify-between text-slate-600">
                <span class="font-medium">Duration</span>
                <span class="text-slate-900 font-semibold">{{ nights }} {{ nights === 1 ? 'night' : 'nights' }}</span>
              </div>
            </div>

            <!-- Price breakdown -->
            <div class="border-t border-slate-100 pt-4 space-y-3">
              <div class="flex justify-between text-sm text-slate-600">
                <span>{{ formatLKRShort(cabana.price_per_night) }} × {{ nights }} nights</span>
                <span>{{ formatLKR(subtotal) }}</span>
              </div>
              <div class="flex justify-between text-base font-extrabold text-slate-900 pt-3 border-t border-slate-100">
                <span>Total</span>
                <span class="text-indigo-700">{{ formatLKR(totalPrice) }}</span>
              </div>
            </div>

            <p class="text-xs text-slate-400 mt-4 text-center">Price is shown in Sri Lankan Rupees (LKR)</p>
          </div>
        </div>

      </div>

      <!-- Fallback if no cabana -->
      <div v-else-if="!cabanaStore.loading" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-xl text-slate-600 font-medium">Cabana information not found.</p>
        <router-link to="/" class="mt-4 inline-block text-indigo-600 underline">Return to Home</router-link>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCabanaStore } from '../store/cabanaStore';
import { useBookingStore } from '../store/bookingStore';
import { formatLKR, formatLKRShort } from '../utils/currency';

const route = useRoute();
const router = useRouter();
const cabanaStore = useCabanaStore();
const bookingStore = useBookingStore();

const cabana = computed(() => cabanaStore.currentCabana);
const successMessage = ref('');

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';
const summaryImgFailed = ref(false);
const summaryImage = computed(() => {
  if (summaryImgFailed.value || !cabana.value?.image) return PLACEHOLDER;
  return cabana.value.image;
});
const onSummaryImgError = () => { summaryImgFailed.value = true; };

const form = ref({
  cabana_id: route.query.cabana_id || null,
  check_in_date: route.query.check_in_date || '',
  check_out_date: route.query.check_out_date || '',
  guest_name: '',
  guest_email: '',
  guests: 1,
});

// Date helpers
const formatDate = (dateStr) => {
  if (!dateStr) return 'Not selected';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('en-LK', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
};

// Night / price calculations
const nights = computed(() => {
  if (!form.value.check_in_date || !form.value.check_out_date) return 0;
  const start = new Date(form.value.check_in_date);
  const end = new Date(form.value.check_out_date);
  if (end <= start) return 0;
  return Math.round((end - start) / (1000 * 60 * 60 * 24));
});

const subtotal = computed(() => {
  if (!cabana.value || nights.value <= 0) return 0;
  return parseFloat(cabana.value.price_per_night) * nights.value;
});

const totalPrice = computed(() => subtotal.value);

// Form validation
const isFormValid = computed(() =>
  form.value.guest_name &&
  /\S+@\S+\.\S+/.test(form.value.guest_email) &&
  form.value.guests > 0 &&
  cabana.value &&
  form.value.guests <= cabana.value.max_guests &&
  nights.value > 0 &&
  form.value.check_in_date &&
  form.value.check_out_date
);

onMounted(() => {
  if (form.value.cabana_id) {
    cabanaStore.fetchCabana(form.value.cabana_id);
  }
});

const submitBooking = async () => {
  if (!isFormValid.value || bookingStore.loading) return;
  try {
    const response = await bookingStore.createBooking({
      cabana_id: form.value.cabana_id,
      check_in: form.value.check_in_date,
      check_out: form.value.check_out_date,
      guest_name: form.value.guest_name,
      guest_email: form.value.guest_email,
      guests_count: form.value.guests,
    });
    successMessage.value = 'Booking confirmed! Redirecting to payment...';
    const newBookingId = response.data?.id || response.id;
    setTimeout(() => {
      router.push({ name: 'Payment', query: { booking_id: newBookingId } });
    }, 1500);
  } catch (e) {
    console.error('Booking failed', e);
  }
};
</script>
