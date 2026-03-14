<template>
  <div class="space-y-6">

    <!-- Loading -->
    <div v-if="loading" class="flex justify-center items-center py-16">
      <div class="flex flex-col items-center gap-3">
        <div class="animate-spin rounded-full h-10 w-10 border-[3px] border-indigo-200 border-t-indigo-600"></div>
        <p class="text-sm text-slate-400">Loading analytics...</p>
      </div>
    </div>

    <!-- Error -->
    <div v-else-if="adminStore.error" class="flex items-center gap-3 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl text-sm">
      <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ adminStore.error }}
    </div>

    <template v-else-if="stats">

      <!-- Page Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-slate-900">Analytics Dashboard</h1>
          <p class="text-slate-500 text-sm mt-1">System overview and performance metrics.</p>
        </div>
        <div class="flex items-center gap-2 text-xs text-slate-400 bg-slate-100 px-3 py-2 rounded-xl">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Updated just now
        </div>
      </div>

      <!-- Primary KPI Cards  -->
      <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-5">

        <StatCard
          label="Total Revenue"
          :value="formatLKR(stats.total_revenue)"
          sub-label="All time paid revenue"
          icon-bg="from-emerald-500 to-teal-500"
          trend-label="+This month"
          :trend-value="formatLKR(stats.revenue_this_month)"
        >
          <template #icon>
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </template>
        </StatCard>

        <StatCard
          label="Total Bookings"
          :value="stats.total_bookings"
          sub-label="All reservations"
          icon-bg="from-indigo-500 to-violet-500"
          trend-label="This month"
          :trend-value="stats.bookings_this_month"
        >
          <template #icon>
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </template>
        </StatCard>

        <StatCard
          label="Total Users"
          :value="stats.total_users"
          sub-label="Registered accounts"
          icon-bg="from-purple-500 to-pink-500"
          trend-label="Upcoming stays"
          :trend-value="stats.upcoming_bookings"
        >
          <template #icon>
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
            </svg>
          </template>
        </StatCard>

        <StatCard
          label="Active Cabanas"
          :value="stats.active_cabanas"
          :sub-label="`of ${stats.total_cabanas} total`"
          icon-bg="from-amber-500 to-orange-500"
          trend-label="Avg rating"
          :trend-value="stats.average_rating + ' ★'"
        >
          <template #icon>
            <svg class="h-6 w-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
          </template>
        </StatCard>

      </div>

      <!-- Charts Row -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
        <BookingChart :chart-data="stats.bookings_per_month" />
        <RevenueChart :chart-data="stats.revenue_per_month" />
      </div>

      <!-- Highlight Row -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">

        <!-- Most Booked Cabana -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-indigo-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
              </svg>
            </div>
            <p class="text-sm font-semibold text-slate-600">Most Booked Cabana</p>
          </div>
          <template v-if="stats.most_booked_cabana">
            <p class="text-xl font-bold text-slate-900 mb-1">{{ stats.most_booked_cabana.name }}</p>
            <div class="flex items-center gap-2 mt-3">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 text-indigo-700 rounded-full text-sm font-semibold">
                📅 {{ stats.most_booked_cabana.booking_count }} confirmed bookings
              </span>
            </div>
          </template>
          <p v-else class="text-slate-400 text-sm">No booking data yet.</p>
        </div>

        <!-- Top Rated Cabana -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-yellow-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 20 20">
                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
              </svg>
            </div>
            <p class="text-sm font-semibold text-slate-600">Top Rated Cabana</p>
          </div>
          <template v-if="stats.top_rated_cabana">
            <p class="text-xl font-bold text-slate-900 mb-1">{{ stats.top_rated_cabana.name }}</p>
            <div class="flex items-center gap-2 mt-3">
              <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-yellow-50 text-yellow-700 rounded-full text-sm font-semibold">
                ⭐ {{ stats.top_rated_cabana.avg_rating }} / 5
              </span>
              <span class="text-xs text-slate-400">{{ stats.top_rated_cabana.review_count }} reviews</span>
            </div>
          </template>
          <p v-else class="text-slate-400 text-sm">No review data yet.</p>
        </div>

        <!-- Booking Health -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 hover:shadow-md transition-shadow">
          <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center">
              <svg class="w-5 h-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>
            <p class="text-sm font-semibold text-slate-600">Booking Health</p>
          </div>
          <div class="space-y-3">
            <div class="flex justify-between items-center text-sm">
              <span class="text-slate-600">Confirmed</span>
              <span class="font-bold text-emerald-700">{{ stats.confirmed_bookings }}</span>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-2">
              <div
                class="bg-emerald-500 h-2 rounded-full transition-all duration-700"
                :style="{ width: stats.total_bookings ? `${Math.round((stats.confirmed_bookings / stats.total_bookings) * 100)}%` : '0%' }"
              ></div>
            </div>
            <p class="text-xs text-slate-400">
              {{ stats.total_bookings ? Math.round((stats.confirmed_bookings / stats.total_bookings) * 100) : 0 }}% confirmation rate
            </p>
            <div class="pt-2 border-t border-slate-100">
              <div class="flex justify-between text-sm">
                <span class="text-slate-600">Upcoming Stays</span>
                <span class="font-bold text-indigo-700">{{ stats.upcoming_bookings }}</span>
              </div>
            </div>
          </div>
        </div>

      </div>

    </template>

  </div>
</template>

<script setup>
import { onMounted, computed, defineComponent, h } from 'vue';
import { useAdminStore } from '../store/adminStore';
import BookingChart from '../components/charts/BookingChart.vue';
import RevenueChart from '../components/charts/RevenueChart.vue';

const adminStore = useAdminStore();
const stats   = computed(() => adminStore.dashboardStats);
const loading = computed(() => adminStore.loading && !stats.value);

const formatLKR = (value) => {
    if (value === null || value === undefined) return 'Rs. 0';
    return new Intl.NumberFormat('si-LK', {
        style: 'currency',
        currency: 'LKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

onMounted(() => {
    adminStore.fetchDashboardStats();
});

// ── Inline StatCard component ──────────────────────────────────
const StatCard = defineComponent({
    props: {
        label:      String,
        value:      [String, Number],
        subLabel:   String,
        iconBg:     String,
        trendLabel: String,
        trendValue: [String, Number],
    },
    setup(props, { slots }) {
        return () => h('div', {
            class: 'bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col gap-4 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 cursor-default',
        }, [
            h('div', { class: 'flex items-start justify-between' }, [
                h('div', [
                    h('p', { class: 'text-xs font-semibold text-slate-400 uppercase tracking-wider mb-1' }, props.label),
                    h('p', { class: 'text-3xl font-black text-slate-900 leading-none' }, props.value),
                    props.subLabel
                        ? h('p', { class: 'text-xs text-slate-400 mt-1.5' }, props.subLabel)
                        : null,
                ]),
                h('div', {
                    class: `w-12 h-12 rounded-2xl bg-gradient-to-br ${props.iconBg} flex items-center justify-center shadow-lg`,
                }, slots.icon ? slots.icon() : null),
            ]),
            props.trendLabel
                ? h('div', { class: 'flex items-center justify-between pt-3 border-t border-slate-50 text-xs' }, [
                    h('span', { class: 'text-slate-400' }, props.trendLabel),
                    h('span', { class: 'font-bold text-slate-700' }, props.trendValue),
                ])
                : null,
        ]);
    },
});
</script>
