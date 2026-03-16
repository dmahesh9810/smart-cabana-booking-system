<template>
  <div>
    <!-- Header -->
    <div class="flex items-center justify-between px-4 py-3 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <svg class="w-5 h-5 text-ocean-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
        <span v-if="store.unreadCount" class="w-5 h-5 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
          {{ store.unreadCount > 9 ? '9+' : store.unreadCount }}
        </span>
      </div>
      <button
        v-if="store.unreadCount"
        @click="store.markAllAsRead()"
        class="text-xs font-semibold text-ocean-600 hover:text-ocean-700 transition-colors"
      >
        Mark all read
      </button>
    </div>

    <!-- Loading -->
    <div v-if="store.loading && !store.notifications.length" class="py-12 flex justify-center">
      <div class="animate-spin w-6 h-6 rounded-full border-2 border-ocean-200 border-t-ocean-600"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!store.notifications.length" class="py-10 text-center">
      <div class="text-3xl mb-2">🔔</div>
      <p class="text-sm text-slate-500 font-medium">All caught up!</p>
      <p class="text-xs text-slate-400 mt-1">No notifications yet.</p>
    </div>

    <!-- List -->
    <ul v-else class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
      <li
        v-for="n in store.notifications"
        :key="n.id"
        @click="handleClick(n)"
        class="px-4 py-4 flex items-start gap-3 cursor-pointer hover:bg-slate-50 transition-all border-l-2"
        :class="!n.read_at ? 'bg-ocean-50/40 border-ocean-500' : 'border-transparent'"
      >
        <!-- Icon -->
        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-lg" :class="iconBg(n.type)">
          {{ typeIcon(n.type) }}
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <div class="flex justify-between items-start">
            <p class="text-sm font-bold text-slate-900 leading-tight mb-0.5">{{ n.title }}</p>
            <span v-if="!n.read_at" class="w-2 h-2 bg-ocean-500 rounded-full shrink-0 ml-2 mt-1"></span>
          </div>
          <p class="text-xs text-slate-500 leading-relaxed line-clamp-2 mb-1">{{ n.message }}</p>
          <p class="text-[10px] text-slate-400 font-semibold uppercase tracking-wide">{{ formatTime(n.created_at) }}</p>
        </div>
      </li>
    </ul>

    <!-- Footer -->
    <div v-if="store.notifications.length" class="px-4 py-3 border-t border-slate-100 bg-slate-50/50">
      <router-link to="/dashboard?tab=notifications" @click="$emit('close')"
        class="text-xs font-bold text-ocean-600 hover:text-ocean-700 flex items-center justify-center gap-1.5 transition-colors">
        View All Notifications
        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
        </svg>
      </router-link>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useNotificationStore } from '../store/notificationStore';
import { useRouter } from 'vue-router';

const store = useNotificationStore();
const router = useRouter();
const emit = defineEmits(['close']);

onMounted(() => {
  store.fetchNotifications();
});

const handleClick = async (n) => {
  if (!n.read_at) {
    await store.markAsRead(n.id);
  }
  if (n.link) {
    router.push(n.link);
    emit('close');
  }
};

const typeIcon = (type) => {
  const icons = {
    booking_created: '📝',
    payment_success: '💳',
    booking_confirmed: '✅',
    booking_cancelled: '❌',
    booking_expired: '⌛',
  };
  return icons[type] ?? '🔔';
};

const iconBg = (type) => {
  const bg = {
    booking_created: 'bg-blue-50',
    payment_success: 'bg-teal-50',
    booking_confirmed: 'bg-emerald-50',
    booking_cancelled: 'bg-red-50',
    booking_expired: 'bg-amber-50',
  };
  return bg[type] ?? 'bg-slate-100';
};

const formatTime = (iso) => {
  if (!iso) return '';
  const diff  = Date.now() - new Date(iso).getTime();
  const mins  = Math.floor(diff / 60_000);
  const hours = Math.floor(diff / 3_600_000);
  const days  = Math.floor(diff / 86_400_000);
  if (mins < 1)   return 'Just now';
  if (mins < 60)  return `${mins}m ago`;
  if (hours < 24) return `${hours}h ago`;
  return `${days}d ago`;
};
</script>
