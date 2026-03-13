<template>
  <div v-if="loading" class="fixed inset-0 z-50 flex items-center justify-center bg-white/80 backdrop-blur-sm transition-opacity">
    <div class="flex flex-col items-center">
      <div class="relative">
        <div class="w-16 h-16 border-4 border-indigo-200 rounded-full animate-pulse"></div>
        <div class="w-16 h-16 border-4 border-indigo-600 rounded-full animate-spin absolute top-0 left-0 border-t-transparent shadow-sm"></div>
      </div>
      <p v-if="message" class="mt-4 text-sm font-semibold text-slate-700">{{ message }}</p>
    </div>
  </div>
</template>

<script setup>
defineProps({
  loading: {
    type: Boolean,
    default: true
  },
  message: {
    type: String,
    default: 'Loading...'
  }
})
</script>
