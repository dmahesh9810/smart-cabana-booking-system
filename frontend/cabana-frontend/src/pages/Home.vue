<template>
  <div class="min-h-screen bg-gradient-to-b from-slate-50 to-white">

    <!-- Hero Section -->
    <div class="relative bg-gradient-to-br from-indigo-900 via-indigo-800 to-purple-900 overflow-hidden">
      <!-- Decorative circles -->
      <div class="absolute -top-24 -right-24 w-96 h-96 bg-white/5 rounded-full"></div>
      <div class="absolute -bottom-16 -left-16 w-72 h-72 bg-purple-500/10 rounded-full"></div>

      <div class="container mx-auto px-4 max-w-7xl py-20 relative z-10">
        <div class="max-w-3xl">
          <div class="inline-flex items-center bg-white/10 backdrop-blur-sm text-white text-xs font-semibold px-4 py-2 rounded-full mb-6 border border-white/20">
            <span class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></span>
            Cabanas available now
          </div>
          <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-6 leading-tight tracking-tight">
            Find Your Perfect
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-yellow-300 to-orange-300"> Cabana</span>
          </h1>
          <p class="text-indigo-200 text-lg md:text-xl max-w-2xl leading-relaxed">
            Escape to luxury and tranquility. Browse our exclusive selection of premium tropical cabanas.
          </p>
        </div>
      </div>
    </div>

    <div class="container mx-auto px-4 max-w-7xl py-14">

      <!-- Recommendation Section -->
      <div class="mb-16">
        <RecommendedCabanas />
      </div>

      <!-- Section Divider -->
      <div class="flex items-center mb-10">
        <div class="flex-1 h-px bg-slate-200"></div>
        <div class="mx-4">
          <h2 class="text-2xl font-extrabold text-slate-900">All Cabanas</h2>
        </div>
        <div class="flex-1 h-px bg-slate-200"></div>
      </div>

      <!-- Loading State -->
      <div v-if="cabanaStore.loading" class="flex justify-center items-center py-20">
        <div class="flex flex-col items-center space-y-4">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
          <p class="text-slate-500 text-sm">Loading cabanas...</p>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="cabanaStore.error" class="bg-red-50 text-red-600 p-6 rounded-2xl text-center border border-red-100 shadow-sm">
        <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="font-semibold">{{ cabanaStore.error }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="cabanaStore.cabanas.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
        </svg>
        <p class="text-xl font-semibold text-slate-700">No cabanas available</p>
        <p class="text-slate-500 mt-2">Please check back later.</p>
      </div>

      <!-- Cabana Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <CabanaCard
          v-for="cabana in cabanaStore.cabanas"
          :key="cabana.id"
          :cabana="cabana"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useCabanaStore } from '../store/cabanaStore';
import CabanaCard from '../components/CabanaCard.vue';
import RecommendedCabanas from '../components/RecommendedCabanas.vue';

const cabanaStore = useCabanaStore();

onMounted(() => {
  cabanaStore.fetchCabanas();
});
</script>
