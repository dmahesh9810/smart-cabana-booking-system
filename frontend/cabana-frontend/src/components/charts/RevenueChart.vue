<template>
  <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
    <div class="flex items-center justify-between mb-4">
      <h3 class="text-lg font-bold text-slate-900">Revenue Analysis</h3>
      <div class="p-2 bg-green-50 rounded-lg text-green-600">
        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
      </div>
    </div>
    
    <div class="h-72 w-full">
      <apexchart v-if="hasData" type="bar" height="100%" :options="chartOptions" :series="series"></apexchart>
      <div v-else class="h-full flex items-center justify-center text-slate-400">
        No revenue data available.
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

// Process revenue data: group by month (or check_in_date month)
const chartData = computed(() => {
  const revenues = {}
  
  // Only consider paid/completed bookings for revenue (or all if we want to show projected)
  // Let's assume paid or completed means actual revenue. If property payment_status exists, use it.
  const paidBookings = props.bookings.filter(b => b.payment_status === 'paid' || b.booking_status === 'completed')

  // If there are no paid bookings, maybe fallback to all for demonstration purposes (you can adjust this logic)
  const sourceBookings = paidBookings.length > 0 ? paidBookings : props.bookings

  // Sort bookings
  const sortedBookings = [...sourceBookings].sort((a, b) => {
      const dateA = new Date(a.created_at || a.check_in_date).getTime()
      const dateB = new Date(b.created_at || b.check_in_date).getTime()
      return dateA - dateB
  })

  sortedBookings.forEach(booking => {
      const dateString = booking.created_at ? booking.created_at.split('T')[0] : booking.check_in_date
      if (!dateString) return
      
      const date = new Date(dateString)
      // Group by Month Year
      const monthYear = date.toLocaleDateString('en-US', { month: 'short', year: 'numeric' })
      
      // Parse total price
      const price = parseFloat(booking.total_price) || 0
      
      revenues[monthYear] = (revenues[monthYear] || 0) + price
  })
  
  return {
      labels: Object.keys(revenues),
      data: Object.values(revenues)
  }
})

const hasData = computed(() => chartData.value.labels.length > 0)

const series = computed(() => [{
  name: 'Revenue ($)',
  data: chartData.value.data.map(val => val.toFixed(2)) // Format to 2 decimal places
}])

const chartOptions = computed(() => ({
  chart: {
    type: 'bar',
    fontFamily: 'inherit',
    toolbar: { show: false }
  },
  colors: ['#10b981'], // Emerald-500
  plotOptions: {
    bar: {
      borderRadius: 4,
      columnWidth: '60%',
    }
  },
  dataLabels: { enabled: false },
  xaxis: {
    categories: chartData.value.labels,
    axisBorder: { show: false },
    axisTicks: { show: false },
    labels: { style: { colors: '#64748b' } }
  },
  yaxis: {
    labels: { 
      style: { colors: '#64748b' },
      formatter: (value) => `$${value}` 
    },
    min: 0,
    forceNiceScale: true
  },
  grid: {
    borderColor: '#f1f5f9',
    strokeDashArray: 4,
    yaxis: { lines: { show: true } }
  },
  tooltip: { 
    theme: 'light',
    y: { formatter: (value) => `$${value}` }
  }
}))
</script>

<script>
export default {
  components: {
    apexchart: VueApexCharts,
  }
}
</script>
