<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-slate-900">Booking Trends</h3>
      <div class="p-2 bg-indigo-50 rounded-lg text-indigo-600">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 12l3-3 3 3 4-4M8 21l4-4 4 4M3 4h18M4 4h16v12a1 1 0 01-1 1H5a1 1 0 01-1-1V4z" />
        </svg>
      </div>
    </div>
    
    <div class="h-72 w-full">
      <apexchart v-if="hasData" type="area" height="100%" :options="chartOptions" :series="series"></apexchart>
      <div v-else class="h-full flex items-center justify-center text-slate-400">
        No booking data available.
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import VueApexCharts from 'vue3-apexcharts'

const props = defineProps({
  bookings: {
    type: Array,
    required: true,
    default: () => []
  }
})

// Process bookings data: group by created_at date (or check_in_date if preferred)
const chartData = computed(() => {
  const counts = {}
  
  // Sort bookings by date
  const sortedBookings = [...props.bookings].sort((a, b) => {
      // Use check_in_date or a created_at property, fallback to check_in_date to show activity
      const dateA = new Date(a.created_at || a.check_in_date).setHours(0,0,0,0)
      const dateB = new Date(b.created_at || b.check_in_date).setHours(0,0,0,0)
      return dateA - dateB
  })

  sortedBookings.forEach(booking => {
      // Assuming booking has a check_in_date or created_at (we'll use check_in_date as an example of booking activity)
      const dateString = booking.created_at ? booking.created_at.split('T')[0] : booking.check_in_date
      
      if (!dateString) return
      
      // We will parse the date properly to display on chart
      // Formatter can be simple YYYY-MM-DD
      const date = new Date(dateString)
      const formattedDate = date.toLocaleDateString('en-US', { month: 'short', day: 'numeric' })
      
      counts[formattedDate] = (counts[formattedDate] || 0) + 1
  })
  
  // Extract labels and data series
  return {
      labels: Object.keys(counts),
      data: Object.values(counts)
  }
})

const hasData = computed(() => chartData.value.labels.length > 0)

const series = computed(() => [{
  name: 'Bookings',
  data: chartData.value.data
}])

const chartOptions = computed(() => ({
  chart: {
    type: 'area',
    fontFamily: 'inherit',
    toolbar: { show: false },
    zoom: { enabled: false }
  },
  colors: ['#4f46e5'], // Indigo-600
  dataLabels: { enabled: false },
  stroke: { curve: 'smooth', width: 2 },
  fill: {
    type: 'gradient',
    gradient: {
      shadeIntensity: 1,
      opacityFrom: 0.4,
      opacityTo: 0.05,
      stops: [0, 90, 100]
    }
  },
  xaxis: {
    categories: chartData.value.labels,
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#64748b' } }
  },
  yaxis: {
    labels: { style: { colors: '#64748b' } },
    min: 0,
    forceNiceScale: true,
    decimalsInFloat: 0
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4,
    yaxis: { lines: { show: true } }
  },
  tooltip: { theme: 'light' }
}))
</script>

<script>
// Install apexcharts locally
export default {
  components: {
    apexchart: VueApexCharts,
  }
}
</script>
