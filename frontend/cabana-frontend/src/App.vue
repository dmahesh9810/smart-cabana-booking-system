<template>
  <div class="min-h-screen bg-slate-50 flex flex-col">
    <Navbar />
    <main class="flex-grow w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
      <router-view></router-view>
    </main>
  </div>
</template>

<script setup>
import Navbar from './components/Navbar.vue'
</script>
