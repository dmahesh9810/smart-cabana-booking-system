<template>
  <div class="space-y-6">
    <!-- Header & Tabs -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">System Reports</h1>
        <p class="text-slate-500 text-sm">Generate and export business insights</p>
      </div>
      
      <div class="flex bg-white p-1 rounded-xl shadow-sm border border-slate-200">
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-4 py-2 text-sm font-medium rounded-lg transition-all"
          :class="activeTab === tab.id ? 'bg-indigo-600 text-white shadow-md' : 'text-slate-500 hover:text-slate-700'"
        >
          {{ tab.name }}
        </button>
      </div>
    </div>

    <!-- Filters Section -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-200 p-6">
      <div class="grid grid-cols-1 md:grid-cols-4 gap-4 items-end">
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Start Date</label>
          <input type="date" v-model="filters.start_date" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">End Date</label>
          <input type="date" v-model="filters.end_date" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all">
        </div>
        <div v-if="activeTab === 'bookings'">
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
          <select v-model="filters.status" class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none">
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button @click="fetchReportData" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
            Generate
          </button>
          <div class="relative group">
            <button class="bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold py-2 px-4 rounded-xl transition-all flex items-center gap-2">
               <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
               Export
            </button>
            <div class="absolute right-0 mt-2 w-48 bg-white border border-slate-200 rounded-xl shadow-xl z-50 hidden group-hover:block overflow-hidden">
               <button @click="exportData('excel')" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 text-slate-700 flex items-center gap-2">
                 <span class="text-green-600 font-bold italic">Xls</span> Excel Spreadsheet
               </button>
               <button @click="exportData('pdf')" class="w-full text-left px-4 py-2 text-sm hover:bg-slate-50 text-slate-700 flex items-center gap-2">
                 <span class="text-red-600 font-bold italic">Pdf</span> PDF Document
               </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Active Report Content -->
    <div v-if="loading" class="flex justify-center items-center py-20">
       <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
    </div>

    <div v-else class="space-y-6">
      <!-- Bookings Table -->
      <div v-if="activeTab === 'bookings'" class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-slate-50 border-b border-slate-200">
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Booking ID</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Cabana</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Guest</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Check In</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Status</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-100">
            <tr v-for="booking in reportData" :key="booking.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-4 font-bold text-slate-700">{{ booking.booking_ref }}</td>
              <td class="px-6 py-4 text-slate-600">{{ booking.cabana?.name }}</td>
              <td class="px-6 py-4 text-slate-600">{{ booking.user?.name }}</td>
              <td class="px-6 py-4 text-slate-600">{{ booking.check_in }}</td>
              <td class="px-6 py-4 font-bold text-indigo-600">Rs. {{ booking.total_amount.toLocaleString() }}</td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 text-[10px] font-black uppercase rounded-full tracking-tighter" :class="statusBadge(booking.status)">
                  {{ booking.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Revenue Charts & Table -->
      <div v-if="activeTab === 'revenue'" class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
          <apexchart height="350" type="line" :options="revenueOptions" :series="revenueSeries"></apexchart>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
           <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Month</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Gross Revenue</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Commission</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Owner Earnings</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="row in reportData" :key="row.month" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 font-bold text-slate-700">{{ row.month }}</td>
                <td class="px-6 py-4 text-slate-600">Rs. {{ parseFloat(row.gross_revenue).toLocaleString() }}</td>
                <td class="px-6 py-4 font-bold text-emerald-600">Rs. {{ parseFloat(row.platform_commission).toLocaleString() }}</td>
                <td class="px-6 py-4 text-slate-600">Rs. {{ parseFloat(row.owner_earnings).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- Occupancy Charts & Table -->
      <div v-if="activeTab === 'occupancy'" class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
          <apexchart height="350" type="bar" :options="occupancyOptions" :series="occupancySeries"></apexchart>
        </div>
        <div class="bg-white rounded-2xl shadow-sm border border-slate-200 overflow-hidden">
           <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 border-b border-slate-200">
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Cabana Name</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Total Days</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Booked Days</th>
                <th class="px-6 py-4 text-xs font-bold text-slate-500 uppercase tracking-wider">Occupancy %</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr v-for="row in reportData" :key="row.cabana_name" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 font-bold text-slate-700">{{ row.cabana_name }}</td>
                <td class="px-6 py-4 text-slate-600">{{ row.total_days }}</td>
                <td class="px-6 py-4 text-slate-600">{{ row.booked_days }}</td>
                <td class="px-6 py-4 font-bold" :class="row.occupancy_percentage > 70 ? 'text-emerald-600' : 'text-amber-600'">
                  {{ row.occupancy_percentage }}%
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, watch, computed } from 'vue';
import axios from '../api/axios';
import { useToast } from 'vue-toastification';

const toast = useToast();
const activeTab = ref('bookings');
const loading = ref(false);
const reportData = ref([]);

const tabs = [
  { id: 'bookings', name: 'Booking Report' },
  { id: 'revenue', name: 'Revenue Report' },
  { id: 'occupancy', name: 'Occupancy' }
];

const filters = ref({
  start_date: new Date(new Date().setDate(new Date().getDate() - 30)).toISOString().split('T')[0],
  end_date: new Date().toISOString().split('T')[0],
  status: '',
  cabana_id: ''
});

const statusBadge = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-100 text-emerald-700';
    case 'confirmed': return 'bg-blue-100 text-blue-700';
    case 'cancelled': return 'bg-red-100 text-red-700';
    default: return 'bg-slate-100 text-slate-700';
  }
};

const fetchReportData = async () => {
  loading.value = true;
  try {
    const params = { ...filters.value };
    const response = await axios.get(`/admin/reports/${activeTab.value}`, { params });
    reportData.value = response.data.data.data || response.data.data; // Handle pagination vs object
  } catch (error) {
    toast.error('Failed to load report data');
  } finally {
    loading.value = false;
  }
};

const exportData = (format) => {
  const params = new URLSearchParams({ ...filters.value, format }).toString();
  const endpoint = activeTab.value === 'bookings' ? 'bookings' : (activeTab.value === 'revenue' ? 'revenue' : 'occupancy');
  window.open(`${import.meta.env.VITE_API_BASE_URL}/admin/reports/export/${endpoint}?${params}`, '_blank');
};

// Charts Logic
const revenueSeries = computed(() => {
  if (activeTab.value !== 'revenue') return [];
  return [
    { name: 'Gross Revenue', data: reportData.value.map(r => r.gross_revenue) },
    { name: 'Platform Commission', data: reportData.value.map(r => r.platform_commission) }
  ];
});

const revenueOptions = {
  chart: { toolbar: { show: false }, zoom: { enabled: false } },
  stroke: { curve: 'smooth', width: 3 },
  colors: ['#4f46e5', '#10b981'],
  xaxis: { categories: computed(() => reportData.value.map(r => r.month)) },
  yaxis: { labels: { formatter: (val) => `Rs. ${val.toLocaleString()}` } },
  tooltip: { y: { formatter: (val) => `Rs. ${val.toLocaleString()}` } }
};

const occupancySeries = computed(() => {
  if (activeTab.value !== 'occupancy') return [];
  return [{ name: 'Occupancy Rate', data: reportData.value.map(r => r.occupancy_percentage) }];
});

const occupancyOptions = {
  chart: { toolbar: { show: false } },
  plotOptions: { bar: { borderRadius: 10, columnWidth: '50%' } },
  colors: ['#6366f1'],
  xaxis: { categories: computed(() => reportData.value.map(r => r.cabana_name)) },
  yaxis: { max: 100, labels: { formatter: (val) => `${val}%` } }
};

watch(activeTab, () => {
  reportData.value = [];
  fetchReportData();
});

onMounted(fetchReportData);
</script>
