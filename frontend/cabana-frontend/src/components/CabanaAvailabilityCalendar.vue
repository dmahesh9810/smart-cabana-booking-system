<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <!-- Header -->
    <div class="flex items-center justify-between mb-5">
      <div>
        <h3 class="text-lg font-bold text-slate-900">Availability Calendar</h3>
        <p class="text-xs text-slate-400 mt-0.5">Showing confirmed bookings for the next 3 months</p>
      </div>
      <!-- Legend -->
      <div class="flex items-center gap-4 text-xs font-medium">
        <span class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded-sm bg-red-500 inline-block"></span>
          <span class="text-slate-600">Booked</span>
        </span>
        <span class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded-sm bg-emerald-100 border border-emerald-300 inline-block"></span>
          <span class="text-slate-600">Available</span>
        </span>
        <span class="flex items-center gap-1.5">
          <span class="w-3 h-3 rounded-sm bg-indigo-600 inline-block"></span>
          <span class="text-slate-600">Today</span>
        </span>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-12 gap-3">
      <div class="animate-spin rounded-full h-8 w-8 border-[3px] border-indigo-200 border-t-indigo-600"></div>
      <p class="text-xs text-slate-400">Loading availability...</p>
    </div>

    <!-- Error -->
    <div v-else-if="error" class="flex items-center gap-2 text-sm text-red-600 bg-red-50 border border-red-100 rounded-xl p-3 mb-4">
      <svg class="w-4 h-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ error }}
    </div>

    <!-- Month Grids -->
    <div v-else class="space-y-8">
      <div
        v-for="(month, mIdx) in calendarMonths"
        :key="mIdx"
        class="select-none"
      >
        <!-- Month Header + Nav -->
        <div class="flex items-center justify-between mb-3">
          <button
            v-if="mIdx === 0"
            @click="prevMonth"
            :disabled="!canGoPrev"
            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 disabled:opacity-30 disabled:cursor-not-allowed transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
          </button>
          <div v-else class="w-8"></div>

          <h4 class="text-sm font-bold text-slate-800 tracking-wide">
            {{ month.label }}
          </h4>

          <button
            v-if="mIdx === calendarMonths.length - 1"
            @click="nextMonth"
            class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-500 transition-colors"
          >
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
          </button>
          <div v-else class="w-8"></div>
        </div>

        <!-- Day-of-week headers -->
        <div class="grid grid-cols-7 mb-1">
          <div
            v-for="day in ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat']"
            :key="day"
            class="text-center text-[10px] font-semibold text-slate-400 uppercase tracking-wider py-1"
          >
            {{ day }}
          </div>
        </div>

        <!-- Day cells -->
        <div class="grid grid-cols-7 gap-0.5">
          <!-- Leading empty cells -->
          <div v-for="n in month.startDay" :key="'empty-' + n"></div>

          <!-- Actual days -->
          <div
            v-for="day in month.days"
            :key="day.iso"
            class="aspect-square flex items-center justify-center rounded-lg text-xs font-medium transition-all relative"
            :class="dayClass(day)"
            :title="dayTitle(day)"
          >
            {{ day.date }}
            <!-- Today dot indicator -->
            <span
              v-if="day.isToday"
              class="absolute bottom-1 left-1/2 -translate-x-1/2 w-1 h-1 rounded-full bg-indigo-300"
            ></span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch } from 'vue';
import api from '../api/axios';

const props = defineProps({
    cabanaId: {
        type: [String, Number],
        required: true,
    },
});

// ── State ──────────────────────────────────────────────────────
const bookedDates  = ref(new Set());
const loading      = ref(false);
const error        = ref(null);

// Track which month window to display (offset from current month)
const monthOffset = ref(0);
const MONTHS_SHOWN = 1; // Show one full month at a time; navigate with arrows

// ── Fetch availability ─────────────────────────────────────────
const fetchAvailability = async () => {
    if (!props.cabanaId) return;
    loading.value = true;
    error.value   = null;
    try {
        const { data } = await api.get(`/cabanas/${props.cabanaId}/availability`);
        bookedDates.value = new Set(data.booked_dates ?? []);
    } catch (err) {
        error.value = 'Failed to load availability data.';
        console.error(err);
    } finally {
        loading.value = false;
    }
};

onMounted(fetchAvailability);
watch(() => props.cabanaId, fetchAvailability);

// ── Calendar calculations ──────────────────────────────────────
const todayISO = new Date().toISOString().split('T')[0];

// Build array of months to display (currently 1 month, navigated via offset)
const calendarMonths = computed(() => {
    const months = [];
    const base   = new Date();
    base.setDate(1);

    for (let i = 0; i < MONTHS_SHOWN; i++) {
        const d = new Date(base.getFullYear(), base.getMonth() + monthOffset.value + i, 1);
        months.push(buildMonth(d));
    }
    return months;
});

const canGoPrev = computed(() => monthOffset.value > 0);

const prevMonth = () => { if (canGoPrev.value) monthOffset.value--; };
const nextMonth = () => { monthOffset.value++; };

function buildMonth(firstOfMonth) {
    const year  = firstOfMonth.getFullYear();
    const month = firstOfMonth.getMonth();

    const label = firstOfMonth.toLocaleDateString('en-US', { month: 'long', year: 'numeric' });

    // Day of week of the 1st (0=Sun)
    const startDay = firstOfMonth.getDay();

    // Number of days in month
    const daysInMonth = new Date(year, month + 1, 0).getDate();

    const days = [];
    for (let d = 1; d <= daysInMonth; d++) {
        const iso  = `${year}-${String(month + 1).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
        days.push({
            date:     d,
            iso,
            isToday:  iso === todayISO,
            isPast:   iso < todayISO,
            isBooked: bookedDates.value.has(iso),
        });
    }

    return { label, startDay, days };
}

// ── Styling helpers ────────────────────────────────────────────
const dayClass = (day) => {
    if (day.isBooked) {
        return 'bg-red-500 text-white cursor-not-allowed';
    }
    if (day.isToday) {
        return 'bg-indigo-600 text-white font-bold ring-2 ring-indigo-300 ring-offset-1';
    }
    if (day.isPast) {
        return 'text-slate-300 cursor-default';
    }
    return 'bg-emerald-50 text-emerald-800 hover:bg-emerald-100 hover:scale-110 cursor-default border border-emerald-200';
};

const dayTitle = (day) => {
    if (day.isBooked) return `${day.iso} — Booked`;
    if (day.isPast)   return `${day.iso} — Past`;
    if (day.isToday)  return `${day.iso} — Today`;
    return             `${day.iso} — Available`;
};
</script>
