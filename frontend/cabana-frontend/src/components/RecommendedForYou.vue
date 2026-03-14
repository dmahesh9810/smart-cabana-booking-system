<template>
  <section class="py-2">
    <!-- Section Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <div class="flex items-center gap-2 mb-2">
          <span class="text-xl">✨</span>
          <span class="text-xs font-bold uppercase tracking-widest text-violet-600 bg-violet-50 px-3 py-1 rounded-full">
            AI Picks For You
          </span>
        </div>
        <h2 class="text-3xl font-extrabold text-slate-900 tracking-tight">Recommended For You</h2>
        <p class="text-slate-500 mt-1 text-sm">
          Personalized suggestions based on your booking history and similar guests.
        </p>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="n in 4"
        :key="n"
        class="bg-white rounded-2xl border border-slate-100 overflow-hidden animate-pulse"
      >
        <div class="h-48 bg-slate-100"></div>
        <div class="p-5 space-y-3">
          <div class="h-4 bg-slate-100 rounded-full w-3/4"></div>
          <div class="h-3 bg-slate-100 rounded-full w-1/2"></div>
          <div class="h-10 bg-slate-100 rounded-xl mt-4"></div>
        </div>
      </div>
    </div>

    <!-- Error -->
    <div
      v-else-if="error"
      class="flex items-center gap-3 bg-red-50 border border-red-100 text-red-600 px-5 py-4 rounded-xl text-sm"
    >
      <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ error }}
    </div>

    <!-- Empty -->
    <div
      v-else-if="!recommendations.length"
      class="text-center py-12 bg-white rounded-2xl border border-slate-100"
    >
      <div class="text-4xl mb-3">🏖️</div>
      <p class="text-slate-600 font-semibold">No personalized picks yet.</p>
      <p class="text-slate-400 text-sm mt-1">Make your first booking to unlock tailored recommendations.</p>
    </div>

    <!-- Cards Grid -->
    <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
      <div
        v-for="cabana in recommendations"
        :key="cabana.id"
        class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col group"
      >
        <!-- Image -->
        <div class="relative h-48 overflow-hidden bg-slate-100 shrink-0">
          <img
            :src="resolveImage(cabana.image)"
            :alt="cabana.name"
            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
            @error="onImgError"
          />

          <!-- AI badge -->
          <div class="absolute top-3 left-3 bg-gradient-to-r from-violet-600 to-indigo-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full flex items-center gap-1 shadow-md">
            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            AI Pick
          </div>

          <!-- Price -->
          <div class="absolute bottom-3 right-3 bg-white/95 backdrop-blur-sm px-2.5 py-1 rounded-full shadow-sm">
            <span class="text-sm font-extrabold text-indigo-700">{{ formatLKRShort(cabana.price) }}</span>
            <span class="text-[10px] text-slate-500"> / night</span>
          </div>
        </div>

        <!-- Content -->
        <div class="p-5 flex flex-col flex-grow">
          <!-- Name + Rating -->
          <div class="flex items-start justify-between gap-2 mb-2">
            <h3 class="text-base font-bold text-slate-900 leading-snug flex-1 truncate">{{ cabana.name }}</h3>
            <span v-if="cabana.rating" class="flex items-center gap-1 shrink-0 text-sm font-bold text-amber-600 bg-amber-50 px-2 py-0.5 rounded-lg">
              <svg class="w-3.5 h-3.5 text-amber-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
              {{ cabana.rating }}
            </span>
          </div>

          <!-- Location -->
          <p v-if="cabana.location" class="text-xs text-slate-400 mb-2 flex items-center gap-1 truncate">
            <svg class="w-3 h-3 text-indigo-300 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0zM15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            {{ cabana.location }}
          </p>

          <!-- Reason pill -->
          <div class="flex items-start gap-1.5 mb-4">
            <svg class="w-3.5 h-3.5 text-violet-400 mt-0.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"/>
            </svg>
            <p class="text-[11px] text-violet-600 leading-tight italic">{{ cabana.reason }}</p>
          </div>

          <!-- CTA -->
          <div class="mt-auto">
            <router-link
              :to="{ name: 'CabanaDetails', params: { id: cabana.id } }"
              class="block w-full text-center bg-gradient-to-r from-violet-600 to-indigo-600 hover:from-violet-700 hover:to-indigo-700 text-white text-sm font-bold py-3 px-4 rounded-xl transition-all duration-200 shadow-sm hover:shadow-indigo-400/30 hover:-translate-y-0.5"
            >
              View &amp; Book
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </section>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../api/axios';
import { formatLKRShort } from '../utils/currency';

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const recommendations = ref([]);
const loading = ref(true);
const error   = ref(null);

onMounted(async () => {
    try {
        const { data } = await api.get('/recommendations/personalized');
        recommendations.value = data.recommended_cabanas ?? [];
    } catch (err) {
        // If auth fails (guest user), silently hide the section
        if (err.response?.status === 401) {
            recommendations.value = [];
        } else {
            error.value = 'Unable to load personalized recommendations.';
        }
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
