<template>
  <div class="min-h-screen">

    <!-- ── Hero Section ──────────────────────────────────────── -->
    <div class="relative bg-gradient-to-br from-ocean-900 via-ocean-800 to-teal-700 overflow-hidden">
      <!-- Decorative blobs -->
      <div class="absolute -top-32 -right-32 w-[500px] h-[500px] bg-teal-500/10 rounded-full blur-3xl"></div>
      <div class="absolute -bottom-20 -left-20 w-96 h-96 bg-ocean-400/10 rounded-full blur-3xl"></div>
      <!-- Wave SVG at bottom -->
      <div class="absolute bottom-0 left-0 right-0 overflow-hidden leading-none">
        <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" class="wave-anim" preserveAspectRatio="none" style="display:block; height:60px; width:100%;">
          <path d="M0,30 C360,60 1080,0 1440,30 L1440,60 L0,60 Z" fill="#f8fafc"/>
        </svg>
      </div>

      <div class="container mx-auto px-4 max-w-7xl py-20 pb-24 relative z-10">
        <div class="max-w-3xl">
          <div class="inline-flex items-center bg-white/10 backdrop-blur-sm text-white text-xs font-semibold px-4 py-2 rounded-full mb-6 border border-white/20 gap-2">
            <span class="w-2 h-2 bg-teal-400 rounded-full animate-pulse"></span>
            Premium cabanas available now
          </div>
          <h1 class="text-5xl md:text-6xl font-extrabold text-white mb-5 leading-tight tracking-tight">
            Find Your Perfect
            <span class="text-transparent bg-clip-text bg-gradient-to-r from-sand-300 to-teal-300"> Cabana</span>
          </h1>
          <p class="text-ocean-200 text-lg md:text-xl max-w-2xl leading-relaxed mb-10">
            Escape to luxury and tranquility. Browse our exclusive selection of premium tropical cabanas with beachside access, breathtaking views, and world-class amenities.
          </p>

          <!-- Search Bar -->
          <div class="flex flex-col sm:flex-row gap-3">
            <div class="flex-1 relative">
              <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-slate-400 pointer-events-none" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="20" height="20">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
              </svg>
              <input
                v-model="localSearch"
                @input="debouncedSearch"
                type="text"
                placeholder="Search by name, location..."
                class="w-full pl-12 pr-4 py-4 bg-white rounded-2xl text-slate-900 placeholder-slate-400 font-medium focus:outline-none focus:ring-2 focus:ring-teal-400 shadow-lg text-sm"
              />
            </div>
            <button
               @click="showFilters = !showFilters"
               class="flex items-center justify-center gap-2 px-6 py-4 bg-white/15 backdrop-blur-sm border border-white/30 text-white font-semibold rounded-2xl hover:bg-white/25 transition-colors text-sm"
             >
               <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="16" height="16">
                 <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V18l-4 4v-5.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"/>
               </svg>
              Filters
              <span v-if="activeFilterCount" class="w-5 h-5 bg-teal-400 text-ocean-900 text-xs font-bold rounded-full flex items-center justify-center">{{ activeFilterCount }}</span>
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ── Filter Panel ────────────────────────────────────── -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
    >
      <div v-if="showFilters" class="bg-white border-b border-slate-200 shadow-sm">
        <div class="container mx-auto px-4 max-w-7xl py-5">
          <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Price min -->
            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Min Price (LKR)</label>
              <input type="number" v-model="localMin" @input="applyFilters" min="0" placeholder="0"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"/>
            </div>
            <!-- Price max -->
            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Max Price (LKR)</label>
              <input type="number" v-model="localMax" @input="applyFilters" min="0" placeholder="Any"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"/>
            </div>
            <!-- Min Guests -->
            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Min Guests</label>
              <select v-model="localGuests" @change="applyFilters"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 bg-white">
                <option value="">Any</option>
                <option value="1">1+</option>
                <option value="2">2+</option>
                <option value="4">4+</option>
                <option value="6">6+</option>
                <option value="8">8+</option>
              </select>
            </div>
            <!-- Sort -->
            <div>
              <label class="block text-xs font-semibold text-slate-500 uppercase tracking-wider mb-1.5">Sort By</label>
              <select v-model="localSort" @change="applySort"
                class="w-full border border-slate-200 rounded-xl px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500 bg-white">
                <option value="default">Default</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
                <option value="rating">Best Rated</option>
                <option value="newest">Newest</option>
              </select>
            </div>
          </div>
          <div class="flex justify-end mt-3">
            <button @click="resetFilters"
              class="text-xs font-semibold text-slate-500 hover:text-red-600 transition-colors flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" width="14" height="14">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Reset filters
            </button>
          </div>
        </div>
      </div>
    </Transition>

    <!-- ── Main Content ────────────────────────────────────── -->
    <div class="container mx-auto px-4 max-w-7xl py-12">

      <!-- AI Personalized Recommendations (authenticated users) -->
      <div v-if="authStore.isAuthenticated" class="mb-12">
        <RecommendedForYou />
      </div>

      <!-- Public popular recommendations -->
      <div class="mb-14">
        <RecommendedCabanas />
      </div>

      <!-- Section Header -->
      <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
          <div class="flex-1 h-px bg-slate-200 w-8"></div>
          <h2 class="text-2xl font-extrabold text-slate-900">All Cabanas</h2>
          <div class="flex-1 h-px bg-slate-200 w-8"></div>
        </div>
        <span v-if="!cabanaStore.loading" class="text-sm text-slate-500">
          {{ cabanaStore.displayedCabanas.length }} cabin{{ cabanaStore.displayedCabanas.length !== 1 ? 'as' : 'a' }}
        </span>
      </div>

      <!-- Loading Skeletons -->
      <div v-if="cabanaStore.loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <div v-for="i in 8" :key="i" class="bg-white rounded-2xl overflow-hidden border border-slate-100 shadow-sm">
          <div class="skeleton h-44 w-full"></div>
          <div class="p-4 space-y-2">
            <div class="skeleton h-4 w-3/4 rounded"></div>
            <div class="skeleton h-3 w-1/2 rounded"></div>
            <div class="skeleton h-3 w-1/3 rounded mt-3"></div>
          </div>
        </div>
      </div>

      <!-- Error State -->
      <div v-else-if="cabanaStore.error" class="bg-red-50 text-red-600 p-6 rounded-2xl text-center border border-red-100 shadow-sm">
        <svg class="w-12 h-12 text-red-400 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="48" height="48">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <p class="font-semibold">{{ cabanaStore.error }}</p>
      </div>

      <!-- Empty State -->
      <div v-else-if="cabanaStore.displayedCabanas.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
        <svg class="w-16 h-16 text-slate-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" width="64" height="64">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
        <p class="text-xl font-semibold text-slate-700">No cabanas found</p>
        <p class="text-slate-500 mt-2 text-sm">Try adjusting your search or filters.</p>
        <button @click="resetFilters" class="mt-5 px-6 py-2 bg-ocean-600 text-white rounded-xl font-semibold text-sm hover:bg-ocean-700 transition-colors">
          Clear Filters
        </button>
      </div>

      <!-- Cabana Grid -->
      <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        <CabanaCard
          v-for="cabana in cabanaStore.displayedCabanas"
          :key="cabana.id"
          :cabana="cabana"
        />
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { useCabanaStore } from '../store/cabanaStore';
import { useAuthStore } from '../store/authStore';
import CabanaCard from '../components/CabanaCard.vue';
import RecommendedCabanas from '../components/RecommendedCabanas.vue';
import RecommendedForYou from '../components/RecommendedForYou.vue';

const cabanaStore = useCabanaStore();
const authStore   = useAuthStore();

const showFilters = ref(false);
const localSearch = ref('');
const localMin    = ref('');
const localMax    = ref('');
const localGuests = ref('');
const localSort   = ref('default');

let debounceTimer = null;

const debouncedSearch = () => {
  clearTimeout(debounceTimer);
  debounceTimer = setTimeout(() => {
    cabanaStore.setSearchQuery(localSearch.value);
  }, 300);
};

const applyFilters = () => {
  cabanaStore.setFilter('minPrice', localMin.value);
  cabanaStore.setFilter('maxPrice', localMax.value);
  cabanaStore.setFilter('minGuests', localGuests.value);
};

const applySort = () => cabanaStore.setSortBy(localSort.value);

const resetFilters = () => {
  localSearch.value = '';
  localMin.value = '';
  localMax.value = '';
  localGuests.value = '';
  localSort.value = 'default';
  cabanaStore.resetFilters();
  showFilters.value = false;
};

const activeFilterCount = computed(() => {
  let count = 0;
  if (localMin.value) count++;
  if (localMax.value) count++;
  if (localGuests.value) count++;
  if (localSort.value !== 'default') count++;
  return count;
});

onMounted(() => {
  cabanaStore.fetchCabanas();
});
</script>
