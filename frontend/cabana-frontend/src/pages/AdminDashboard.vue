<template>
  <div class="space-y-6">
    
    <!-- Loading State -->
    <div v-if="adminStore.loading" class="flex justify-center items-center py-20">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Error State -->
    <div v-else-if="adminStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg shadow-sm border border-red-100">
      {{ adminStore.error }}
    </div>

    <!-- Stat Cards Grid -->
    <div v-else-if="stats" class="grid grid-cols-1 gap-6 sm:grid-cols-2 lg:grid-cols-4">
      
      <!-- Total Bookings -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center">
        <div class="p-3 rounded-xl bg-blue-50 text-blue-600">
          <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
          </svg>
        </div>
        <div class="ml-5">
          <p class="text-sm font-medium text-slate-500 truncate mb-1">Total Bookings</p>
          <p class="text-2xl font-black text-slate-900">{{ stats.total_bookings }}</p>
        </div>
      </div>

      <!-- Total Revenue -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center">
        <div class="p-3 rounded-xl bg-green-50 text-green-600">
          <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
          </svg>
        </div>
        <div class="ml-5">
          <p class="text-sm font-medium text-slate-500 truncate mb-1">Total Revenue</p>
          <p class="text-2xl font-black text-slate-900">${{ stats.total_revenue }}</p>
        </div>
      </div>

      <!-- Total Cabanas -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center">
        <div class="p-3 rounded-xl bg-indigo-50 text-indigo-600">
          <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
          </svg>
        </div>
        <div class="ml-5">
          <p class="text-sm font-medium text-slate-500 truncate mb-1">Total Cabanas</p>
          <p class="text-2xl font-black text-slate-900">{{ stats.total_cabanas }}</p>
        </div>
      </div>

      <!-- Total Users -->
      <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 flex items-center">
        <div class="p-3 rounded-xl bg-purple-50 text-purple-600">
          <svg class="h-8 w-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path>
          </svg>
        </div>
        <div class="ml-5">
          <p class="text-sm font-medium text-slate-500 truncate mb-1">Total Users</p>
          <p class="text-2xl font-black text-slate-900">{{ stats.total_users }}</p>
        </div>
      </div>

    </div>

    <!-- Analytics Charts -->
    <div v-else-if="stats && adminStore.bookings" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <BookingChart :bookings="adminStore.bookings" />
      <RevenueChart :bookings="adminStore.bookings" />
    </div>

  </div>
</template>

<script setup>
import { onMounted, computed } from 'vue';
import { useAdminStore } from '../store/adminStore';
import BookingChart from '../components/charts/BookingChart.vue';
import RevenueChart from '../components/charts/RevenueChart.vue';

const adminStore = useAdminStore();
const stats = computed(() => adminStore.dashboardStats);

onMounted(async () => {
    // Fetch stats and bookings simultaneously for the charts
    await Promise.all([
      adminStore.fetchDashboardStats(),
      adminStore.fetchBookings()
    ]);
});
</script>
