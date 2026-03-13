<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="container mx-auto px-4 max-w-5xl">

      <!-- Header -->
      <div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div>
          <h1 class="text-3xl font-extrabold text-slate-900">My Payments</h1>
          <p class="text-slate-500 mt-1">Your complete payment history and transaction details.</p>
        </div>
        <button
          @click="refreshPayments"
          class="inline-flex items-center bg-white border border-slate-200 text-slate-700 hover:bg-slate-50 px-4 py-2.5 rounded-xl text-sm font-semibold shadow-sm transition-all hover:shadow-md"
        >
          <svg class="h-4 w-4 mr-2 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
          </svg>
          Refresh
        </button>
      </div>

      <!-- Loading -->
      <div v-if="paymentStore.loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error -->
      <div v-else-if="paymentStore.error" class="bg-red-50 text-red-600 p-6 rounded-2xl text-center border border-red-100 shadow-sm">
        {{ paymentStore.error }}
      </div>

      <!-- Empty -->
      <div v-else-if="!paymentStore.userPayments || paymentStore.userPayments.length === 0"
        class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-indigo-50 mb-6">
          <svg class="h-10 w-10 text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
          </svg>
        </div>
        <p class="text-xl font-bold text-slate-800">No payment history</p>
        <p class="mt-2 text-slate-500">You haven't made any payments yet.</p>
        <router-link to="/dashboard"
          class="mt-6 inline-block bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 text-white font-semibold py-3 px-8 rounded-xl shadow-md transition-all">
          View Bookings
        </router-link>
      </div>

      <!-- Payments List -->
      <div v-else class="space-y-4">
        <div
          v-for="payment in paymentStore.userPayments"
          :key="payment.order_id"
          class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-200"
        >
          <div class="flex flex-col sm:flex-row items-start sm:items-center p-6 gap-4">

            <!-- Icon -->
            <div class="w-12 h-12 rounded-xl flex items-center justify-center shrink-0"
              :class="payment.payment_status === 'paid' ? 'bg-green-50' : payment.payment_status === 'failed' ? 'bg-red-50' : 'bg-yellow-50'"
            >
              <svg v-if="payment.payment_status === 'paid'" class="w-6 h-6 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
              </svg>
              <svg v-else-if="payment.payment_status === 'failed'" class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
              <svg v-else class="w-6 h-6 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
              </svg>
            </div>

            <!-- Main info -->
            <div class="flex-1 min-w-0">
              <div class="flex flex-wrap items-start justify-between gap-2">
                <div>
                  <h3 class="font-bold text-slate-900">{{ payment.cabana_name || 'Cabana Booking' }}</h3>
                  <p class="text-xs text-slate-400 font-mono mt-0.5">Order: {{ payment.order_id }}</p>
                </div>
                <span
                  class="px-3 py-1 text-xs font-bold rounded-full border capitalize shrink-0"
                  :class="paymentStatusClass(payment.payment_status)"
                >
                  {{ payment.payment_status }}
                </span>
              </div>
              <p class="text-xs text-slate-500 mt-2">
                {{ formatDate(payment.created_at) }}
              </p>
            </div>

            <!-- Amount -->
            <div class="text-right shrink-0">
              <p class="text-xs text-slate-400 font-medium mb-0.5">Amount</p>
              <p class="text-xl font-extrabold text-indigo-700">{{ formatLKR(payment.amount) }}</p>
              <p class="text-xs text-slate-400">{{ payment.currency || 'LKR' }}</p>
            </div>

          </div>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { usePaymentStore } from '../store/paymentStore';
import { formatLKR } from '../utils/currency';

const paymentStore = usePaymentStore();

onMounted(() => {
  paymentStore.fetchUserPayments();
});

const refreshPayments = () => {
  paymentStore.fetchUserPayments();
};

const formatDate = (dateStr) => {
  if (!dateStr) return '—';
  return new Date(dateStr).toLocaleDateString('en-LK', {
    year: 'numeric', month: 'short', day: 'numeric',
    hour: '2-digit', minute: '2-digit'
  });
};

const paymentStatusClass = (s) => ({
  paid:       'bg-green-50 text-green-700 border-green-200',
  pending:    'bg-yellow-50 text-yellow-700 border-yellow-200',
  failed:     'bg-red-50 text-red-700 border-red-200',
  cancelled:  'bg-red-50 text-red-700 border-red-200',
  refunded:   'bg-slate-100 text-slate-600 border-slate-200',
}[s] || 'bg-slate-100 text-slate-600 border-slate-200');
</script>
