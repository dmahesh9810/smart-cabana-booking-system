<template>
  <div class="min-h-screen bg-slate-50 py-12">
    <div class="container mx-auto px-4 max-w-6xl">
      <div class="mb-8 flex justify-between items-center">
        <div>
          <h1 class="text-3xl font-extrabold text-slate-900">My Payments</h1>
          <p class="text-slate-600 mt-2">View your payment history and transaction details.</p>
        </div>
        <button @click="refreshPayments" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-100 transition inline-flex items-center">
          <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
          </svg>
          Refresh
        </button>
      </div>

      <!-- Loading State -->
      <div v-if="paymentStore.loading" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-indigo-600"></div>
      </div>

      <!-- Error State -->
      <div v-else-if="paymentStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg text-center shadow-sm">
        {{ paymentStore.error }}
      </div>

      <!-- Empty State -->
      <div v-else-if="!paymentStore.userPayments || paymentStore.userPayments.length === 0" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
        <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-slate-100 mb-4">
          <svg class="h-8 w-8 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
             <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
          </svg>
        </div>
        <p class="text-xl font-medium text-slate-900">No payment history</p>
        <p class="mt-2 text-slate-500">You haven't made any payments yet.</p>
        <router-link to="/dashboard" class="mt-6 inline-block bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-2.5 px-6 rounded-lg shadow-md transition-colors">
          View Bookings
        </router-link>
      </div>

      <!-- Payments List -->
      <div v-else class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-100">
        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-slate-50 text-slate-600 text-sm uppercase tracking-wider border-b border-slate-200">
                <th class="p-4 font-semibold">Order ID</th>
                <th class="p-4 font-semibold">Cabana</th>
                <th class="p-4 font-semibold">Amount</th>
                <th class="p-4 font-semibold">Status</th>
                <th class="p-4 font-semibold">Date</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
              <tr 
                v-for="payment in paymentStore.userPayments" 
                :key="payment.order_id" 
                class="hover:bg-slate-50 transition-colors"
              >
                <td class="p-4 font-mono text-sm font-medium text-slate-700">
                  {{ payment.order_id }}
                </td>
                <td class="p-4 font-medium text-slate-900">
                  {{ payment.cabana_name }}
                </td>
                <td class="p-4 text-slate-900 font-semibold">
                  {{ payment.currency }} {{ payment.amount }}
                </td>
                <td class="p-4">
                  <span 
                    class="px-3 py-1 text-xs font-semibold rounded-full capitalize"
                    :class="{
                      'bg-green-100 text-green-700': payment.payment_status === 'paid',
                      'bg-orange-100 text-orange-700': payment.payment_status === 'pending',
                      'bg-red-100 text-red-700': payment.payment_status === 'failed' || payment.payment_status === 'cancelled',
                      'bg-slate-100 text-slate-700': payment.payment_status === 'refunded'
                    }"
                  >
                    {{ payment.payment_status }}
                  </span>
                </td>
                <td class="p-4 text-slate-600 text-sm">
                  {{ new Date(payment.created_at).toLocaleDateString(undefined, { year: 'numeric', month: 'short', day: 'numeric', hour: '2-digit', minute: '2-digit' }) }}
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
import { onMounted } from 'vue';
import { usePaymentStore } from '../store/paymentStore';

const paymentStore = usePaymentStore();

onMounted(() => {
    paymentStore.fetchUserPayments();
});

const refreshPayments = () => {
    paymentStore.fetchUserPayments();
};
</script>
