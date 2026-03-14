<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-base font-bold text-slate-900">Booking Trends</h3>
        <p class="text-xs text-slate-400 mt-0.5">Monthly bookings — last 6 months</p>
      </div>
      <div class="w-9 h-9 bg-indigo-50 rounded-xl flex items-center justify-center">
        <svg class="h-5 w-5 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z"/>
        </svg>
      </div>
    </div>

    <div class="h-64 w-full">
      <apexchart
        v-if="hasData"
        type="area"
        height="100%"
        :options="chartOptions"
        :series="series"
      />
      <div v-else class="h-full flex flex-col items-center justify-center gap-2 text-slate-300">
        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
        </svg>
        <p class="text-sm">No booking data yet</p>
      </div>
    </div>
  </div>
</template>

<script>
import VueApexCharts from 'vue3-apexcharts';
export default { components: { apexchart: VueApexCharts } };
</script>

<script setup>
import { computed } from 'vue';

const props = defineProps({
    chartData: {
        type: Object,
        default: () => ({}),
    },
});

// chartData is { "Mar 2026": 5, "Apr 2026": 12, ... }
const labels  = computed(() => Object.keys(props.chartData ?? {}));
const values  = computed(() => Object.values(props.chartData ?? {}));
const hasData = computed(() => labels.value.length > 0 && values.value.some(v => v > 0));

const series = computed(() => [{
    name: 'Bookings',
    data: values.value,
}]);

const chartOptions = computed(() => ({
    chart: {
        type: 'area',
        fontFamily: 'inherit',
        toolbar: { show: false },
        zoom: { enabled: false },
        animations: { enabled: true, speed: 600 },
    },
    colors: ['#4f46e5'],
    dataLabels: { enabled: false },
    stroke: { curve: 'smooth', width: 2.5 },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.35,
            opacityTo: 0.03,
            stops: [0, 95, 100],
        },
    },
    xaxis: {
        categories: labels.value,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#94a3b8', fontSize: '11px' } },
    },
    yaxis: {
        labels: {
            style: { colors: '#94a3b8', fontSize: '11px' },
            formatter: (v) => Math.round(v),
        },
        min: 0,
        forceNiceScale: true,
        decimalsInFloat: 0,
    },
    grid: {
        borderColor: '#f1f5f9',
        strokeDashArray: 4,
        yaxis: { lines: { show: true } },
        xaxis: { lines: { show: false } },
        padding: { left: 0, right: 0 },
    },
    tooltip: {
        theme: 'light',
        y: { formatter: (v) => `${v} booking${v !== 1 ? 's' : ''}` },
    },
    markers: {
        size: 4,
        colors: ['#4f46e5'],
        strokeColors: '#fff',
        strokeWidth: 2,
    },
}));
</script>
