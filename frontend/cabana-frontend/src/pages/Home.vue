<template>
  <div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-7xl">
      <!-- Header Section -->
      <div class="mb-12 text-center">
        <h1 class="text-4xl md:text-5xl font-extrabold text-slate-900 mb-4 tracking-tight">
          Find Your Perfect <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600">Cabana</span>
        </h1>
        <p class="text-lg text-slate-600 max-w-2xl mx-auto">
          Escape to luxury and tranquility. Browse our exclusive selection of premium cabanas for your next getaway.
        </p>
      </div>

      <!-- Recommendation Section -->
      <div class="mb-16">
        <RecommendedCabanas />
      </div>

      <!-- Loading State -->
      <div v-if="cabanaStore.loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="cabanaStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg text-center max-w-2xl mx-auto shadow-sm">
        {{ cabanaStore.error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="cabanaStore.cabanas.length === 0" class="text-center py-20 text-slate-500">
        <p class="text-xl font-medium">No cabanas available at the moment.</p>
        <p class="mt-2">Please check back later.</p>
      </div>

      <!-- Cabana Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
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
