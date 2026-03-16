<template>
  <nav
    class="sticky top-0 z-50 bg-white/95 backdrop-blur-md border-b border-slate-200 shadow-sm transition-all duration-300"
  >
    <div class="container mx-auto px-4 max-w-7xl">
      <div class="flex items-center justify-between h-16">

        <!-- Logo -->
        <router-link to="/" class="flex items-center space-x-2 group">
          <div class="w-9 h-9 bg-gradient-to-br from-ocean-600 to-teal-500 rounded-xl flex items-center justify-center shadow-md group-hover:shadow-ocean-500/40 transition-all">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6" />
            </svg>
          </div>
          <span class="text-xl font-extrabold text-slate-900 tracking-tight">Smart<span class="text-ocean-600">Cabana</span></span>
        </router-link>

        <!-- Desktop Nav Links -->
        <div class="hidden md:flex items-center space-x-1">
          <router-link
            to="/"
            class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-ocean-600 hover:bg-ocean-50 transition-all duration-200"
            active-class="text-ocean-600 bg-ocean-50"
            :exact="true"
          >
            Home
          </router-link>

          <template v-if="!authStore.isAuthenticated">
            <router-link
              to="/login"
              class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-ocean-600 hover:bg-ocean-50 transition-all duration-200"
            >
              Login
            </router-link>
            <router-link
              to="/register"
              class="ml-2 px-5 py-2 bg-gradient-to-r from-ocean-600 to-teal-500 hover:from-ocean-700 hover:to-teal-600 text-white text-sm font-semibold rounded-xl shadow-sm hover:shadow-ocean-500/30 transition-all duration-200"
            >
              Register
            </router-link>
          </template>

          <template v-else>
            <template v-if="authStore.isAdmin">
              <router-link
                to="/admin"
                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-ocean-600 hover:bg-ocean-50 transition-all duration-200"
              >
                Admin Panel
              </router-link>
            </template>
            <template v-else>
              <router-link
                to="/dashboard"
                class="px-4 py-2 rounded-lg text-sm font-medium text-slate-600 hover:text-ocean-600 hover:bg-ocean-50 transition-all duration-200"
                active-class="text-ocean-600 bg-ocean-50"
              >
                My Bookings
              </router-link>
            </template>

            <!-- Notification Bell -->
            <div class="relative ml-1" v-if="!authStore.isAdmin">
              <button
                @click="notifOpen = !notifOpen"
                class="relative p-2 rounded-lg text-slate-500 hover:text-ocean-600 hover:bg-ocean-50 transition-all duration-200"
                aria-label="Notifications"
              >
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span
                  v-if="notifStore.unreadCount > 0"
                  class="absolute top-1 right-1 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center ring-2 ring-white"
                >{{ notifStore.unreadCount > 9 ? '9+' : notifStore.unreadCount }}</span>
              </button>

              <!-- Notification Dropdown -->
              <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 scale-95 -translate-y-1"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-1"
              >
                <div
                  v-if="notifOpen"
                  v-click-outside="() => notifOpen = false"
                  class="absolute right-0 mt-2 w-80 bg-white rounded-2xl shadow-xl border border-slate-100 overflow-hidden z-60"
                  style="transform-origin: top right;"
                >
                  <NotificationDropdown @close="notifOpen = false" />
                </div>
              </Transition>
            </div>

            <!-- Profile Avatar Dropdown -->
            <div class="relative ml-2 pl-2 border-l border-slate-200">
              <button
                @click="profileOpen = !profileOpen"
                class="flex items-center space-x-2 group"
              >
                <div class="w-8 h-8 bg-gradient-to-br from-ocean-500 to-teal-500 rounded-full flex items-center justify-center text-white text-sm font-bold select-none ring-2 ring-ocean-100 group-hover:ring-ocean-200 transition-all">
                  {{ userInitial }}
                </div>
                <svg class="w-3 h-3 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
              </button>

              <Transition
                enter-active-class="transition-all duration-200 ease-out"
                enter-from-class="opacity-0 scale-95 -translate-y-1"
                enter-to-class="opacity-100 scale-100 translate-y-0"
                leave-active-class="transition-all duration-150 ease-in"
                leave-from-class="opacity-100 scale-100 translate-y-0"
                leave-to-class="opacity-0 scale-95 -translate-y-1"
              >
                <div
                  v-if="profileOpen"
                  v-click-outside="() => profileOpen = false"
                  class="absolute right-0 mt-2 w-52 bg-white rounded-2xl shadow-xl border border-slate-100 py-1.5 z-60"
                  style="transform-origin: top right;"
                >
                  <div class="px-4 py-2.5 border-b border-slate-100">
                    <p class="text-sm font-bold text-slate-900 truncate">{{ authStore.user?.name || 'User' }}</p>
                    <p class="text-xs text-slate-500 truncate">{{ authStore.user?.email }}</p>
                  </div>
                  <template v-if="!authStore.isAdmin">
                    <router-link to="/dashboard" @click="profileOpen = false"
                      class="flex items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-ocean-50 hover:text-ocean-600 transition-colors">
                      <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                      My Dashboard
                    </router-link>
                    <router-link to="/dashboard?tab=profile" @click="profileOpen = false"
                      class="flex items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-ocean-50 hover:text-ocean-600 transition-colors">
                      <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      </svg>
                      Profile Settings
                    </router-link>
                  </template>
                  <template v-else>
                    <router-link to="/admin" @click="profileOpen = false"
                      class="flex items-center px-4 py-2.5 text-sm text-slate-700 hover:bg-ocean-50 hover:text-ocean-600 transition-colors">
                      <svg class="w-4 h-4 mr-2.5 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                      </svg>
                      Admin Dashboard
                    </router-link>
                  </template>
                  <div class="border-t border-slate-100 mt-1">
                    <button @click="handleLogout" class="w-full flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors">
                      <svg class="w-4 h-4 mr-2.5 text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                      </svg>
                      Sign Out
                    </button>
                  </div>
                </div>
              </Transition>
            </div>
          </template>
        </div>

        <!-- Mobile menu toggle -->
        <button
          @click="mobileOpen = !mobileOpen"
          class="md:hidden p-2 rounded-lg text-slate-500 hover:text-ocean-600 hover:bg-ocean-50 transition-colors"
        >
          <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path v-if="!mobileOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
    </div>

    <!-- Mobile Menu -->
    <div v-if="mobileOpen" class="md:hidden border-t border-slate-100 bg-white/98 backdrop-blur-md px-4 py-3 space-y-1">
      <router-link to="/" @click="mobileOpen = false"
        class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-ocean-50 hover:text-ocean-600">Home</router-link>

      <template v-if="!authStore.isAuthenticated">
        <router-link to="/login" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-ocean-50 hover:text-ocean-600">Login</router-link>
        <router-link to="/register" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-white bg-gradient-to-r from-ocean-600 to-teal-500 text-center rounded-xl">Register</router-link>
      </template>
      <template v-else>
        <div class="px-4 py-2 border-b border-slate-100 mb-1">
          <p class="text-sm font-bold text-slate-900">{{ authStore.user?.name }}</p>
          <p class="text-xs text-slate-500">{{ authStore.user?.email }}</p>
        </div>
        <router-link v-if="!authStore.isAdmin" to="/dashboard" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-ocean-50 hover:text-ocean-600">My Bookings</router-link>
        <router-link v-if="!authStore.isAdmin" to="/dashboard?tab=profile" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-ocean-50 hover:text-ocean-600">Profile Settings</router-link>
        <router-link v-if="authStore.isAdmin" to="/admin" @click="mobileOpen = false"
          class="block px-4 py-2.5 rounded-lg text-sm font-medium text-slate-700 hover:bg-ocean-50 hover:text-ocean-600">Admin Panel</router-link>
        <button @click="handleLogout"
          class="block w-full text-left px-4 py-2.5 rounded-lg text-sm font-medium text-red-600 hover:bg-red-50">Sign Out</button>
      </template>
    </div>
  </nav>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useAuthStore } from '../store/authStore';
import { useNotificationStore } from '../store/notificationStore';
import { useRouter } from 'vue-router';
import NotificationDropdown from './NotificationDropdown.vue';

const authStore = useAuthStore();
const notifStore = useNotificationStore();
const router = useRouter();
const mobileOpen = ref(false);
const notifOpen   = ref(false);
const profileOpen = ref(false);

const userInitial = computed(() => {
  const name = authStore.user?.name || authStore.user?.email || 'U';
  return name.charAt(0).toUpperCase();
});

const handleLogout = async () => {
  mobileOpen.value = false;
  profileOpen.value = false;
  notifStore.stopPolling();
  await authStore.logout();
  router.push('/login');
};

// Polling for notifications
import { onMounted, onUnmounted, watch } from 'vue';

onMounted(() => {
  if (authStore.isAuthenticated && !authStore.isAdmin) {
    notifStore.fetchNotifications();
    notifStore.startPolling(30000); // 30s polling
  }
});

onUnmounted(() => {
  notifStore.stopPolling();
});

watch(() => authStore.isAuthenticated, (newVal) => {
  if (newVal && !authStore.isAdmin) {
    notifStore.fetchNotifications();
    notifStore.startPolling(30000);
  } else {
    notifStore.stopPolling();
  }
});

// click-outside directive
const vClickOutside = {
  mounted(el, binding) {
    el._clickOutsideHandler = (event) => {
      if (!el.contains(event.target)) binding.value(event);
    };
    document.addEventListener('mousedown', el._clickOutsideHandler);
  },
  unmounted(el) {
    document.removeEventListener('mousedown', el._clickOutsideHandler);
  },
};
</script>
