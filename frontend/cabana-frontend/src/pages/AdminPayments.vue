<template>
  <div class="space-y-6">
    <div class="flex justify-between items-center">
      <h2 class="text-2xl font-bold text-slate-800">Payments</h2>
      <button @click="refreshPayments" class="bg-indigo-50 text-indigo-600 px-4 py-2 rounded-lg text-sm font-medium hover:bg-indigo-100 transition inline-flex items-center">
        <svg class="h-4 w-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
        </svg>
        Refresh
      </button>
    </div>

    <!-- Error State -->
    <div v-if="adminStore.error" class="bg-red-50 text-red-600 p-4 rounded-lg shadow-sm border border-red-100 text-sm">
      {{ adminStore.error }}
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
      <div v-if="adminStore.loading && !adminStore.payments.length" class="flex justify-center items-center py-20">
        <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-indigo-600"></div>
      </div>

      <div v-else-if="!adminStore.payments.length" class="text-center py-16 text-slate-500">
        <svg class="mx-auto h-12 w-12 text-slate-300 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
        </svg>
        No payments found.
      </div>

      <div v-else class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50">
            <tr>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">ID</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Order Ref</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Amount</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Method</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">PayHere Ref</th>
              <th scope="col" class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Date</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-100">
            <tr v-for="payment in adminStore.payments" :key="payment.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-medium">#{{ payment.id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ payment.order_id }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900 font-semibold">{{ payment.currency }} {{ payment.amount }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ payment.payment_method }}</td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span :class="{
                  'bg-green-100 text-green-800': payment.payment_status === 'paid',
                  'bg-yellow-100 text-yellow-800': payment.payment_status === 'pending',
                  'bg-red-100 text-red-800': payment.payment_status === 'failed',
                  'bg-slate-100 text-slate-800': payment.payment_status === 'refunded'
                }" class="px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full capitalize">
                  {{ payment.payment_status }}
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500 font-mono text-xs">{{ payment.payhere_payment_id || 'N/A' }}</td>
              <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">{{ new Date(payment.created_at).toLocaleDateString() }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script setup>
import { onMounted } from 'vue';
import { useAdminStore } from '../store/adminStore';

const adminStore = useAdminStore();

onMounted(() => {
    adminStore.fetchPayments();
});

const refreshPayments = () => {
    adminStore.fetchPayments();
};
</script>
