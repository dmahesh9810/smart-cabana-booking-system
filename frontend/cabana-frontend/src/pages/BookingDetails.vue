<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="container mx-auto px-4 max-w-4xl">

      <!-- Back Button -->
      <button @click="router.back()"
        class="flex items-center text-indigo-600 hover:text-indigo-800 mb-8 font-medium transition-colors group"
      >
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1.5 group-hover:-translate-x-0.5 transition-transform" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Bookings
      </button>

      <!-- Loading -->
      <div v-if="bookingStore.loading && !booking" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error -->
      <div v-else-if="bookingStore.error && !booking" class="bg-red-50 text-red-600 p-6 rounded-2xl text-center border border-red-100">
        {{ bookingStore.error }}
      </div>

      <!-- Booking Details -->
      <div v-else-if="booking" class="space-y-6">

        <!-- Header card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
              <p class="text-sm text-slate-500 font-mono mb-1">{{ booking.booking_ref || `Booking #${booking.id}` }}</p>
              <h1 class="text-3xl font-extrabold text-slate-900">{{ booking.cabana?.name || 'Cabana Booking' }}</h1>
              <p v-if="booking.cabana?.location" class="text-slate-500 mt-1 flex items-center text-sm">
                <svg class="w-4 h-4 mr-1 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                {{ booking.cabana.location }}
              </p>
            </div>
            <span
              class="px-5 py-2 text-sm font-bold rounded-full border capitalize self-start sm:self-auto"
              :class="statusClass(booking.status)"
            >
              {{ booking.status || 'Pending' }}
            </span>
          </div>
        </div>

        <!-- Details grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

          <!-- Reservation card -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7">
            <h2 class="text-lg font-bold text-slate-900 mb-5 pb-4 border-b border-slate-100">Reservation Details</h2>
            <div class="space-y-4 text-sm">
              <div class="flex justify-between">
                <span class="text-slate-500 font-medium">Check-in</span>
                <span class="text-slate-900 font-semibold">{{ formatDisplayDate(booking.check_in) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 font-medium">Check-out</span>
                <span class="text-slate-900 font-semibold">{{ formatDisplayDate(booking.check_out) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 font-medium">Duration</span>
                <span class="text-slate-900 font-semibold">{{ nights }} {{ nights === 1 ? 'night' : 'nights' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-slate-500 font-medium">Guests</span>
                <span class="text-slate-900 font-semibold">{{ booking.guests_count }} {{ booking.guests_count === 1 ? 'Person' : 'Persons' }}</span>
              </div>
            </div>
          </div>

          <!-- Payment card -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-7">
            <h2 class="text-lg font-bold text-slate-900 mb-5 pb-4 border-b border-slate-100">Payment Summary</h2>
            <div class="space-y-4">
              <div class="text-center bg-indigo-50 rounded-xl py-4 border border-indigo-100">
                <p class="text-sm text-indigo-600 font-medium mb-1">Total Amount</p>
                <p class="text-3xl font-extrabold text-indigo-800">{{ formatLKR(booking.total_amount) }}</p>
              </div>
              <div class="flex justify-between text-sm">
                <span class="text-slate-500 font-medium">Payment Status</span>
                <span
                  class="px-3 py-1 text-xs font-bold rounded-full border capitalize"
                  :class="paymentStatusClass(booking.payment?.status)"
                >
                  {{ booking.payment?.status || 'Pending' }}
                </span>
              </div>
              <!-- Pay now if pending -->
              <div v-if="(!booking.payment || booking.payment?.status === 'pending') && booking.status !== 'cancelled'" class="pt-2 flex flex-col gap-2">
                <router-link
                  :to="{ name: 'Payment', query: { booking_id: booking.id } }"
                  class="block w-full text-center bg-gradient-to-r from-ocean-600 to-teal-500 hover:from-ocean-700 hover:to-teal-600 text-white font-bold py-3 rounded-xl shadow-md transition-all"
                >
                  Pay Now
                </router-link>
              </div>
              <div v-if="booking.status === 'pending' || booking.status === 'confirmed'" class="pt-2">
                 <button
                   @click="showCancelModal = true"
                   class="w-full bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-sm font-semibold py-3 rounded-xl transition-colors"
                 >
                   Cancel Booking
                 </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Review Section -->
        <div v-if="booking.status === 'completed'" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
          <div class="flex items-center space-x-3 mb-2">
            <div class="w-10 h-10 bg-yellow-50 rounded-xl flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </div>
            <div>
              <h2 class="text-xl font-bold text-slate-900">Rate Your Stay</h2>
              <p class="text-slate-500 text-sm">We hope you loved your cabana!</p>
            </div>
          </div>

          <div v-if="reviewSuccess" class="bg-green-50 text-green-700 p-4 rounded-xl font-medium border border-green-200 flex items-center space-x-2 mt-6">
            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span>Thank you! Your review has been submitted.</span>
          </div>

          <form v-else @submit.prevent="submitReview" class="space-y-5 mt-6 max-w-2xl">
            <!-- Star picker -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-3">Your Rating</label>
              <div class="flex items-center space-x-2">
                <template v-for="star in 5" :key="star">
                  <button
                    type="button"
                    @click="reviewForm.rating = star"
                    @mouseenter="hoverRating = star"
                    @mouseleave="hoverRating = 0"
                    class="focus:outline-none transition-all hover:scale-110"
                  >
                    <svg
                      class="w-10 h-10 transition-colors"
                      :class="(hoverRating || reviewForm.rating) >= star ? 'text-yellow-400 drop-shadow-sm' : 'text-slate-200'"
                      fill="currentColor" viewBox="0 0 20 20"
                    >
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  </button>
                </template>
                <span v-if="reviewForm.rating" class="ml-3 text-sm font-semibold text-slate-700">
                  {{ ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent'][reviewForm.rating] }}
                </span>
              </div>
            </div>

            <!-- Comment -->
            <div>
              <label class="block text-sm font-semibold text-slate-700 mb-1.5">Your Experience</label>
              <textarea
                v-model="reviewForm.comment"
                rows="4"
                required
                class="w-full border border-slate-300 rounded-xl shadow-sm focus:border-indigo-500 focus:ring-2 focus:ring-indigo-200 py-3 px-4 resize-none text-sm transition-all"
                placeholder="Tell us what you loved about this cabana..."
              ></textarea>
            </div>

            <div v-if="reviewError" class="text-red-600 text-sm font-medium bg-red-50 px-4 py-2 rounded-lg border border-red-100">
              {{ reviewError }}
            </div>

            <button
              type="submit"
              :disabled="loadingReview || !reviewForm.rating"
              class="bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 disabled:opacity-50 text-white font-bold py-3 px-8 rounded-xl shadow-md transition-all flex items-center"
            >
              <span v-if="loadingReview" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
              Submit Review
            </button>
          </form>
        </div>

      </div>
    </div>
    
    <!-- Cancel Booking Modal -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
        <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-red-50 border-2 border-red-100 flex items-center justify-center">
              <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-bold text-slate-900">Cancel Booking?</h3>
              <p class="text-sm text-slate-500 mt-1">
                This will cancel your booking. This cannot be undone.
              </p>
            </div>
            <div class="flex gap-3 w-full pt-1">
              <button @click="showCancelModal = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Keep Booking</button>
              <button @click="executeCancel" :disabled="cancelLoading"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 disabled:opacity-60">
                <span v-if="cancelLoading" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                Yes, Cancel
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBookingStore } from '../store/bookingStore';
import { useToast } from 'vue-toastification';
import { formatLKR } from '../utils/currency';

const route = useRoute();
const router = useRouter();
const bookingStore = useBookingStore();
const toast = useToast();

const booking = computed(() => bookingStore.currentBooking);

const reviewForm = ref({ rating: 0, comment: '' });
const hoverRating = ref(0);
const loadingReview = ref(false);
const reviewSuccess = ref(false);
const reviewError = ref('');

onMounted(async () => {
  const id = route.params.id;
  if (id) await bookingStore.fetchBookingDetails(id);
});

// Night calculation
const nights = computed(() => {
  if (!booking.value?.check_in || !booking.value?.check_out) return 0;
  const diff = new Date(booking.value.check_out) - new Date(booking.value.check_in);
  return Math.max(0, Math.round(diff / (1000 * 60 * 60 * 24)));
});

const formatDisplayDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr + 'T00:00:00');
  return d.toLocaleDateString('en-LK', { weekday: 'short', year: 'numeric', month: 'short', day: 'numeric' });
};

const statusClass = (s) => ({
  confirmed: 'bg-green-50 text-green-700 border-green-200',
  completed:  'bg-blue-50  text-blue-700  border-blue-200',
  pending:    'bg-yellow-50 text-yellow-700 border-yellow-200',
  cancelled:  'bg-red-50   text-red-700   border-red-200',
}[s] || 'bg-slate-100 text-slate-700 border-slate-200');

const paymentStatusClass = (s) => ({
  paid:       'bg-green-50 text-green-700 border-green-200',
  pending:    'bg-yellow-50 text-yellow-700 border-yellow-200',
  failed:     'bg-red-50 text-red-700 border-red-200',
  cancelled:  'bg-red-50 text-red-700 border-red-200',
  refunded:   'bg-slate-100 text-slate-600 border-slate-200',
}[s] || 'bg-yellow-50 text-yellow-700 border-yellow-200');

const submitReview = async () => {
  if (!reviewForm.value.rating) return;
  loadingReview.value = true;
  reviewError.value = '';
  try {
    await bookingStore.submitReview(booking.value.id, {
      rating: reviewForm.value.rating,
      comment: reviewForm.value.comment,
    });
    reviewSuccess.value = true;
  } catch (err) {
    reviewError.value = bookingStore.error || 'Failed to submit review.';
  } finally {
    loadingReview.value = false;
  }
};

const showCancelModal = ref(false);
const cancelLoading = ref(false);

const executeCancel = async () => {
  cancelLoading.value = true;
  try {
    await bookingStore.cancelBooking(booking.value.id);
    toast.success('Booking cancelled successfully.');
    // refreshing the details
    await bookingStore.fetchBookingDetails(booking.value.id);
  } catch {
    toast.error('Failed to cancel booking.');
  } finally {
    cancelLoading.value = false;
    showCancelModal.value = false;
  }
};
</script>
