<template>
  <div class="min-h-screen bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      
      <div class="flex justify-center mb-6">
        <div class="h-20 w-20 bg-teal-100 text-teal-600 rounded-full flex items-center justify-center shadow-inner overflow-hidden relative">
          <svg class="w-10 h-10 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" />
          </svg>
          <div class="absolute inset-0 bg-teal-400 opacity-20 animate-ping rounded-full"></div>
        </div>
      </div>

      <h2 class="text-center text-3xl font-extrabold text-slate-900 mb-2">
        Email Successfully Verified
      </h2>
      <p class="text-center text-sm text-slate-600 mb-8 px-4">
        Thank you for verifying your email address. You can now access your account.
      </p>
    </div>

    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-8 px-4 shadow-lg shadow-teal-900/5 rounded-2xl sm:px-10 border border-slate-100 text-center">
        
        <router-link
          to="/login"
          class="w-full flex justify-center items-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-semibold text-white bg-gradient-to-r from-ocean-600 to-teal-500 hover:from-ocean-700 hover:to-teal-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-ocean-500 transition-all hover:-translate-y-0.5"
        >
          Go to Login
        </router-link>
        
      </div>
    </div>
  </div>
</template>

<script setup>
</script>
