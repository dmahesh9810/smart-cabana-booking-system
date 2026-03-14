<template>
  <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-900/60 backdrop-blur-sm">
    <div class="bg-white w-full max-w-md rounded-2xl shadow-2xl border border-slate-100 overflow-hidden animate-in fade-in zoom-in duration-200">
      
      <!-- Header -->
      <div class="bg-slate-50 px-6 py-4 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-lg font-bold text-slate-900">Share Your Experience</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors">
          <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Body -->
      <div class="p-6">
        <div class="text-center mb-6">
          <p class="text-sm text-slate-500 mb-4">How was your stay at <span class="font-bold text-slate-700">{{ booking?.cabana?.name }}</span>?</p>
          
          <!-- Star Rating -->
          <div class="flex items-center justify-center gap-2">
            <button 
              v-for="star in 5" 
              :key="star"
              @click="rating = star"
              @mouseenter="hoverRating = star"
              @mouseleave="hoverRating = 0"
              class="transition-transform active:scale-95"
            >
              <svg 
                class="w-10 h-10" 
                :class="(hoverRating || rating) >= star ? 'text-yellow-400 fill-current' : 'text-slate-200'"
                viewBox="0 0 20 20"
              >
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </button>
          </div>
          <p class="text-xs font-bold mt-2 uppercase tracking-widest" :class="ratingColor">
            {{ ratingLabel }}
          </p>
        </div>

        <!-- Comment -->
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-slate-700">Write a comment</label>
          <textarea 
            v-model="comment"
            placeholder="Tell us about the amenities, location, and overall service..."
            class="w-full h-32 px-4 py-3 rounded-xl border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all text-sm resize-none"
          ></textarea>
        </div>
      </div>

      <!-- Footer -->
      <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex gap-3">
        <button 
          @click="$emit('close')"
          class="flex-1 px-4 py-2.5 bg-white border border-slate-200 text-slate-600 font-bold rounded-xl hover:bg-slate-50 transition-colors"
        >
          Cancel
        </button>
        <button 
          @click="handleSubmit"
          :disabled="!rating || loading"
          class="flex-[2] px-4 py-2.5 bg-indigo-600 text-white font-bold rounded-xl hover:bg-indigo-700 disabled:opacity-50 transition-all flex items-center justify-center"
        >
          <span v-if="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2"></span>
          Submit Review
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useBookingStore } from '../store/bookingStore';
import { useToast } from 'vue-toastification';

const props = defineProps({
  isOpen: Boolean,
  booking: Object
});

const emit = defineEmits(['close', 'success']);
const bookingStore = useBookingStore();
const toast = useToast();

const rating = ref(0);
const hoverRating = ref(0);
const comment = ref('');
const loading = ref(false);

const ratingLabel = computed(() => {
  const r = hoverRating.value || rating.value;
  const labels = ['', 'Poor', 'Fair', 'Good', 'Very Good', 'Excellent!'];
  return labels[r] || '';
});

const ratingColor = computed(() => {
  const r = hoverRating.value || rating.value;
  if (r <= 2) return 'text-red-500';
  if (r === 3) return 'text-yellow-600';
  return 'text-emerald-600';
});

const handleSubmit = async () => {
  if (!rating.value) return;
  
  loading.value = true;
  try {
    await bookingStore.submitReview(props.booking.id, {
      rating: rating.value,
      comment: comment.value
    });
    toast.success('Thank you for your review!');
    emit('success');
    emit('close');
    // Reset
    rating.value = 0;
    comment.value = '';
  } catch (error) {
    toast.error(error.response?.data?.message || 'Failed to submit review');
  } finally {
    loading.value = false;
  }
};
</script>
