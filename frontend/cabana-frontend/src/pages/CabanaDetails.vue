<template>
  <div class="min-h-screen bg-slate-50">

    <!-- Hero Image -->
    <div class="relative h-72 md:h-[420px] w-full bg-slate-900 overflow-hidden">
      <img
        :src="heroImage"
        :alt="cabana?.name"
        class="w-full h-full object-cover opacity-80"
        @error="onHeroImgError"
      />
      <!-- Overlay gradient -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-900/80 via-slate-900/20 to-transparent"></div>

      <!-- Back button -->
      <button @click="router.back()"
        class="absolute top-5 left-5 flex items-center bg-white/90 backdrop-blur-sm text-slate-700 hover:text-indigo-600 px-4 py-2 rounded-xl font-medium text-sm shadow-md transition-all hover:bg-white"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back
      </button>

      <!-- Price overlay -->
      <div v-if="cabana" class="absolute bottom-6 left-6 text-white">
        <p class="text-sm font-medium text-white/70 mb-1">Starting from</p>
        <p class="text-3xl font-extrabold">{{ formatLKRShort(cabana.price_per_night) }}<span class="text-lg font-normal text-white/70"> / night</span></p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="cabanaStore.loading && !cabana" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="cabanaStore.error" class="container mx-auto px-4 max-w-5xl py-8">
      <div class="bg-red-50 text-red-600 p-4 rounded-2xl text-center shadow-sm border border-red-100">
        {{ cabanaStore.error }}
      </div>
    </div>

    <!-- Cabana Content -->
    <div v-else-if="cabana" class="container mx-auto px-4 max-w-5xl py-10">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-8">

        <!-- Left: Details -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">

            <div class="flex items-start justify-between mb-4">
              <h1 class="text-3xl font-extrabold text-slate-900">{{ cabana.name }}</h1>
              <span v-if="cabana.avg_rating" class="flex items-center bg-yellow-50 text-yellow-700 border border-yellow-200 px-3 py-1.5 rounded-xl text-sm font-bold shrink-0 ml-4">
                <svg class="w-4 h-4 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                {{ Number(cabana.avg_rating).toFixed(1) }}
              </span>
            </div>

            <!-- Meta badges -->
            <div class="flex flex-wrap gap-3 mb-6">
              <span class="flex items-center bg-indigo-50 text-indigo-700 border border-indigo-100 px-3 py-1.5 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                Up to {{ cabana.max_guests }} guests
              </span>
              <span v-if="cabana.location" class="flex items-center bg-slate-50 text-slate-700 border border-slate-100 px-3 py-1.5 rounded-xl text-sm font-medium">
                <svg class="w-4 h-4 mr-1.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ cabana.location }}
              </span>
            </div>

            <h3 class="text-lg font-semibold text-slate-900 mb-3">About this cabana</h3>
            <p class="text-slate-600 leading-relaxed whitespace-pre-line">
              {{ cabana.description || 'No description available for this cabana.' }}
            </p>
          </div>

          <!-- Availability Calendar -->
          <CabanaAvailabilityCalendar
            v-if="cabana?.id"
            :cabana-id="cabana.id"
            class="mt-6"
          />

          <!-- Reviews Section -->
          <ReviewList 
            v-if="cabana"
            :reviews="cabana.reviews" 
            :average-rating="cabana.average_rating"
          />
        </div>

        <!-- Right: Booking Panel -->
        <div class="md:col-span-1">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-24">
            <h3 class="text-xl font-bold text-slate-900 mb-5">Book Your Stay</h3>

            <div class="space-y-4 mb-5">
              <!-- Check-in -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Check-in Date</label>
                <input
                  type="date"
                  v-model="bookingForm.checkIn"
                  :min="todayISO()"
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-2.5 px-3 text-sm transition-all"
                  @change="resetAvailability"
                />
              </div>
              <!-- Check-out -->
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Check-out Date</label>
                <input
                  type="date"
                  v-model="bookingForm.checkOut"
                  :min="minCheckout"
                  class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-2.5 px-3 text-sm transition-all"
                  @change="resetAvailability"
                />
              </div>

              <!-- Date validation -->
              <div v-if="dateError" class="text-sm text-red-600 font-medium bg-red-50 px-3 py-2 rounded-lg border border-red-100">
                {{ dateError }}
              </div>

              <!-- Night + Price preview -->
              <div v-if="previewNights > 0" class="bg-indigo-50 rounded-xl p-4 space-y-2 border border-indigo-100">
                <div class="flex justify-between text-sm text-indigo-700">
                  <span>{{ formatLKRShort(cabana.price_per_night) }} × {{ previewNights }} nights</span>
                  <span class="font-semibold">{{ formatLKR(previewTotal) }}</span>
                </div>
                <div class="flex justify-between font-extrabold text-indigo-900 text-base border-t border-indigo-200 pt-2">
                  <span>Total</span>
                  <span>{{ formatLKR(previewTotal) }}</span>
                </div>
              </div>

              <button
                @click="checkAvailability"
                :disabled="!isDatesValid || bookingStore.loading"
                class="w-full bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold py-3 rounded-xl transition-colors mt-1 disabled:opacity-50 flex justify-center items-center text-sm"
              >
                <span v-if="bookingStore.loading" class="animate-spin h-4 w-4 border-2 border-slate-600 border-t-transparent rounded-full mr-2"></span>
                Check Availability
              </button>
            </div>

            <!-- Availability result -->
            <div v-if="bookingStore.isAvailable !== null" class="mb-4 p-4 rounded-xl text-center text-sm font-medium border"
              :class="bookingStore.isAvailable ? 'bg-green-50 text-green-700 border-green-200' : 'bg-red-50 text-red-700 border-red-200'"
            >
              <div class="flex items-center justify-center space-x-2">
                <svg v-if="bookingStore.isAvailable" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span>{{ bookingStore.isAvailable ? 'Available for your dates!' : (bookingStore.error || 'Not available for selected dates') }}</span>
              </div>
            </div>

            <!-- Book Now button -->
            <div v-if="bookingStore.isAvailable">
              <button
                @click="proceedToBooking"
                class="block w-full text-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-bold py-4 rounded-xl shadow-lg hover:shadow-indigo-500/30 transition-all duration-200 transform hover:-translate-y-0.5"
              >
                Proceed to Booking
              </button>
              <p class="text-center text-xs text-slate-500 mt-3">You won't be charged yet</p>
            </div>
          </div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useCabanaStore } from '../store/cabanaStore';
import { useBookingStore } from '../store/bookingStore';
import { formatLKR, formatLKRShort, todayISO } from '../utils/currency';
import CabanaAvailabilityCalendar from '../components/CabanaAvailabilityCalendar.vue';
import ReviewList from '../components/ReviewList.vue';

const route = useRoute();
const router = useRouter();
const cabanaStore = useCabanaStore();
const bookingStore = useBookingStore();

const cabana = computed(() => cabanaStore.currentCabana);

const heroImgFailed = ref(false);
const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const heroImage = computed(() => {
  if (heroImgFailed.value || !cabana.value?.image) return PLACEHOLDER;
  return cabana.value.image;
});
const onHeroImgError = () => { heroImgFailed.value = true; };

const bookingForm = ref({ checkIn: '', checkOut: '' });

// ── Date validation ──────────────────────────────────────────────────────────
const dateError = computed(() => {
  if (!bookingForm.value.checkIn || !bookingForm.value.checkOut) return '';
  if (bookingForm.value.checkOut <= bookingForm.value.checkIn) {
    return 'Check-out date must be after check-in date.';
  }
  return '';
});

const minCheckout = computed(() => {
  if (!bookingForm.value.checkIn) return todayISO();
  // Minimum checkout is the day after checkin
  const d = new Date(bookingForm.value.checkIn);
  d.setDate(d.getDate() + 1);
  return d.toISOString().split('T')[0];
});

const isDatesValid = computed(() =>
  bookingForm.value.checkIn && bookingForm.value.checkOut && !dateError.value
);

// ── Reactive price preview ────────────────────────────────────────────────────
const previewNights = computed(() => {
  if (!bookingForm.value.checkIn || !bookingForm.value.checkOut || dateError.value) return 0;
  const diff = new Date(bookingForm.value.checkOut) - new Date(bookingForm.value.checkIn);
  return Math.max(0, Math.round(diff / (1000 * 60 * 60 * 24)));
});

const previewTotal = computed(() => {
  if (!cabana.value || previewNights.value <= 0) return 0;
  return parseFloat(cabana.value.price_per_night) * previewNights.value;
});

// ── Lifecycle ─────────────────────────────────────────────────────────────────
onMounted(() => {
  const id = route.params.id;
  if (id) cabanaStore.fetchCabana(id);
  bookingStore.isAvailable = null;
  bookingStore.error = null;
});

const resetAvailability = () => {
  bookingStore.isAvailable = null;
  bookingStore.error = null;
};

const checkAvailability = async () => {
  if (!cabana.value || !isDatesValid.value) return;
  await bookingStore.checkAvailability(cabana.value.id, bookingForm.value.checkIn, bookingForm.value.checkOut);
};

const proceedToBooking = () => {
  router.push({
    name: 'Booking',
    query: {
      cabana_id: cabana.value.id,
      check_in_date: bookingForm.value.checkIn,
      check_out_date: bookingForm.value.checkOut,
    }
  });
};
</script>
