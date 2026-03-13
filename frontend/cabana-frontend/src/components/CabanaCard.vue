<template>
  <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-shadow duration-300 overflow-hidden border border-slate-100 flex flex-col group">
    <!-- Image Area -->
    <div class="relative h-60 overflow-hidden bg-slate-100">
      <img v-if="cabana.image" :src="cabana.image" :alt="cabana.name" loading="lazy" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" />
      <div v-else class="w-full h-full flex items-center justify-center text-slate-400">
        <span class="text-sm">No Image Available</span>
      </div>
      <div class="absolute top-4 right-4 bg-white/90 backdrop-blur-sm px-3 py-1 rounded-full text-sm font-semibold text-indigo-600 shadow-sm">
        ${{ cabana.price_per_night }} / night
      </div>
    </div>
    
    <div class="p-5">
      <h3 class="text-xl font-bold text-slate-900 mb-2 truncate">{{ cabana.name }}</h3>
      
      <div class="flex items-center text-slate-600 mb-4 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
        </svg>
        <span>Capacity: {{ cabana.max_guests }} Persons</span>
      </div>
      
      <p v-if="cabana.location" class="text-slate-500 text-sm mb-5 truncate">
        📍 {{ cabana.location }}
      </p>

      <router-link 
        :to="{ name: 'CabanaDetails', params: { id: cabana.id } }" 
        class="block w-full text-center bg-indigo-600 hover:bg-indigo-700 text-white font-medium py-2.5 rounded-lg transition-colors duration-200"
      >
        View Details
      </router-link>
    </div>
  </div>
</template>

<script setup>
defineProps({
    cabana: {
        type: Object,
        required: true
    }
});
</script>
