<template>
  <div class="mt-12">
    <div class="flex items-center justify-between mb-8">
      <h3 class="text-2xl font-bold text-slate-900">Guest Reviews</h3>
      <div v-if="reviews.length > 0" class="flex items-center bg-white px-4 py-2 rounded-xl shadow-sm border border-slate-100">
        <span class="text-3xl font-extrabold text-slate-900 mr-2">{{ averageRating }}</span>
        <div class="flex flex-col">
          <div class="flex text-yellow-400">
            <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= Math.round(averageRating) ? 'fill-current' : 'text-slate-200'" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
          </div>
          <span class="text-xs text-slate-500 font-medium">{{ reviews.length }} reviews</span>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-if="reviews.length === 0" class="bg-white rounded-2xl p-10 text-center border border-dashed border-slate-300">
      <div class="bg-slate-50 w-16 h-16 rounded-full flex items-center justify-center mx-auto mb-4">
        <svg class="w-8 h-8 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
        </svg>
      </div>
      <p class="text-slate-600 font-medium">No reviews yet for this cabana.</p>
      <p class="text-slate-400 text-sm mt-1">Be the first to share your experience!</p>
    </div>

    <!-- Review Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div v-for="review in reviews" :key="review.id" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-100 hover:shadow-md transition-shadow">
        <div class="flex items-center justify-between mb-4">
          <div class="flex items-center">
            <div class="w-10 h-10 rounded-full bg-indigo-100 flex items-center justify-center text-indigo-700 font-bold mr-3 border-2 border-white shadow-sm">
              {{ review.user?.name.charAt(0).toUpperCase() }}
            </div>
            <div>
              <p class="text-sm font-bold text-slate-900">{{ review.user?.name }}</p>
              <p class="text-xs text-slate-400">{{ formatDate(review.created_at) }}</p>
            </div>
          </div>
          <div class="flex text-yellow-400">
            <svg v-for="i in 5" :key="i" class="w-3 h-3" :class="i <= review.rating ? 'fill-current' : 'text-slate-200'" viewBox="0 0 20 20">
              <path d="M10 15l-5.878 3.09 1.123-6.545L.489 6.91l6.572-.955L10 0l2.939 5.955 6.572.955-4.756 4.635 1.123 6.545z"/>
            </svg>
          </div>
        </div>
        <p class="text-slate-600 text-sm leading-relaxed italic">
          "{{ review.comment }}"
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
defineProps({
  reviews: {
    type: Array,
    default: () => []
  },
  averageRating: {
    type: [Number, String],
    default: 0
  }
});

const formatDate = (dateString) => {
  if (!dateString) return '';
  const date = new Date(dateString);
  return new Intl.DateTimeFormat('en-US', { 
    month: 'short', 
    day: 'numeric', 
    year: 'numeric' 
  }).format(date);
};
</script>
