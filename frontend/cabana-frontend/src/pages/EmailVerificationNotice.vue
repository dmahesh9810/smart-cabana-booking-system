<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <!-- Logo or Icon -->
      <div class="flex justify-center mb-6">
        <div class="h-16 w-16 bg-ocean-100 text-ocean-600 rounded-full flex items-center justify-center">
          <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
          </svg>
        </div>
      </div>

      <h2 class="text-center text-3xl font-extrabold text-slate-900 mb-2">
        Verify your email
      </h2>
      <p class="text-center text-sm text-slate-500 mb-8 px-4">
        We've sent a verification link to your email address. Please check your inbox and click the link to verify your account before logging in.
      </p>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow rounded-2xl sm:px-10 border border-slate-100">
        <div v-if="successMessage" class="mb-4 bg-teal-50 text-teal-700 p-4 rounded-xl text-sm border border-teal-100 text-center">
          {{ successMessage }}
        </div>
        <div v-if="authStore.error" class="mb-4 bg-red-50 text-red-600 p-4 rounded-xl text-sm border border-red-100 text-center">
          {{ authStore.error }}
        </div>

        <button
          @click="resendEmail"
          :disabled="authStore.loading || cooldown > 0"
          class="w-full flex justify-center items-center py-3 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-ocean-600 hover:bg-ocean-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 disabled:opacity-50 disabled:cursor-not-allowed transition-colors"
        >
          <svg v-if="authStore.loading" class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
          </svg>
          {{ cooldown > 0 ? `Resend available in ${cooldown}s` : 'Resend Verification Email' }}
        </button>

        <div class="mt-6 text-center">
          <router-link to="/login" class="text-sm font-medium text-ocean-600 hover:text-ocean-500 transition-colors">
            Return to login
          </router-link>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import { useAuthStore } from '../store/authStore';

const authStore = useAuthStore();
const successMessage = ref('');
const cooldown = ref(0);
let timer = null;

const startCooldown = () => {
  cooldown.value = 60;
  timer = setInterval(() => {
    cooldown.value--;
    if (cooldown.value <= 0) {
      clearInterval(timer);
    }
  }, 1000);
};

const resendEmail = async () => {
  successMessage.value = '';
  try {
    const response = await authStore.resendVerification();
    successMessage.value = 'Verification link sent! Please check your email.';
    startCooldown();
  } catch (err) {
    // Error is handled in the store, authStore.error is displayed
  }
};

onUnmounted(() => {
  if (timer) clearInterval(timer);
});
</script>
