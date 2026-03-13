<template>
  <div class="py-4">
    <div class="flex items-center justify-between mb-8">
      <div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recommended For You</h2>
        <p class="text-slate-500 mt-2">Discover our most popular and highly rated cabanas based on guest experiences.</p>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex justify-center items-center py-12">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="bg-red-50 text-red-600 p-4 rounded-xl text-center shadow-sm text-sm font-medium">
      {{ error }}
    </div>

    <!-- Empty State -->
    <div v-else-if="!recommendations || recommendations.length === 0" class="text-center py-12 text-slate-500">
      <p>No recommendations available right now.</p>
    </div>

    <!-- Recommendations Grid -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div 
        v-for="cabana in recommendations" 
        :key="cabana.id" 
        class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-md transition-shadow flex flex-col group"
      >
        <!-- Cabana Image -->
        <div class="relative h-56 overflow-hidden bg-slate-100">
          <img 
            :src="cabana.image" 
            :alt="cabana.name" 
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            @error="handleImageError"
          >
          <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-xs font-bold text-indigo-700 shadow-sm flex items-center">
            <svg class="w-3.5 h-3.5 mr-1 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
              <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
            </svg>
            Popular
          </div>
        </div>

        <!-- Cabana Content -->
        <div class="p-6 flex flex-col flex-grow">
          <h3 class="text-xl font-bold text-slate-900 mb-2 truncate" :title="cabana.name">{{ cabana.name }}</h3>
          <div class="text-slate-500 font-medium mb-6">
            LKR {{ Number(cabana.price).toLocaleString() }} <span class="text-sm font-normal">/ night</span>
          </div>
          
          <div class="mt-auto">
            <router-link 
              :to="{ name: 'CabanaDetails', params: { id: cabana.id } }" 
              class="w-full block text-center bg-slate-900 hover:bg-slate-800 text-white font-semibold py-3 px-4 rounded-xl transition-colors shadow-sm"
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
        console.error("Failed to load recommendations:", err);
        error.value = "Unable to load popular cabanas at this time.";
    } finally {
        loading.value = false;
    }
});

// Fallback image handler in case the dynamic URL breaks
const handleImageError = (e) => {
    e.target.src = '/placeholder.jpg';
    e.target.onerror = null;
};
</script>
