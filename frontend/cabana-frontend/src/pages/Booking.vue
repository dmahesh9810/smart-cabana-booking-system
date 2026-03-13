<template>
  <div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
      
      <button @click="router.back()"
        class="flex items-center text-indigo-600 hover:text-indigo-800 mb-8 font-medium transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd"
            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
            clip-rule="evenodd" />
        </svg>
        Back
      </button>

      <div class="mb-8">
        <h1 class="text-3xl font-extrabold text-slate-900">Complete Your Booking</h1>
        <p class="text-slate-600 mt-2">Please review your details and confirm the booking.</p>
      </div>

      <!-- Loading State for Fetching Cabana Info -->
      <div v-if="cabanaStore.loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <div v-else-if="cabana" class="grid grid-cols-1 md:grid-cols-3 gap-8">
        
        <!-- Left Column: Form -->
        <div class="md:col-span-2">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Guest Information</h2>
            
            <form @submit.prevent="submitBooking" class="space-y-5">
              
              <!-- Guest Name -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                <input type="text" v-model="form.guest_name" required
                  class="w-full border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3"
                  placeholder="John Doe" />
              </div>

              <!-- Guest Email -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                <input type="email" v-model="form.guest_email" required
                  class="w-full border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3"
                  placeholder="john@example.com" />
              </div>

              <!-- Number of Guests -->
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Number of Guests</label>
                <input type="number" v-model.number="form.guests" required min="1" :max="cabana.max_guests"
                  class="w-full border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-2.5 px-3" />
                <p class="text-xs text-slate-500 mt-1">Maximum capacity is {{ cabana.max_guests }} persons.</p>
              </div>

              <div v-if="bookingStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg text-sm border border-red-200">
                {{ bookingStore.error }}
              </div>
              
              <div v-if="successMessage" class="bg-green-50 text-green-700 p-4 rounded-lg text-sm border border-green-200 mb-4 font-semibold">
                {{ successMessage }}
              </div>

              <button 
                type="submit" 
                :disabled="!isFormValid || bookingStore.loading"
                class="w-full bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold py-3.5 rounded-xl shadow-md transition-all flex justify-center items-center mt-6"
              >
                <span v-if="bookingStore.loading" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full mr-2"></span>
                {{ successMessage ? 'Booking Confirmed' : 'Confirm Booking' }}
              </button>
            </form>
          </div>
        </div>

        <!-- Right Column: Summary Card -->
        <div class="md:col-span-1">
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 sticky top-6">
            <h3 class="text-lg font-bold text-slate-900 mb-4 border-b border-slate-100 pb-4">Booking Summary</h3>
            
            <div class="flex items-center space-x-4 mb-6">
              <img v-if="cabana.image" :src="cabana.image" class="w-16 h-16 rounded-lg object-cover" />
              <div class="flex-1">
                <h4 class="font-bold text-slate-900 truncate">{{ cabana.name }}</h4>
                <p class="text-sm text-slate-500">📍 {{ cabana.location }}</p>
              </div>
            </div>

            <div class="space-y-3 text-sm text-slate-600 mb-6 font-medium">
              <div class="flex justify-between">
                <span>Check-in</span>
                <span class="text-slate-900">{{ form.check_in_date || 'Not selected' }}</span>
              </div>
              <div class="flex justify-between">
                <span>Check-out</span>
                <span class="text-slate-900">{{ form.check_out_date || 'Not selected' }}</span>
              </div>
            </div>
            
            <div class="border-t border-slate-100 pt-4 space-y-3">
              <div class="flex justify-between text-slate-600">
                <span>${{ cabana.price_per_night }} × {{ nights }} nights</span>
                <span>${{ subtotal }}</span>
              </div>
              
              <div class="flex justify-between text-lg font-bold text-slate-900 pt-2 border-t border-slate-100 mt-2">
                <span>Total</span>
                <span>${{ totalPrice }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

      <!-- Missing Params Fallback -->
      <div v-else class="text-center py-20 bg-white rounded-2xl shadow-sm">
        <p class="text-xl text-slate-600">Missing cabana information for booking.</p>
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

const route = useRoute();
const router = useRouter();
const cabanaStore = useCabanaStore();
const bookingStore = useBookingStore();

const cabana = computed(() => cabanaStore.currentCabana);
const successMessage = ref('');

const form = ref({
    cabana_id: route.query.cabana_id || null,
    check_in_date: route.query.check_in_date || '',
    check_out_date: route.query.check_out_date || '',
    guest_name: '',
    guest_email: '',
    guests: 1
});

// Computed dates calculation
const nights = computed(() => {
    if (!form.value.check_in_date || !form.value.check_out_date) return 0;
    
    // Check-out must be strictly after check-in
    const start = new Date(form.value.check_in_date);
    const end = new Date(form.value.check_out_date);
    if (end <= start) return 0;

    return Math.max(
        1,
        Math.ceil(
            (end - start) / (1000 * 60 * 60 * 24)
        )
    );
});

const subtotal = computed(() => {
    if (!cabana.value || nights.value <= 0) return 0;
    return (parseFloat(cabana.value.price_per_night) * nights.value).toFixed(2);
});

const totalPrice = computed(() => {
    return subtotal.value; // For now, no taxes added
});

// Form validation
const isFormValid = computed(() => {
    return (
        form.value.guest_name &&
        /\S+@\S+\.\S+/.test(form.value.guest_email) &&
        form.value.guests > 0 &&
        cabana.value &&
        form.value.guests <= cabana.value.max_guests &&
        nights.value > 0 &&
        form.value.check_in_date &&
        form.value.check_out_date
    );
});

onMounted(() => {
    if (form.value.cabana_id) {
        // Fetch cabana info to show details in the summary
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
            guests_count: form.value.guests
        });
        
        successMessage.value = 'Booking reserved! Redirecting to payment...';
        
        // Use response.id (or response.data.id depending on API payload wrap)
        const newBookingId = response.data?.id || response.id;

        setTimeout(() => {
            router.push({ name: 'Payment', query: { booking_id: newBookingId } });
        }, 1500);
        
    } catch (e) {
        // Error is handled inside the store
        console.error("Booking Creation Failed", e);
    }
};
</script>
