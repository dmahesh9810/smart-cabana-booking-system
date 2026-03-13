<template>
  <div class="min-h-[80vh] bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
      <!-- Icon / Logo Area -->
      <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4 border-4 border-white shadow-sm">
        <svg class="h-8 w-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
           <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
        </svg>
      </div>
      <h2 class="text-center text-3xl font-extrabold text-slate-900 tracking-tight">
        Welcome Back
      </h2>
      <p class="mt-2 text-center text-sm text-slate-600">
        Don't have an account?
        <router-link to="/register" class="font-bold text-indigo-600 hover:text-indigo-500 transition-colors">
          Register here
        </router-link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md">
      <div class="bg-white py-10 px-8 shadow-xl sm:rounded-2xl border border-slate-100 relative overflow-hidden">
        
        <!-- Loading Overlay -->
        <div v-if="authStore.loading" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center">
             <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
        </div>

        <form class="space-y-6" @submit.prevent="handleLogin">
          
          <div v-if="authStore.error" class="bg-red-50 text-red-600 text-sm p-4 rounded-xl font-medium border border-red-100 flex items-start">
             <svg class="h-5 w-5 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             <span>{{ authStore.error }}</span>
          </div>

          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
              Email Address
            </label>
            <input id="email" type="email" required v-model="form.email" 
              class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium" 
              placeholder="you@example.com" />
          </div>

          <div>
            <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
              Password
            </label>
            <input id="password" type="password" required v-model="form.password" 
              class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium" 
              placeholder="••••••••" />
          </div>

          <div class="flex items-center justify-between pt-2">
            <div class="flex items-center">
              <input id="remember-me" name="remember-me" type="checkbox" class="h-4 w-4 text-indigo-600 focus:ring-indigo-500 border-slate-300 rounded transition-colors" />
              <label for="remember-me" class="ml-2 block text-sm text-slate-700 font-medium">
                Remember me
              </label>
            </div>
          </div>

          <div class="pt-2">
            <button type="submit" :disabled="authStore.loading" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-all active:scale-[0.98]">
              Sign in
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import { useToast } from 'vue-toastification';

const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const form = ref({
    email: '',
    password: ''
});

const handleLogin = async () => {
    try {
        await authStore.login(form.value);
        toast.success('Successfully logged in!');
        
        // Redirect based on role
        if (authStore.isAdmin) {
            router.push('/admin');
        } else {
            router.push('/dashboard');
        }
    } catch (error) {
        // Error is technically already caught and pushed to authStore.error, but interceptor toast fires too.
        console.error("Login Failed", error);
    }
};
</script>
