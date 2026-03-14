<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6 hover:shadow-md transition-shadow">
    <div class="flex items-center justify-between mb-4">
      <div>
        <h3 class="text-base font-bold text-slate-900">Revenue Analysis</h3>
        <p class="text-xs text-slate-400 mt-0.5">Monthly revenue — last 6 months (LKR)</p>
      </div>
      <div class="w-9 h-9 bg-emerald-50 rounded-xl flex items-center justify-center">
        <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
    </div>

    <div class="h-64 w-full">
      <apexchart
        v-if="hasData"
        type="bar"
        height="100%"
        :options="chartOptions"
        :series="series"
      />
      <div v-else class="h-full flex flex-col items-center justify-center gap-2 text-slate-300">
        <svg class="w-10 h-10" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm">No revenue data yet</p>
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

// chartData is { "Mar 2026": 45000, "Apr 2026": 120000, ... }
const labels  = computed(() => Object.keys(props.chartData ?? {}));
const values  = computed(() => Object.values(props.chartData ?? {}).map(v => Number(v)));
const hasData = computed(() => labels.value.length > 0 && values.value.some(v => v > 0));

const series = computed(() => [{
    name: 'Revenue (LKR)',
    data: values.value,
}]);

// LKR compact formatter for y-axis
const formatLKRShort = (val) => {
    if (val >= 1_000_000) return `Rs. ${(val / 1_000_000).toFixed(1)}M`;
    if (val >= 1_000)     return `Rs. ${(val / 1_000).toFixed(0)}K`;
    return `Rs. ${val}`;
};

const formatLKRFull = (val) =>
    new Intl.NumberFormat('si-LK', { style: 'currency', currency: 'LKR', minimumFractionDigits: 0 }).format(val);

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        fontFamily: 'inherit',
        toolbar: { show: false },
        animations: { enabled: true, speed: 600 },
    },
    colors: ['#10b981'],
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: '55%',
        },
    },
    dataLabels: { enabled: false },
    xaxis: {
        categories: labels.value,
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: { style: { colors: '#94a3b8', fontSize: '11px' } },
    },
    yaxis: {
        labels: {
            style: { colors: '#94a3b8', fontSize: '11px' },
            formatter: formatLKRShort,
        },
        min: 0,
        forceNiceScale: true,
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
        y: { formatter: (v) => formatLKRFull(v) },
    },
    states: {
        hover: { filter: { type: 'lighten', value: 0.1 } },
    },
}));
</script>
