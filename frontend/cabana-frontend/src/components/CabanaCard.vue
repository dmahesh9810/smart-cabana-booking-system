<template>
  <div class="bg-white rounded-2xl shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden border border-slate-100 flex flex-col group cursor-pointer">

    <!-- Image Area -->
    <div class="relative h-56 overflow-hidden bg-slate-100">
      <img
        :src="imageUrl"
        :alt="cabana.name"
        loading="lazy"
        class="w-full h-full object-cover group-hover:scale-108 transition-transform duration-500"
        @error="onImageError"
      />

      <!-- Price badge -->
      <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-sm px-3 py-1.5 rounded-full shadow-md">
        <span class="text-xs font-semibold text-slate-500 block leading-none">per night</span>
        <span class="text-sm font-extrabold text-indigo-700 leading-tight">{{ formatLKRShort(cabana.price_per_night) }}</span>
      </div>

      <!-- Status badge -->
      <div v-if="cabana.is_active === false" class="absolute top-3 left-3 bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full">
        Unavailable
      </div>
    </div>

    <!-- Card Content -->
    <div class="p-5 flex flex-col flex-grow">

      <!-- Name + Stars row -->
      <div class="flex items-start justify-between mb-1.5">
        <h3 class="text-lg font-bold text-slate-900 truncate flex-1 mr-2">{{ cabana.name }}</h3>
        <div v-if="cabana.avg_rating" class="flex items-center space-x-1 shrink-0">
          <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <span class="text-sm font-semibold text-slate-700">{{ Number(cabana.avg_rating).toFixed(1) }}</span>
        </div>
      </div>

      <!-- Location -->
      <p v-if="cabana.location" class="text-sm text-slate-500 mb-3 flex items-center">
        <svg class="w-3.5 h-3.5 mr-1 text-indigo-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        {{ cabana.location }}
      </p>

      <!-- Capacity -->
      <div class="flex items-center text-slate-500 text-sm mb-5">
        <svg class="w-4 h-4 mr-1.5 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        Up to {{ cabana.max_guests }} guests
      </div>

      <!-- Price highlight -->
      <div class="bg-indigo-50 rounded-xl px-4 py-3 mb-4 flex items-center justify-between">
        <span class="text-sm text-indigo-700 font-medium">Price per night</span>
        <span class="text-xl font-extrabold text-indigo-700">{{ formatLKRShort(cabana.price_per_night) }}</span>
      </div>

      <!-- CTA Button -->
      <router-link
        :to="{ name: 'CabanaDetails', params: { id: cabana.id } }"
        class="mt-auto block w-full text-center bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 rounded-xl shadow-sm hover:shadow-indigo-400/30 transition-all duration-200 transform hover:-translate-y-0.5"
      >
        View &amp; Book
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { formatLKRShort } from '../utils/currency';

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const props = defineProps({
  cabana: { type: Object, required: true }
});

const imgFailed = ref(false);

const imageUrl = computed(() => {
  if (imgFailed.value || !props.cabana.image) return PLACEHOLDER;
  return props.cabana.image;
});

const onImageError = () => {
  imgFailed.value = true;
};
</script>
