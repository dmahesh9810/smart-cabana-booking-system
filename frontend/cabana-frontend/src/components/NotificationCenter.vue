<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
    <!-- Header -->
    <div class="flex items-center justify-between px-5 py-4 border-b border-slate-100">
      <div class="flex items-center gap-2">
        <div class="relative">
          <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
          </svg>
          <span
            v-if="unreadCount"
            class="absolute -top-1.5 -right-1.5 w-4 h-4 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center"
          >{{ unreadCount }}</span>
        </div>
        <h3 class="text-sm font-bold text-slate-900">Notifications</h3>
      </div>
      <button
        v-if="unreadCount"
        @click="markAllRead"
        class="text-xs font-semibold text-indigo-600 hover:text-indigo-700 transition-colors"
      >
        Mark all read
      </button>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="py-8 flex justify-center">
      <div class="animate-spin w-5 h-5 rounded-full border-2 border-indigo-200 border-t-indigo-600"></div>
    </div>

    <!-- Empty -->
    <div v-else-if="!notifications.length" class="py-10 text-center">
      <div class="text-3xl mb-2">🔔</div>
      <p class="text-sm text-slate-500 font-medium">All caught up!</p>
      <p class="text-xs text-slate-400 mt-1">No notifications yet.</p>
    </div>

    <!-- List -->
    <ul v-else class="divide-y divide-slate-50 max-h-96 overflow-y-auto">
      <li
        v-for="n in notifications"
        :key="n.id"
        @click="markRead(n)"
        class="px-5 py-4 flex items-start gap-3 cursor-pointer hover:bg-slate-50 transition-colors"
        :class="{ 'bg-indigo-50/40': !n.read }"
      >
        <!-- Icon -->
        <div
          class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-lg"
          :class="iconBg(n.type)"
        >
          {{ typeIcon(n.type) }}
        </div>

        <!-- Content -->
        <div class="flex-1 min-w-0">
          <p class="text-sm font-semibold text-slate-800 leading-snug">{{ n.title }}</p>
          <p class="text-xs text-slate-500 mt-0.5 leading-relaxed">{{ n.message }}</p>
          <p class="text-[10px] text-slate-400 mt-1.5 font-medium">{{ formatTime(n.created_at) }}</p>
        </div>

        <!-- Unread dot -->
        <span v-if="!n.read" class="w-2 h-2 bg-indigo-500 rounded-full shrink-0 mt-1.5"></span>
      </li>
    </ul>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

// ── Local storage-backed notification store ────────────────────────
// In a real app this would sync with a backend `/api/v1/notifications` endpoint.
// Here we maintain a lightweight localStorage-based feed that other parts of the
// app can push to via window.dispatchEvent(new CustomEvent('cabana:notify', { detail: {...} })).

const STORAGE_KEY = 'cabana_notifications';

const notifications = ref([]);
const loading       = ref(false);

const unreadCount = computed(() => notifications.value.filter(n => !n.read).length);

// ── Load on mount ──────────────────────────────────────────────────
onMounted(() => {
    load();
    // Listen for new notifications pushed by other components
    window.addEventListener('cabana:notify', handleIncoming);
});

const load = () => {
    try {
        const stored = localStorage.getItem(STORAGE_KEY);
        notifications.value = stored ? JSON.parse(stored) : [];
    } catch {
        notifications.value = [];
    }
};

const save = () => {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(notifications.value));
};

// ── Add notification (called via custom event) ─────────────────────
const handleIncoming = (event) => {
    const { type = 'info', title, message } = event.detail ?? {};
    if (!title) return;

    notifications.value.unshift({
        id:         Date.now(),
        type,
        title,
        message,
        read:       false,
        created_at: new Date().toISOString(),
    });

    // Keep last 30 only
    if (notifications.value.length > 30) {
        notifications.value = notifications.value.slice(0, 30);
    }
    save();
};

// ── Actions ────────────────────────────────────────────────────────
const markRead = (n) => {
    if (!n.read) {
        n.read = true;
        save();
    }
};

const markAllRead = () => {
    notifications.value.forEach(n => { n.read = true; });
    save();
};

// ── Helpers ────────────────────────────────────────────────────────
const typeIcon = (type) => {
    const map = {
        booking_confirmed: '✅',
        payment_received:  '💳',
        booking_reminder:  '⏰',
        booking_cancelled: '❌',
        info:              '📣',
    };
    return map[type] ?? '🔔';
};

const iconBg = (type) => {
    const map = {
        booking_confirmed: 'bg-emerald-50',
        payment_received:  'bg-blue-50',
        booking_reminder:  'bg-amber-50',
        booking_cancelled: 'bg-red-50',
        info:              'bg-indigo-50',
    };
    return map[type] ?? 'bg-slate-50';
};

const formatTime = (iso) => {
    if (!iso) return '';
    const diff = Date.now() - new Date(iso).getTime();
    const mins  = Math.floor(diff / 60_000);
    const hours = Math.floor(diff / 3_600_000);
    const days  = Math.floor(diff / 86_400_000);
    if (mins < 1)   return 'Just now';
    if (mins < 60)  return `${mins}m ago`;
    if (hours < 24) return `${hours}h ago`;
    return `${days}d ago`;
};

// ── Expose push helper for programmatic use ────────────────────────
defineExpose({
    push: (detail) => window.dispatchEvent(new CustomEvent('cabana:notify', { detail })),
});
</script>
