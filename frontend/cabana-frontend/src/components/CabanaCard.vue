<template>
  <div
    class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-xl border border-slate-100 hover:border-ocean-100 transition-all duration-300 hover:-translate-y-1 cursor-pointer"
    @click="router.push({ name: 'CabanaDetails', params: { id: cabana.id } })"
  >
    <!-- Image -->
    <div class="relative h-48 overflow-hidden bg-slate-100">
      <img
        :src="imageUrl"
        :alt="cabana.name"
        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
        @error="imgFailed = true"
        loading="lazy"
      />
      <!-- Overlay -->
      <div class="absolute inset-0 bg-gradient-to-t from-slate-900/50 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>

      <!-- Price Badge -->
      <div class="absolute top-3 right-3 bg-white/95 backdrop-blur-sm text-ocean-700 text-xs font-bold px-3 py-1.5 rounded-xl shadow-sm">
        {{ formatLKRShort(cabana.price_per_night) }}<span class="text-slate-400 font-normal">/night</span>
      </div>

      <!-- Status dot -->
      <div v-if="cabana.is_active === false" class="absolute top-3 left-3 bg-amber-500 text-white text-xs font-bold px-2.5 py-1 rounded-lg">
        Unavailable
      </div>

      <!-- View Details overlay -->
      <div class="absolute bottom-0 left-0 right-0 flex items-center justify-center pb-4 opacity-0 group-hover:opacity-100 transition-all duration-300 translate-y-2 group-hover:translate-y-0">
        <span class="bg-white text-ocean-700 text-xs font-bold px-4 py-2 rounded-xl shadow-lg">View Details →</span>
      </div>
    </div>

    <!-- Content -->
    <div class="p-4">
      <!-- Name & Rating -->
      <div class="flex items-start justify-between mb-2">
        <h3 class="font-bold text-slate-900 leading-snug line-clamp-1 text-base flex-1 mr-2">{{ cabana.name }}</h3>
        <div v-if="cabana.avg_rating" class="flex items-center gap-0.5 shrink-0 bg-amber-50 border border-amber-100 px-2 py-1 rounded-lg">
          <svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
          </svg>
          <span class="text-xs font-bold text-amber-700">{{ Number(cabana.avg_rating).toFixed(1) }}</span>
        </div>
      </div>

      <!-- Location -->
      <p v-if="cabana.location" class="flex items-center gap-1 text-xs text-slate-500 mb-3">
        <svg class="w-3 h-3 text-ocean-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        {{ cabana.location }}
      </p>

      <!-- Description -->
      <p class="text-slate-500 text-xs leading-relaxed line-clamp-2 mb-4">
        {{ cabana.description || 'A beautiful premium cabana experience.' }}
      </p>

      <!-- Footer -->
      <div class="flex items-center justify-between pt-3 border-t border-slate-100">
        <div class="flex items-center gap-1 text-xs text-slate-500">
          <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
          </svg>
          Up to <strong class="text-slate-700">{{ cabana.max_guests }}</strong> guests
        </div>
        <span class="text-ocean-600 font-bold text-sm">
          {{ formatLKRShort(cabana.price_per_night) }}
          <span class="text-slate-400 font-normal text-xs">/night</span>
        </span>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { formatLKRShort } from '../utils/currency';

const props = defineProps({ cabana: { type: Object, required: true } });
const router = useRouter();
const imgFailed = ref(false);

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const imageUrl = computed(() => {
  if (imgFailed.value) return PLACEHOLDER;
  const img = props.cabana.primary_image?.image_path || props.cabana.image;
  if (img && (img.startsWith('http') || img.startsWith('/'))) return img;
  return PLACEHOLDER;
});
</script>
