<template>
  <div class="h-screen flex bg-slate-100 overflow-hidden font-sans">
    
    <!-- Sidebar -->
    <div class="flex flex-col w-64 bg-slate-900 border-r border-slate-800 transition-width duration-300">
      
      <!-- Logo Branding -->
      <div class="h-16 flex items-center justify-center bg-slate-900 border-b border-slate-800 px-4 shrink-0">
        <router-link to="/" class="text-white font-black text-xl tracking-tight flex items-center gap-2 hover:opacity-80 transition-opacity">
          <svg class="h-6 w-6 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
          </svg>
          Smart Cabana
        </router-link>
      </div>

      <!-- Navigation Links -->
      <div class="flex-1 overflow-y-auto py-6 px-3">
        <nav class="space-y-2">
          
          <router-link :to="{ name: 'AdminDashboard' }" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all"
            :class="route.name === 'AdminDashboard' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
            <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z"></path>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z"></path>
            </svg>
            Dashboard
          </router-link>

          <router-link :to="{ name: 'AdminCabanas' }" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all"
             :class="route.name === 'AdminCabanas' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
            <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            Cabanas
          </router-link>

          <router-link :to="{ name: 'AdminBookings' }" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all"
             :class="route.name === 'AdminBookings' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
            <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path>
            </svg>
            Bookings
          </router-link>

          <router-link :to="{ name: 'AdminPayments' }" class="group flex items-center px-4 py-3 text-sm font-medium rounded-xl transition-all"
             :class="route.name === 'AdminPayments' ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white'">
            <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            Payments
          </router-link>

        </nav>
      </div>

      <!-- Logout Component -->
      <div class="p-4 border-t border-slate-800 shrink-0">
        <button @click="handleLogout" class="group flex w-full items-center px-4 py-3 text-sm font-medium rounded-xl text-slate-400 hover:bg-red-500/10 hover:text-red-400 transition-all">
          <svg class="mr-3 h-5 w-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path>
          </svg>
          Logout
        </button>
      </div>

    </div>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col w-0 overflow-hidden">
      
      <!-- Top nav mobile (optional) / Breadcrumbs -->
      <div class="h-16 flex items-center px-8 bg-white border-b border-slate-200 shrink-0">
        <h2 class="text-xl font-bold text-slate-800 capitalize">{{ route.name.replace('Admin', '') }} Management</h2>
      </div>

      <!-- Dynamic Page Content -->
      <main class="flex-1 relative overflow-y-auto focus:outline-none p-8">
        <router-view></router-view>
      </main>

    </div>

  </div>
</template>

<script setup>
import { useRoute, useRouter } from 'vue-router';
import { useAuthStore } from '../store/authStore';

const route = useRoute();
const router = useRouter();
const authStore = useAuthStore();

const handleLogout = async () => {
    await authStore.logout();
    router.push('/login');
};
</script>
