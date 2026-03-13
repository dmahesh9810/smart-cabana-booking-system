<template>
  <div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-4xl">
      
      <!-- Back Button -->
      <button @click="router.back()"
        class="flex items-center text-indigo-600 hover:text-indigo-800 mb-8 font-medium transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
          <path fill-rule="evenodd" d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z" clip-rule="evenodd" />
        </svg>
        Back to Dashboard
      </button>

      <!-- Loading State -->
      <div v-if="bookingStore.loading && !booking" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="bookingStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg text-center shadow-sm">
        {{ bookingStore.error }}
      </div>

      <!-- Booking Details Container -->
      <div v-else-if="booking" class="space-y-6">
        
        <!-- Header Card -->
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
          <div>
            <h1 class="text-3xl font-extrabold text-slate-900 mb-2">Booking #{{ booking.id }}</h1>
            <p class="text-slate-600 font-medium flex items-center">
              <span class="mr-2">Cabana:</span> 
              <span class="text-slate-900 font-bold border-b border-indigo-200">{{ booking.cabana?.name || 'Unknown' }}</span>
            </p>
          </div>
          
          <div class="flex space-x-3">
             <span class="px-4 py-2 text-sm font-semibold rounded-full"
                :class="{
                  'bg-green-100 text-green-700 border border-green-200': booking.booking_status === 'confirmed',
                  'bg-blue-100 text-blue-700 border border-blue-200': booking.booking_status === 'completed',
                  'bg-yellow-100 text-yellow-700 border border-yellow-200': booking.booking_status === 'pending',
                  'bg-red-100 text-red-700 border border-red-200': booking.booking_status === 'cancelled'
                }"
              >
                Status: {{ booking.booking_status }}
              </span>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
          
          <!-- Details Card -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
             <h2 class="text-xl font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Reservation Details</h2>
             
             <div class="space-y-4">
               <div>
                 <p class="text-sm text-slate-500 mb-1">Check-in Date</p>
                 <p class="font-medium text-slate-900">{{ booking.check_in_date }}</p>
               </div>
               <div>
                 <p class="text-sm text-slate-500 mb-1">Check-out Date</p>
                 <p class="font-medium text-slate-900">{{ booking.check_out_date }}</p>
               </div>
               <div>
                 <p class="text-sm text-slate-500 mb-1">Guests</p>
                 <p class="font-medium text-slate-900">{{ booking.guests }} Persons</p>
               </div>
             </div>
          </div>

           <!-- Payment Card -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
             <h2 class="text-xl font-bold text-slate-900 mb-6 border-b border-slate-100 pb-4">Payment Summary</h2>
             
             <div class="space-y-4">
               <div>
                 <p class="text-sm text-slate-500 mb-1">Total Amount</p>
                 <p class="text-2xl font-black text-slate-900">${{ booking.total_price }}</p>
               </div>
               <div>
                 <p class="text-sm text-slate-500 mb-2">Payment Status</p>
                 <span class="px-3 py-1 text-sm font-semibold rounded-full inline-block"
                    :class="{
                      'bg-green-100 text-green-700': booking.payment_status === 'paid',
                      'bg-yellow-100 text-yellow-700': booking.payment_status === 'pending',
                      'bg-red-100 text-red-700': booking.payment_status === 'failed' || booking.payment_status === 'cancelled'
                    }"
                  >
                    {{ booking.payment_status }}
                  </span>
               </div>
               
               <!-- Payment Action (if pending) -->
               <div v-if="booking.payment_status === 'pending' && booking.booking_status !== 'cancelled'" class="pt-4 border-t border-slate-100 mt-4">
                 <router-link :to="{ name: 'Payment', query: { booking_id: booking.id } }" class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 rounded-xl shadow-md transition-colors">
                   Pay Now
                 </router-link>
               </div>
             </div>
          </div>
        </div>

        <!-- Review Section (Show only if completed) -->
        <div v-if="booking.booking_status === 'completed'" class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8 mt-6">
          <h2 class="text-2xl font-bold text-slate-900 mb-2">Rate Your Stay</h2>
          <p class="text-slate-600 mb-8">We hope you enjoyed your cabana! Please leave a review.</p>
          
          <div v-if="reviewSuccess" class="bg-green-50 text-green-700 p-4 rounded-lg font-medium border border-green-200">
            Thank you for your feedback! Your review has been submitted successfully.
          </div>

          <form v-else @submit.prevent="submitReview" class="space-y-6 max-w-2xl">
            
            <!-- Rating -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Rating</label>
              <div class="flex items-center space-x-2">
                <template v-for="star in 5" :key="star">
                  <button 
                    type="button" 
                    @click="reviewForm.rating = star"
                    @mouseenter="hoverRating = star"
                    @mouseleave="hoverRating = 0"
                    class="focus:outline-none transition-transform hover:scale-110"
                  >
                    <svg class="w-10 h-10" :class="(hoverRating || reviewForm.rating) >= star ? 'text-yellow-400' : 'text-slate-300'" fill="currentColor" viewBox="0 0 20 20">
                      <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                  </button>
                </template>
              </div>
            </div>

            <!-- Comment -->
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-2">Your Experience</label>
              <textarea 
                v-model="reviewForm.comment" 
                rows="4" 
                required
                class="w-full border-slate-300 rounded-lg shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-3 px-4 resize-none"
                placeholder="Tell us what you loved about this cabana..."
              ></textarea>
            </div>

            <!-- Error Feedback -->
            <div v-if="reviewError" class="text-red-600 text-sm font-medium">
              {{ reviewError }}
            </div>

            <button 
              type="submit" 
              :disabled="loadingReview || !reviewForm.rating"
              class="bg-indigo-600 hover:bg-indigo-700 disabled:opacity-50 text-white font-semibold py-3 px-8 rounded-xl shadow-md transition-colors flex items-center"
            >
              <span v-if="loadingReview" class="animate-spin h-5 w-5 border-2 border-white border-t-transparent rounded-full mr-2"></span>
              Submit Review
            </button>
          </form>

        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBookingStore } from '../store/bookingStore';

const route = useRoute();
const router = useRouter();
const bookingStore = useBookingStore();

const booking = computed(() => bookingStore.currentBooking);

const reviewForm = ref({
    rating: 0,
    comment: ''
});
const hoverRating = ref(0);
const loadingReview = ref(false);
const reviewSuccess = ref(false);
const reviewError = ref('');

onMounted(async () => {
    const id = route.params.id;
    if (id) {
        await bookingStore.fetchBookingDetails(id);
    }
});

const submitReview = async () => {
    if (!reviewForm.value.rating) return;
    
    loadingReview.value = true;
    reviewError.value = '';
    
    try {
        await bookingStore.submitReview(booking.value.id, {
            rating: reviewForm.value.rating,
            comment: reviewForm.value.comment
        });
        reviewSuccess.value = true;
    } catch (err) {
        reviewError.value = bookingStore.error || 'Failed to submit review.';
        console.error("Review Error", err);
    } finally {
        loadingReview.value = false;
    }
};
</script>
