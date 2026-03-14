<template>
  <div class="space-y-6">

    <!-- Success Toast -->
    <Transition
      enter-active-class="transition-all duration-300 ease-out"
      enter-from-class="opacity-0 translate-x-4"
      enter-to-class="opacity-100 translate-x-0"
      leave-active-class="transition-all duration-200 ease-in"
      leave-from-class="opacity-100 translate-x-0"
      leave-to-class="opacity-0 translate-x-4"
    >
      <div
        v-if="adminStore.successMessage"
        class="fixed top-5 right-5 z-[60] flex items-center gap-3 bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl shadow-emerald-900/20"
      >
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="text-sm font-semibold">{{ adminStore.successMessage }}</span>
      </div>
    </Transition>

    <!-- Error Banner -->
    <Transition
      enter-active-class="transition-all duration-200"
      enter-from-class="opacity-0 -translate-y-2"
      enter-to-class="opacity-100 translate-y-0"
    >
      <div
        v-if="adminStore.error"
        class="flex items-center gap-3 bg-red-50 text-red-700 border border-red-200 px-4 py-3 rounded-xl text-sm"
      >
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="font-medium">{{ adminStore.error }}</span>
        <button @click="adminStore.error = null" class="ml-auto">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </Transition>

    <!-- Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Bookings Management</h1>
        <p class="text-slate-500 text-sm mt-1">
          {{ bookingsMeta ? `${bookingsMeta.total} total bookings` : `${bookings.length} bookings loaded` }}
        </p>
      </div>

      <!-- Status Filter -->
      <div class="flex items-center gap-2">
        <label class="text-xs font-semibold text-slate-500 uppercase tracking-wide">Filter:</label>
        <select
          v-model="statusFilter"
          @change="applyFilter"
          class="text-sm border border-slate-200 rounded-xl px-3 py-2 bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm"
        >
          <option value="">All Statuses</option>
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
    </div>

    <!-- Loading -->
    <div v-if="adminStore.loading && !bookings.length" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <div class="animate-spin rounded-full h-10 w-10 border-[3px] border-indigo-200 border-t-indigo-600"></div>
        <p class="text-sm text-slate-400">Loading bookings...</p>
      </div>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Booking Ref</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cabana</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Guest</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Dates</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Booking</th>
              <th class="px-5 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Payment</th>
              <th class="px-5 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-50">
            <tr
              v-for="booking in bookings"
              :key="booking.id"
              class="hover:bg-slate-50/70 transition-colors"
            >
              <!-- Ref -->
              <td class="px-5 py-4 whitespace-nowrap">
                <p class="text-sm font-bold text-indigo-600 font-mono">{{ booking.booking_ref || `#${String(booking.id).padStart(5, '0')}` }}</p>
                <p class="text-xs text-slate-400 mt-0.5">{{ booking.created_at }}</p>
              </td>

              <!-- Cabana -->
              <td class="px-5 py-4 whitespace-nowrap">
                <p class="text-sm font-semibold text-slate-800">{{ booking.cabana?.name || '—' }}</p>
                <p class="text-xs text-slate-400">📍 {{ booking.cabana?.location || '' }}</p>
              </td>

              <!-- Guest -->
              <td class="px-5 py-4 whitespace-nowrap">
                <p class="text-sm font-semibold text-slate-800">{{ booking.user?.name || 'Guest' }}</p>
                <p class="text-xs text-slate-400">{{ booking.user?.email || '' }}</p>
              </td>

              <!-- Dates -->
              <td class="px-5 py-4 whitespace-nowrap text-sm text-slate-600">
                <div class="flex items-center gap-1.5">
                  <span class="text-slate-400 text-xs">In</span>
                  <span class="font-medium text-slate-800">{{ formatDate(booking.check_in) }}</span>
                </div>
                <div class="flex items-center gap-1.5 mt-0.5">
                  <span class="text-slate-400 text-xs">Out</span>
                  <span class="font-medium text-slate-800">{{ formatDate(booking.check_out) }}</span>
                </div>
              </td>

              <!-- Amount -->
              <td class="px-5 py-4 whitespace-nowrap">
                <p class="text-sm font-bold text-slate-800">{{ formatLKR(booking.total_amount) }}</p>
                <p class="text-xs text-slate-400">{{ booking.guests_count }} guest{{ booking.guests_count !== 1 ? 's' : '' }}</p>
              </td>

              <!-- Booking Status -->
              <td class="px-5 py-4 whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" :class="bookingStatusClass(booking.status)">
                  <span class="w-1.5 h-1.5 rounded-full" :class="bookingDotClass(booking.status)"></span>
                  {{ capitalize(booking.status || 'pending') }}
                </span>
              </td>

              <!-- Payment Status -->
              <td class="px-5 py-4 whitespace-nowrap">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" :class="paymentStatusClass(booking.payment_status)">
                  <span class="w-1.5 h-1.5 rounded-full" :class="paymentDotClass(booking.payment_status)"></span>
                  {{ capitalize(booking.payment_status || 'pending') }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-5 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <!-- View -->
                  <button
                    @click="openDetailModal(booking)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-slate-50 text-slate-700 border border-slate-200 hover:bg-slate-100 transition-colors"
                    title="View Details"
                  >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View
                  </button>

                  <!-- Approve (only if pending) -->
                  <button
                    v-if="booking.status === 'pending'"
                    @click="confirmStatusChange(booking, 'confirmed')"
                    :disabled="updatingId === booking.id"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors disabled:opacity-50"
                  >
                    <svg v-if="updatingId === booking.id" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 8 8 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                    Approve
                  </button>

                  <!-- Cancel (only if not already cancelled) -->
                  <button
                    v-if="booking.status !== 'cancelled'"
                    @click="openCancelModal(booking)"
                    class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors"
                  >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Cancel
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty -->
            <tr v-if="!bookings.length && !adminStore.loading">
              <td colspan="8" class="py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                  </div>
                  <p class="text-slate-500 font-medium">No bookings found</p>
                  <p class="text-slate-400 text-xs">Try changing the status filter.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <div v-if="bookingsMeta && bookingsMeta.last_page > 1" class="flex items-center justify-between px-5 py-3 border-t border-slate-100 bg-slate-50/50">
        <p class="text-xs text-slate-500">
          Page {{ bookingsMeta.current_page }} of {{ bookingsMeta.last_page }} · {{ bookingsMeta.total }} total
        </p>
        <div class="flex gap-2">
          <button
            @click="changePage(bookingsMeta.current_page - 1)"
            :disabled="bookingsMeta.current_page === 1"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-white disabled:opacity-40 transition-colors"
          >← Prev</button>
          <button
            @click="changePage(bookingsMeta.current_page + 1)"
            :disabled="bookingsMeta.current_page === bookingsMeta.last_page"
            class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-200 hover:bg-white disabled:opacity-40 transition-colors"
          >Next →</button>
        </div>
      </div>
    </div>

    <!-- ── Detail Modal ──────────────────────────────────────────────── -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
        <div v-if="showDetailModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDetailModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-lg overflow-hidden">
            <!-- Header -->
            <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-slate-50">
              <div>
                <h3 class="text-base font-bold text-slate-900">Booking Details</h3>
                <p class="text-xs text-indigo-600 font-mono font-bold mt-0.5">{{ detailBooking?.booking_ref }}</p>
              </div>
              <button @click="showDetailModal = false" class="text-slate-400 hover:text-slate-600 transition-colors rounded-lg p-1.5 hover:bg-slate-100">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
              </button>
            </div>
            <!-- Body -->
            <div class="p-6 space-y-4">
              <div class="grid grid-cols-2 gap-4">
                <DetailRow label="Guest" :value="detailBooking?.user?.name" />
                <DetailRow label="Email" :value="detailBooking?.user?.email" />
                <DetailRow label="Cabana" :value="detailBooking?.cabana?.name" />
                <DetailRow label="Location" :value="detailBooking?.cabana?.location" />
                <DetailRow label="Check-in" :value="formatDate(detailBooking?.check_in)" />
                <DetailRow label="Check-out" :value="formatDate(detailBooking?.check_out)" />
                <DetailRow label="Guests" :value="detailBooking?.guests_count" />
                <DetailRow label="Total Amount" :value="formatLKR(detailBooking?.total_amount)" />
              </div>
              <div class="flex items-center gap-3 pt-2 border-t border-slate-100">
                <div>
                  <p class="text-xs text-slate-400 mb-1">Booking Status</p>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" :class="bookingStatusClass(detailBooking?.status)">
                    {{ capitalize(detailBooking?.status || 'pending') }}
                  </span>
                </div>
                <div>
                  <p class="text-xs text-slate-400 mb-1">Payment Status</p>
                  <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold" :class="paymentStatusClass(detailBooking?.payment_status)">
                    {{ capitalize(detailBooking?.payment_status || 'pending') }}
                  </span>
                </div>
              </div>
            </div>
            <!-- Footer Actions -->
            <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex justify-between gap-3">
              <button
                v-if="detailBooking?.status === 'pending'"
                @click="confirmStatusChangeFromDetail('confirmed')"
                :disabled="updatingId === detailBooking?.id"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 disabled:opacity-50 transition-colors"
              >Approve Booking</button>
              <button
                v-if="detailBooking?.status !== 'cancelled'"
                @click="confirmStatusChangeFromDetail('cancelled')"
                class="inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 transition-colors"
              >Cancel Booking</button>
              <button @click="showDetailModal = false" class="ml-auto px-4 py-2 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                Close
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── Cancel Confirmation Modal ───────────────────────────────── -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
        <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-red-50 border-2 border-red-100 flex items-center justify-center">
              <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-bold text-slate-900">Cancel Booking?</h3>
              <p class="text-sm text-slate-500 mt-1">
                Booking <strong class="text-slate-700 font-mono">{{ bookingToCancel?.booking_ref }}</strong> for <strong class="text-slate-700">{{ bookingToCancel?.user?.name }}</strong> will be cancelled. This cannot be undone.
              </p>
            </div>
            <div class="flex gap-3 w-full pt-2">
              <button @click="showCancelModal = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors">
                Keep Booking
              </button>
              <button
                @click="executeCancel"
                :disabled="updatingId === bookingToCancel?.id"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 disabled:opacity-60 transition-colors"
              >
                <svg v-if="updatingId === bookingToCancel?.id" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 8 8 0 12h4z"/>
                </svg>
                Yes, Cancel It
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { onMounted, computed, ref, defineComponent, h } from 'vue';
import { useAdminStore } from '../store/adminStore';

// ── Inline helper component for detail modal rows ─────────────
const DetailRow = defineComponent({
    props: { label: String, value: [String, Number] },
    setup(props) {
        return () => h('div', [
            h('p', { class: 'text-xs text-slate-400 mb-0.5' }, props.label),
            h('p', { class: 'text-sm font-semibold text-slate-800' }, props.value ?? '—'),
        ]);
    },
});

const adminStore = useAdminStore();
const bookings     = computed(() => adminStore.bookings);
const bookingsMeta = computed(() => adminStore.bookingsMeta);

const statusFilter = ref('');
const currentPage  = ref(1);
const updatingId   = ref(null);

// Detail Modal
const showDetailModal = ref(false);
const detailBooking   = ref(null);

// Cancel Modal
const showCancelModal  = ref(false);
const bookingToCancel  = ref(null);

onMounted(() => {
    adminStore.fetchBookings();
});

// ── Fetch helpers ──────────────────────────────────────────────
const applyFilter = () => {
    currentPage.value = 1;
    adminStore.fetchBookings({ status: statusFilter.value || undefined, page: 1 });
};

const changePage = (page) => {
    currentPage.value = page;
    adminStore.fetchBookings({ status: statusFilter.value || undefined, page });
};

// ── Formatting ─────────────────────────────────────────────────
const formatLKR = (value) => {
    if (value === null || value === undefined) return '—';
    return new Intl.NumberFormat('si-LK', { style: 'currency', currency: 'LKR', minimumFractionDigits: 0 }).format(value);
};

const formatDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
};

const capitalize = (str) => str ? str.charAt(0).toUpperCase() + str.slice(1) : '—';

// ── Status badge helpers ───────────────────────────────────────
const bookingStatusClass = (status) => ({
    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': status === 'confirmed',
    'bg-amber-50   text-amber-700   ring-1 ring-amber-200':   status === 'pending',
    'bg-red-50     text-red-700     ring-1 ring-red-200':     status === 'cancelled',
    'bg-blue-50    text-blue-700    ring-1 ring-blue-200':    status === 'completed',
    'bg-slate-50   text-slate-600   ring-1 ring-slate-200':   !status,
});
const bookingDotClass = (status) => ({
    'bg-emerald-500': status === 'confirmed',
    'bg-amber-400':   status === 'pending',
    'bg-red-500':     status === 'cancelled',
    'bg-blue-500':    status === 'completed',
    'bg-slate-400':   !status,
});
const paymentStatusClass = (status) => ({
    'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200': status === 'paid',
    'bg-amber-50   text-amber-700   ring-1 ring-amber-200':   status === 'pending',
    'bg-red-50     text-red-700     ring-1 ring-red-200':     status === 'failed' || status === 'refunded',
});
const paymentDotClass = (status) => ({
    'bg-emerald-500': status === 'paid',
    'bg-amber-400':   status === 'pending',
    'bg-red-500':     status === 'failed' || status === 'refunded',
});

// ── Detail Modal ───────────────────────────────────────────────
const openDetailModal = (booking) => {
    detailBooking.value = booking;
    showDetailModal.value = true;
};

const confirmStatusChangeFromDetail = async (newStatus) => {
    showDetailModal.value = false;
    if (newStatus === 'cancelled') {
        openCancelModal(detailBooking.value);
    } else {
        await doStatusUpdate(detailBooking.value.id, newStatus);
    }
};

// ── Status update ──────────────────────────────────────────────
const confirmStatusChange = async (booking, newStatus) => {
    await doStatusUpdate(booking.id, newStatus);
};

const doStatusUpdate = async (id, status) => {
    updatingId.value = id;
    try {
        await adminStore.updateBookingStatus(id, status);
        // Keep detail modal in sync if it's open
        if (detailBooking.value?.id === id) {
            detailBooking.value = adminStore.bookings.find(b => b.id === id) ?? detailBooking.value;
        }
    } finally {
        updatingId.value = null;
    }
};

// ── Cancel Modal ───────────────────────────────────────────────
const openCancelModal = (booking) => {
    bookingToCancel.value = booking;
    showCancelModal.value = true;
};

const executeCancel = async () => {
    updatingId.value = bookingToCancel.value.id;
    try {
        await adminStore.updateBookingStatus(bookingToCancel.value.id, 'cancelled');
    } finally {
        updatingId.value = null;
        showCancelModal.value = false;
        bookingToCancel.value = null;
    }
};
</script>
