<template>
  <nav
    class="sticky top-0 z-50 bg-white/90 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300"
  >
    <div class="container mx-auto px-4 max-w-7xl">
      <div class="flex items-center justify-between h-16">

        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2 group">
          <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-indigo-500/40 transition-shadow">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <span class="text-xl font-extrabold text-slate-900 tracking-tight">Smart<span class="text-indigo-600">Cabana</span></span>
        </router-link>

        <!-- Desktop Nav Links -->
        <div class="hidden md:flex items-center space-x-1">
          <router-link
            to="/"
            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200"
            active-class="text-indigo-600 bg-indigo-50"
          >
            Home
          </router-link>

          <template v-if="!authStore.isAuthenticated">
            <router-link
              to="/login"
              class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200"
            >
              Login
            </router-link>
            <router-link
              to="/register"
              class="ml-2 px-5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-indigo-500/30 transition-all duration-200"
            >
              Register
            </router-link>
          </template>

          <template v-else>
            <template v-if="authStore.isAdmin">
              <router-link
                to="/admin"
                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200"
              >
                Admin Panel
              </router-link>
            </template>
            <template v-else>
              <router-link
                to="/dashboard"
                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200"
                active-class="text-indigo-600 bg-indigo-50"
              >
                My Bookings
              </router-link>
              <router-link
                to="/my-payments"
                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-indigo-600 hover:bg-indigo-50 transition-all duration-200"
                active-class="text-indigo-600 bg-indigo-50"
              >
                My Payments
              </router-link>
            </template>

            <!-- User Avatar + Logout -->
            <div class="flex items-center ml-3 pl-3 border-l border-slate-200 space-x-3">
              <div class="w-8 h-8 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-full flex items-center justify-center text-white text-sm font-bold select-none">
                {{ userInitial }}
              </div>
              <button
                @click="handleLogout"
                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-500 hover:text-red-600 hover:bg-red-50 transition-all duration-200"
              >
                Logout
              </button>
            </div>
          </template>
        </div>

        <!-- Mobile menu toggle -->
        <button
          @click="mobileOpen = !mobileOpen"
          class="md:hidden p-2 rounded-lg text-slate-500 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="mobileOpen" class="md:hidden border-t border-slate-100 bg-white/95 backdrop-blur-md px-4 py-3 space-y-1">
      <router-link to="/" @click="mobileOpen = false"
        class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600">Home</router-link>

      <template v-if="!authStore.isAuthenticated">
        <router-link to="/login" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600">Login</router-link>
        <router-link to="/register" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-indigo-600 text-center">Register</router-link>
      </template>
      <template v-else>
        <router-link v-if="!authStore.isAdmin" to="/dashboard" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600">My Bookings</router-link>
        <router-link v-if="!authStore.isAdmin" to="/my-payments" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600">My Payments</router-link>
        <router-link v-if="authStore.isAdmin" to="/admin" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-indigo-50 hover:text-indigo-600">Admin Panel</router-link>
        <button @click="handleLogout"
          class="block w-full text-left px-4 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50">Logout</button>
      </template>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../store/authStore';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();
const mobileOpen = ref(false);

const userInitial = computed(() => {
  const name = authStore.user?.name || authStore.user?.email || 'U';
  return name.charAt(0).toUpperCase();
});

const handleLogout = async () => {
  mobileOpen.value = false;
  await authStore.logout();
  router.push('/login');
};
</script>
