<template>
  <div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-5xl">

      <!-- Back Button -->
      <button @click="router.back()"
        class="flex items-center text-indigo-600 hover:text-indigo-800 mb-8 font-medium transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clip-rule="evenodd" />
        </svg>
        Back to Listings
      </button>

      <!-- Loading State -->
      <div v-if="cabanaStore.loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="cabanaStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg text-center shadow-sm">
        {{ cabanaStore.error }}
      </div>

      <!-- Cabana Details -->
      <div v-else-if="cabana" class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-100 flex flex-col md:flex-row">
        
        <!-- Left: Image & Description -->
        <div class="md:w-2/3 flex flex-col border-b md:border-b-0 md:border-r border-slate-100">
          <!-- Image Section -->
          <div class="relative h-64 md:h-96 w-full bg-slate-200">
            <img v-if="cabana.image" :src="cabana.image" :alt="cabana.name"
              class="absolute inset-0 w-full h-full object-cover" />
            <div v-else class="flex justify-center items-center h-full text-slate-400">
              No Image Provided
            </div>

            <div
              class="absolute top-4 left-4 bg-white/90 backdrop-blur-sm px-4 py-2 rounded-lg font-bold text-indigo-600 shadow-sm">
              ${{ cabana.price_per_night }} <span class="text-sm font-normal text-slate-500">/ night</span>
            </div>
          </div>

          <!-- Content Section -->
          <div class="p-8">
            <div class="flex items-center justify-between mb-4">
              <h1 class="text-3xl font-extrabold text-slate-900">{{ cabana.name }}</h1>
            </div>

            <div class="flex flex-wrap gap-4 mb-6 text-sm text-slate-600">
              <div class="flex items-center bg-slate-100 px-3 py-1.5 rounded-full">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-indigo-500" fill="none"
                  viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                </svg>
                <span>Up to {{ cabana.max_guests }} Guests</span>
              </div>

              <div v-if="cabana.location" class="flex items-center bg-slate-100 px-3 py-1.5 rounded-full">
                <span class="mr-1">📍</span>
                <span>{{ cabana.location }}</span>
              </div>
            </div>

            <h3 class="text-lg font-semibold text-slate-900 mb-3">About this cabana</h3>
            <p class="text-slate-600 leading-relaxed mb-8 whitespace-pre-line">
              {{ cabana.description || 'No description available for this cabana.' }}
            </p>
          </div>
        </div>

        <!-- Right: Action & Availability Section -->
        <div class="md:w-1/3 p-8 bg-slate-50 flex flex-col justify-start">
          <h3 class="text-xl font-bold text-slate-900 mb-6">Book Your Stay</h3>
          
          <div class="space-y-4 mb-6 relative">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Check-in Date</label>
              <input 
                type="text" 
                v-model="bookingForm.checkIn" 
                placeholder="YYYY/MM/DD"
                class="w-full border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3"
                @input="resetAvailability"
              />
            </div>
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Check-out Date</label>
              <input 
                type="text" 
                v-model="bookingForm.checkOut" 
                placeholder="YYYY/MM/DD"
                class="w-full border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2 px-3"
                @input="resetAvailability"
              />
            </div>

            <div v-if="dateValidationError" class="text-sm text-red-600 font-medium">
              {{ dateValidationError }}
            </div>

            <button 
              @click="checkAvailability"
              :disabled="!isDatesValid || bookingStore.loading"
              class="w-full bg-slate-200 hover:bg-slate-300 text-slate-800 font-semibold py-3 rounded-lg transition-colors mt-2 disabled:opacity-50 flex justify-center items-center"
            >
              <span v-if="bookingStore.loading" class="animate-spin h-5 w-5 border-2 border-slate-600 border-t-transparent rounded-full mr-2"></span>
              Check Availability
            </button>
          </div>

          <!-- Availability Results -->
          <div v-if="bookingStore.isAvailable !== null" class="mb-6 p-4 rounded-lg text-center"
               :class="bookingStore.isAvailable ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'">
            <p class="font-medium">{{ bookingStore.isAvailable ? 'Available for booking' : 'Not available for selected dates' }}</p>
            <p v-if="bookingStore.error" class="text-sm mt-1">{{ bookingStore.error }}</p>
          </div>

          <!-- Book Now Button (Shown only if available) -->
          <div v-if="bookingStore.isAvailable" class="pt-4 border-t border-slate-200">
            <button 
              @click="proceedToBooking"
              class="block w-full text-center bg-gradient-to-r from-indigo-600 to-indigo-700 hover:from-indigo-700 hover:to-indigo-800 text-white font-semibold py-4 rounded-xl shadow-lg hover:shadow-indigo-500/30 transition-all duration-200 transform hover:-translate-y-0.5"
            >
              Book Now
            </button>
            <p class="text-center text-xs text-slate-500 mt-3">You won't be charged yet</p>
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

const route = useRoute();
const router = useRouter();
const cabanaStore = useCabanaStore();
const bookingStore = useBookingStore();

const cabana = computed(() => cabanaStore.currentCabana);

const bookingForm = ref({
    checkIn: '',
    checkOut: ''
});

// Computed properties for date validations
const dateValidationError = computed(() => {
    const regex = /^\d{4}\/\d{2}\/\d{2}$/;
    if (!bookingForm.value.checkIn && !bookingForm.value.checkOut) return '';
    if (bookingForm.value.checkIn && !regex.test(bookingForm.value.checkIn)) return 'Date format must be YYYY/MM/DD';
    if (bookingForm.value.checkOut && !regex.test(bookingForm.value.checkOut)) return 'Date format must be YYYY/MM/DD';
    return '';
});

const isDatesValid = computed(() => {
    return bookingForm.value.checkIn && bookingForm.value.checkOut && !dateValidationError.value;
});

onMounted(() => {
    const cabanaId = route.params.id;
    if (cabanaId) {
        cabanaStore.fetchCabana(cabanaId);
    }
    // Reset availability state on load
    bookingStore.isAvailable = null;
    bookingStore.error = null;
});

const resetAvailability = () => {
    bookingStore.isAvailable = null;
    bookingStore.error = null;
};

const checkAvailability = async () => {
    if (!cabana.value || !isDatesValid.value) return;

    const checkInParsed = bookingForm.value.checkIn.replace(/\//g, "-");
    const checkOutParsed = bookingForm.value.checkOut.replace(/\//g, "-");

    await bookingStore.checkAvailability(cabana.value.id, checkInParsed, checkOutParsed);
};

const proceedToBooking = () => {
    // Navigate to booking page, pass details via query or route state
    router.push({
        name: 'Booking',
        query: {
            cabana_id: cabana.value.id,
            check_in_date: bookingForm.value.checkIn.replace(/\//g, "-"),
            check_out_date: bookingForm.value.checkOut.replace(/\//g, "-")
        }
    });
};
</script>
