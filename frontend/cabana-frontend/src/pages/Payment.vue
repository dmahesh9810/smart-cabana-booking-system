<template>
  <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-xl shadow-lg p-8 text-center space-y-6 border border-slate-100">
      
      <!-- Loading UI -->
      <div v-if="loading" class="flex flex-col flex-center items-center py-6">
        <svg class="animate-spin h-12 w-12 text-indigo-600 mb-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <h2 class="text-xl font-bold text-slate-800">Preparing Payment</h2>
        <p class="text-sm text-slate-500 mt-2">Connecting to secure gateway...</p>
      </div>

      <!-- Error UI -->
      <div v-else-if="error" class="py-6">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-red-100 mb-4">
          <svg class="h-8 w-8 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Error Occurred</h2>
        <p class="text-slate-600 text-sm mb-6">{{ error }}</p>
        <button @click="initiateFlow" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg transition-colors">
          Retry Payment
        </button>
      </div>

      <!-- Ready UI -->
      <div v-else class="py-6">
        <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-indigo-100 mb-4">
          <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285z" />
          </svg>
        </div>
        <h2 class="text-xl font-bold text-slate-900 mb-2">Secure Checkout</h2>
        <p class="text-slate-600 text-sm mb-6">Click below to open the PayHere secure payment portal.</p>
        
        <button @click="startPayment" class="w-full bg-[#1A73E8] hover:bg-[#1557B0] text-white font-semibold flex items-center justify-center py-3 px-4 rounded-lg transition-colors shadow-sm">
          Pay with PayHere
        </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api from '../api/axios';

const route = useRoute();
const router = useRouter();

const bookingId = route.query.booking_id;
const loading = ref(true);
const error = ref(null);
const paymentPayload = ref(null);

onMounted(() => {
    if (!bookingId) {
        router.replace('/');
        return;
    }
    initiateFlow();
});

const initiateFlow = async () => {
    loading.value = true;
    error.value = null;

    try {
        // Step 1: Load PayHere SDK exactly checking for global object
        await loadPayHereSdk();

        // Step 2: Fetch Payment Details from new unified API
        const response = await api.post('/payments/initiate', {
            booking_id: bookingId
        });

        if (response.data && response.data.success) {
            paymentPayload.value = response.data.data;
            setupPayHereCallbacks();
            startPayment(); // Auto-start the popup
        } else {
            error.value = "Failed to retrieve payment request token.";
        }
    } catch (e) {
        error.value = e.response?.data?.message || "An error occurred attempting to reach the payment server.";
        console.error("Payment Initiation Error:", e);
    } finally {
        loading.value = false;
    }
};

const loadPayHereSdk = () => {
    return new Promise((resolve, reject) => {
        if (window.payhere) {
            resolve();
            return;
        }
        const script = document.createElement('script');
        script.src = 'https://www.payhere.lk/lib/payhere.js';
        script.onload = () => resolve();
        script.onerror = () => reject(new Error("Failed to load PayHere SDK. Please check connection."));
        document.head.appendChild(script);
    });
};

const setupPayHereCallbacks = () => {
    // Exact Javascript SDK Callbacks Implementation
    window.payhere.onCompleted = function onCompleted(orderId) {
        console.log("PayHere Checkout Completed. Order:", orderId);
        // Requirement: Redirect onCompleted to /booking/success
        router.push({ name: 'PaymentSuccess', query: { booking_id: bookingId, order_id: orderId } });
    };

    window.payhere.onDismissed = function onDismissed() {
        console.log("PayHere Checkout Dismissed.");
        // Requirement: Redirect onDismissed to /booking/cancel
        router.push({ name: 'PaymentCancel', query: { booking_id: bookingId } });
    };

    window.payhere.onError = function onError(errorMsg) {
        console.error("PayHere Checked Encounted Error:", errorMsg);
        error.value = "Payment Processing Error: " + errorMsg;
    };
};

const startPayment = () => {
    if (!window.payhere || !paymentPayload.value) {
        error.value = "Payment gateway not initialized completely.";
        return;
    }

    // CRITICAL FIX: Convert Vue Proxy into a plain deep-cloned JSON object
    var paymentObject = JSON.parse(JSON.stringify(paymentPayload.value));
    
    // Output object exactly as PayHere expects
    console.log("Triggering PayHere popup with exactly plain object:", paymentObject);
    window.payhere.startPayment(paymentObject);
};
</script>
