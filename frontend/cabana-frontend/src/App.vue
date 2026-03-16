<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <Navbar />
    <main class="flex-grow w-full">
      <router-view></router-view>
    </main>
    <footer class="bg-ocean-900 text-white py-8 mt-auto">
      <div class="container mx-auto px-4 max-w-7xl text-center">
        <div class="flex items-center justify-center space-x-2 mb-3">
          <div class="w-7 h-7 bg-gradient-to-br from-ocean-400 to-teal-500 rounded-lg flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <span class="text-lg font-bold">Smart<span class="text-ocean-300">Cabana</span></span>
        </div>
        <p class="text-ocean-300 text-sm">© 2025 Smart Cabana Booking System. All rights reserved.</p>
        <p class="text-ocean-400 text-xs mt-1">Premium tropical cabana experiences</p>
      </div>
    </footer>
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
</script>
