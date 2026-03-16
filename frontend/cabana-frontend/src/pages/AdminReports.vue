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
          @click="switchTab(tab.id)"
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
          <input
            type="date"
            v-model="filters.start_date"
            :max="filters.end_date"
            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
          >
        </div>
        <div>
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">End Date</label>
          <input
            type="date"
            v-model="filters.end_date"
            :min="filters.start_date"
            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all"
          >
        </div>
        <div v-if="activeTab === 'bookings'">
          <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Status</label>
          <select
            v-model="filters.status"
            class="w-full px-4 py-2 bg-slate-50 border border-slate-200 rounded-xl focus:ring-2 focus:ring-indigo-500 outline-none transition-all appearance-none"
          >
            <option value="">All Statuses</option>
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
            <option value="completed">Completed</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="flex gap-2">
          <button
            @click="fetchReportData"
            :disabled="loading"
            class="flex-1 bg-indigo-600 hover:bg-indigo-700 disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold py-2 rounded-xl transition-all shadow-md shadow-indigo-100 flex items-center justify-center gap-2"
          >
            <svg v-if="!loading" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
            </svg>
            {{ loading ? 'Loading...' : 'Generate' }}
          </button>

          <!-- Export dropdown – click-toggle (group-hover gap breaks on CSS-only approach) -->
          <div class="relative" v-if="activeTab !== 'occupancy'" v-click-outside="() => showExportMenu = false">
            <button
              @click="showExportMenu = !showExportMenu"
              :disabled="exportLoading || reportData.length === 0"
              class="cursor-pointer bg-slate-100 hover:bg-indigo-600 hover:text-white disabled:opacity-50 disabled:cursor-not-allowed text-slate-700 font-bold py-2 px-4 rounded-xl transition-all duration-200 flex items-center gap-2 select-none"
            >
              <svg v-if="!exportLoading" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              <svg v-else class="h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"/>
              </svg>
              {{ exportLoading ? 'Exporting...' : 'Export' }}
              <!-- chevron indicator -->
              <svg class="h-3 w-3 ml-0.5 transition-transform duration-200" :class="showExportMenu ? 'rotate-180' : ''" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
              </svg>
            </button>

            <!-- Dropdown panel – v-show so it mounts immediately (no hover-gap issue) -->
            <transition enter-active-class="transition ease-out duration-150" enter-from-class="opacity-0 -translate-y-1" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition ease-in duration-100" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-1">
              <div v-show="showExportMenu" class="absolute right-0 top-full mt-1 w-52 bg-white border border-slate-200 rounded-xl shadow-xl z-50 overflow-hidden">
                <button
                  @click="exportData('excel'); showExportMenu = false"
                  class="w-full text-left px-4 py-3 text-sm hover:bg-indigo-50 hover:text-indigo-700 text-slate-700 flex items-center gap-2.5 transition-colors duration-150"
                >
                  <span class="text-green-600 font-bold italic text-xs bg-green-50 px-1.5 py-0.5 rounded border border-green-100">XLS</span>
                  Excel Spreadsheet
                </button>
                <div class="border-t border-slate-100" v-if="activeTab === 'revenue'"></div>
                <button
                  v-if="activeTab === 'revenue'"
                  @click="exportData('pdf'); showExportMenu = false"
                  class="w-full text-left px-4 py-3 text-sm hover:bg-red-50 hover:text-red-700 text-slate-700 flex items-center gap-2.5 transition-colors duration-150"
                >
                  <span class="text-red-600 font-bold italic text-xs bg-red-50 px-1.5 py-0.5 rounded border border-red-100">PDF</span>
                  PDF Document
                </button>
              </div>
            </transition>
          </div>
        </div>
      </div>

      <!-- Result count badge -->
      <div v-if="!loading && reportData.length > 0" class="mt-4 flex items-center gap-2">
        <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 px-3 py-1 rounded-full">
          <svg class="h-3 w-3" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
          </svg>
          {{ reportData.length }} record{{ reportData.length !== 1 ? 's' : '' }} found
        </span>
      </div>
    </div>

    <!-- Loading State -->
    <div v-if="loading" class="flex flex-col justify-center items-center py-24 gap-4">
      <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      <p class="text-slate-400 text-sm font-medium">Generating report...</p>
    </div>

    <!-- Report Content -->
    <div v-else class="space-y-6">

      <!-- ── Bookings Table ── -->
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
            <!-- Empty state -->
            <tr v-if="reportData.length === 0">
              <td colspan="6" class="px-6 py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <svg class="h-12 w-12 text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                  </svg>
                  <p class="text-slate-400 font-medium">No bookings found for the selected filters.</p>
                  <p class="text-slate-300 text-sm">Try adjusting the date range or status filter.</p>
                </div>
              </td>
            </tr>
            <!-- Data rows -->
            <tr
              v-for="booking in reportData"
              :key="booking.id"
              class="hover:bg-slate-50 transition-colors"
            >
              <td class="px-6 py-4 font-bold text-slate-700 font-mono text-sm">{{ booking.booking_ref ?? '—' }}</td>
              <td class="px-6 py-4 text-slate-600">{{ booking.cabana?.name ?? '—' }}</td>
              <td class="px-6 py-4 text-slate-600">{{ booking.user?.name ?? '—' }}</td>
              <td class="px-6 py-4 text-slate-600">{{ booking.check_in ?? '—' }}</td>
              <td class="px-6 py-4 font-bold text-indigo-600">
                Rs. {{ Number(booking.total_amount || 0).toLocaleString('en-LK', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) }}
              </td>
              <td class="px-6 py-4">
                <span
                  class="px-2.5 py-1 text-[10px] font-black uppercase rounded-full tracking-tighter"
                  :class="statusBadge(booking.status)"
                >
                  {{ booking.status }}
                </span>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- ── Revenue Charts & Table ── -->
      <div v-if="activeTab === 'revenue'" class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
          <apexchart v-if="reportData.length > 0" height="350" type="line" :options="revenueChartOptions" :series="revenueSeries" />
          <div v-else class="flex items-center justify-center h-32 text-slate-400">No revenue data to chart.</div>
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
              <tr v-if="reportData.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-slate-400">No revenue data for the selected period.</td>
              </tr>
              <tr v-for="row in reportData" :key="row.month" class="hover:bg-slate-50 transition-colors">
                <td class="px-6 py-4 font-bold text-slate-700">{{ row.month }}</td>
                <td class="px-6 py-4 text-slate-600">Rs. {{ Number(row.gross_revenue || 0).toLocaleString() }}</td>
                <td class="px-6 py-4 font-bold text-emerald-600">Rs. {{ Number(row.platform_commission || 0).toLocaleString() }}</td>
                <td class="px-6 py-4 text-slate-600">Rs. {{ Number(row.owner_earnings || 0).toLocaleString() }}</td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- ── Occupancy Charts & Table ── -->
      <div v-if="activeTab === 'occupancy'" class="space-y-6">
        <div class="bg-white p-6 rounded-2xl shadow-sm border border-slate-200">
          <apexchart v-if="reportData.length > 0" height="350" type="bar" :options="occupancyChartOptions" :series="occupancySeries" />
          <div v-else class="flex items-center justify-center h-32 text-slate-400">No occupancy data to chart.</div>
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
              <tr v-if="reportData.length === 0">
                <td colspan="4" class="px-6 py-12 text-center text-slate-400">No occupancy data for the selected period.</td>
              </tr>
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
import { ref, onMounted, computed } from 'vue';
import axios from '../api/axios';
import { useToast } from 'vue-toastification';

const toast         = useToast();
const activeTab     = ref('bookings');
const loading       = ref(false);
const exportLoading = ref(false);
const showExportMenu = ref(false);  // click-toggle for export dropdown
const reportData    = ref([]);

// ─── Click-outside directive (closes export dropdown) ────────────────────────
const vClickOutside = {
  mounted(el, binding) {
    el.__clickOutsideHandler__ = (event) => {
      if (!el.contains(event.target)) binding.value(event);
    };
    document.addEventListener('mousedown', el.__clickOutsideHandler__);
  },
  unmounted(el) {
    document.removeEventListener('mousedown', el.__clickOutsideHandler__);
  },
};

// ─── Parse blob error (axios returns Blob bodies when responseType=blob) ─────
const parseBlobError = async (error) => {
  try {
    if (error.response?.data instanceof Blob) {
      const text = await error.response.data.text();
      const json = JSON.parse(text);
      return json.message || 'Export failed. Please try again.';
    }
  } catch (_) { /* ignore parse errors */ }
  return error.response?.data?.message || error.message || 'Export failed. Please try again.';
};

const tabs = [
  { id: 'bookings',  name: 'Booking Report' },
  { id: 'revenue',   name: 'Revenue Report' },
  { id: 'occupancy', name: 'Occupancy' },
];

// Default to last 30 days
const today   = new Date();
const ago30   = new Date(today);
ago30.setDate(ago30.getDate() - 30);

const filters = ref({
  start_date: ago30.toISOString().split('T')[0],
  end_date:   today.toISOString().split('T')[0],
  status:     '',
  cabana_id:  '',
});

// ─── Status badge ────────────────────────────────────────────────────────────
const statusBadge = (status) => {
  switch (status) {
    case 'completed': return 'bg-emerald-100 text-emerald-700';
    case 'confirmed': return 'bg-blue-100 text-blue-700';
    case 'cancelled': return 'bg-red-100 text-red-700';
    case 'pending':   return 'bg-amber-100 text-amber-700';
    default:          return 'bg-slate-100 text-slate-700';
  }
};

// ─── Switch tab ──────────────────────────────────────────────────────────────
const switchTab = (tab) => {
  activeTab.value = tab;
  reportData.value = [];
  fetchReportData();
};

// ─── Build clean params (strip empty strings / nulls) ─────────────────────
const buildParams = () => {
  const raw = { ...filters.value };
  return Object.fromEntries(
    Object.entries(raw).filter(([, v]) => v !== '' && v !== null && v !== undefined)
  );
};

// ─── Generate Report ─────────────────────────────────────────────────────────
const fetchReportData = async () => {
  loading.value = true;
  reportData.value = [];

  try {
    const params   = buildParams();
    const response = await axios.get(`/admin/reports/${activeTab.value}`, { params });

    // Backend now returns { success, count, data: [...] } — no pagination wrapper
    const payload = response.data;

    if (payload.success) {
      reportData.value = Array.isArray(payload.data) ? payload.data : [];
    } else {
      toast.error(payload.message || 'Report generation failed.');
    }
  } catch (error) {
    const msg = error.response?.data?.message || 'Failed to load report data. Please try again.';
    toast.error(msg);
    console.error('[Reports] fetchReportData error:', error);
  } finally {
    loading.value = false;
  }
};

// ─── Export (blob download with auth) ────────────────────────────────────────
const exportData = async (format = 'excel') => {
  exportLoading.value = true;

  // Map tab → export endpoint
  const endpointMap = {
    bookings:  'bookings',
    revenue:   'revenue',
    occupancy: 'occupancy',
  };

  const endpoint = endpointMap[activeTab.value] ?? activeTab.value;

  try {
    const params = { ...buildParams(), format };

    const response = await axios.get(`/admin/reports/export/${endpoint}`, {
      params,
      responseType: 'blob',   // Download raw binary from the authenticated API
    });

    // Determine filename from Content-Disposition header, or fall back to default
    const disposition = response.headers['content-disposition'] ?? '';
    const filenameMatch = disposition.match(/filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/);
    const extension = format === 'pdf' ? 'pdf' : 'xlsx';
    const filename = filenameMatch
      ? filenameMatch[1].replace(/['"]/g, '')
      : `${endpoint}_report_${new Date().toISOString().slice(0, 10)}.${extension}`;

    // Trigger browser download
    const url  = window.URL.createObjectURL(new Blob([response.data]));
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', filename);
    document.body.appendChild(link);
    link.click();
    link.remove();
    window.URL.revokeObjectURL(url);

    toast.success(`Report exported as ${filename.split('.').pop().toUpperCase()} successfully!`);
  } catch (error) {
    // When responseType is 'blob', error.response.data is a Blob — must parse it
    const msg = await parseBlobError(error);
    toast.error(msg);
    console.error('[Reports] exportData error:', error);
  } finally {
    exportLoading.value = false;
    showExportMenu.value = false;
  }
};

// ─── Revenue chart ────────────────────────────────────────────────────────────
const revenueSeries = computed(() => {
  if (activeTab.value !== 'revenue' || reportData.value.length === 0) return [];
  return [
    { name: 'Gross Revenue',       data: reportData.value.map(r => Number(r.gross_revenue      || 0)) },
    { name: 'Platform Commission', data: reportData.value.map(r => Number(r.platform_commission || 0)) },
  ];
});

const revenueChartOptions = computed(() => ({
  chart:   { toolbar: { show: false }, zoom: { enabled: false } },
  stroke:  { curve: 'smooth', width: 3 },
  colors:  ['#4f46e5', '#10b981'],
  xaxis:   { categories: reportData.value.map(r => r.month) },
  yaxis:   { labels: { formatter: (val) => `Rs. ${Number(val).toLocaleString()}` } },
  tooltip: { y: { formatter: (val) => `Rs. ${Number(val).toLocaleString()}` } },
}));

// ─── Occupancy chart ─────────────────────────────────────────────────────────
const occupancySeries = computed(() => {
  if (activeTab.value !== 'occupancy' || reportData.value.length === 0) return [];
  return [{ name: 'Occupancy Rate', data: reportData.value.map(r => r.occupancy_percentage) }];
});

const occupancyChartOptions = computed(() => ({
  chart:        { toolbar: { show: false } },
  plotOptions:  { bar: { borderRadius: 10, columnWidth: '50%' } },
  colors:       ['#6366f1'],
  xaxis:        { categories: reportData.value.map(r => r.cabana_name) },
  yaxis:        { max: 100, labels: { formatter: (val) => `${val}%` } },
}));

// ─── Load on mount ────────────────────────────────────────────────────────────
onMounted(fetchReportData);
</script>
