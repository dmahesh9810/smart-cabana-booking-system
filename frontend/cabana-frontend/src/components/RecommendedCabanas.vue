<template>
  <div class="py-2">
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center space-x-2 mb-2">
          <span class="text-2xl">🔥</span>
          <span class="text-xs font-bold uppercase tracking-widest text-indigo-600 bg-indigo-50 px-3 py-1 rounded-full">Most Popular</span>
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recommended For You</h2>
        <p class="text-slate-500 mt-1 text-sm">Discover our most booked and highly rated cabanas.</p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="bg-red-50 text-red-600 p-4 rounded-xl text-center text-sm font-medium border border-red-100">
      {{ error }}
    </div>

    <!-- Empty -->
    <div v-else-if="!recommendations || recommendations.length === 0" class="text-center py-12 text-slate-500">
      <p>No recommendations available right now.</p>
    </div>

    <!-- Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-6">
      <div
        v-for="cabana in recommendations"
        :key="cabana.id"
        class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl transition-all duration-300 flex flex-col group"
      >
        <!-- Image -->
        <div class="relative h-52 overflow-hidden bg-slate-100">
          <img
            :src="resolveImage(cabana.image)"
            :alt="cabana.name"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            @error="e => onImgError(e)"
          />
          <!-- Popular badge -->
          <div class="absolute top-3 left-3 bg-gradient-to-r from-orange-500 to-red-500 text-white text-xs font-bold px-3 py-1.5 rounded-full flex items-center shadow-md">
            <svg class="w-3.5 h-3.5 mr-1" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
            </svg>
            Popular
          </div>
          <!-- Price -->
          <div class="absolute bottom-3 right-3 bg-white/95 backdrop-blur-sm px-3 py-1 rounded-full shadow-sm">
            <span class="text-sm font-extrabold text-indigo-700">{{ formatLKRShort(cabana.price) }}</span>
            <span class="text-xs text-slate-500"> / night</span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-5 flex flex-col flex-grow">
          <h3 class="text-lg font-bold text-slate-900 mb-1 truncate">{{ cabana.name }}</h3>
          <div class="mt-auto pt-4">
            <router-link
              :to="{ name: 'CabanaDetails', params: { id: cabana.id } }"
              class="w-full block text-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-4 rounded-xl transition-all duration-200 shadow-sm hover:shadow-indigo-400/30 transform hover:-translate-y-0.5"
            >
              Book Now
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/axios';
import { formatLKRShort } from '../utils/currency';

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const recommendations = ref([]);
const loading = ref(true);
const error = ref(null);

onMounted(async () => {
  try {
    const response = await api.get('/recommendations');
    if (response.data && response.data.success) {
      recommendations.value = response.data.data;
    }
  } catch (err) {
    console.error('Failed to load recommendations:', err);
    error.value = 'Unable to load popular cabanas at this time.';
  } finally {
    loading.value = false;
  }
});

const resolveImage = (src) => (src && src.startsWith('http') ? src : PLACEHOLDER);

const onImgError = (e) => {
  e.target.src = PLACEHOLDER;
  e.target.onerror = null;
};
</script>
