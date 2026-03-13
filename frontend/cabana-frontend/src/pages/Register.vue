<template>
  <div class="min-h-[80vh] bg-slate-50 flex flex-col justify-center py-12 sm:px-6 lg:px-8">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
       <div class="mx-auto flex items-center justify-center h-16 w-16 rounded-full bg-indigo-100 mb-4 border-4 border-white shadow-sm">
        <svg class="h-8 w-8 text-indigo-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
        </svg>
      </div>
      <h2 class="text-center text-3xl font-extrabold text-slate-900 tracking-tight">
        Create an Account
      </h2>
      <p class="mt-2 text-center text-sm text-slate-600">
        Already have an account?
        <router-link to="/login" class="font-bold text-indigo-600 hover:text-indigo-500 transition-colors">
          Sign in instead
        </router-link>
      </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-[32rem]">
      <div class="bg-white py-10 px-8 shadow-xl sm:rounded-2xl border border-slate-100 relative overflow-hidden">
        
        <!-- Loading Overlay -->
        <div v-if="authStore.loading" class="absolute inset-0 bg-white/60 backdrop-blur-sm z-10 flex items-center justify-center">
             <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
        </div>

        <form class="space-y-5" @submit.prevent="handleRegister">
          
          <div v-if="authStore.error" class="bg-red-50 text-red-600 text-sm p-4 rounded-xl font-medium border border-red-100 flex items-start">
             <svg class="h-5 w-5 mr-2 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
             </svg>
             <span>{{ authStore.error }}</span>
          </div>

          <div>
            <label for="name" class="block text-sm font-semibold text-slate-700 mb-2">
              Full Name
            </label>
            <input id="name" type="text" required v-model="form.name" 
              class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium" 
              placeholder="John Doe" />
          </div>

          <div>
            <label for="email" class="block text-sm font-semibold text-slate-700 mb-2">
              Email Address
            </label>
            <input id="email" type="email" required v-model="form.email" 
              class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium" 
              placeholder="you@example.com" />
          </div>

          <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
             <div>
               <label for="password" class="block text-sm font-semibold text-slate-700 mb-2">
                 Password
               </label>
               <input id="password" type="password" required v-model="form.password" 
                 class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium" 
                 placeholder="••••••••" />
             </div>
   
             <div>
               <label for="password_confirmation" class="block text-sm font-semibold text-slate-700 mb-2">
                 Confirm Password
               </label>
               <input id="password_confirmation" type="password" required v-model="form.password_confirmation" 
                 class="appearance-none block w-full px-4 py-3 border border-slate-300 rounded-xl shadow-sm placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-all font-medium"
                 :class="{'border-red-300 focus:ring-red-500 focus:border-red-500': passwordsMismatch}"
                 placeholder="••••••••" />
             </div>
          </div>

          <div v-if="passwordsMismatch" class="text-sm font-medium text-red-600 mt-1">
            Passwords do not match
          </div>

          <div class="pt-4">
            <button type="submit" :disabled="authStore.loading || passwordsMismatch" class="w-full flex justify-center py-3.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-bold text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-all active:scale-[0.98]">
              Create Account
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue';
import { useRouter } from 'vue-router';
import { useAuthStore } from '../store/authStore';
import { useToast } from 'vue-toastification';

const router = useRouter();
const authStore = useAuthStore();
const toast = useToast();

const form = ref({
    name: '',
    email: '',
    password: '',
    password_confirmation: ''
});

const passwordsMismatch = computed(() => {
    return form.value.password_confirmation.length > 0 && form.value.password !== form.value.password_confirmation;
});

const handleRegister = async () => {
    if (passwordsMismatch.value) return;

    try {
        await authStore.register(form.value);
        toast.success('Account created successfully! Welcome!');
        
        // Immediately log them in / route them since token is saved
        router.push('/dashboard');
        
    } catch (error) {
        console.error("Registration Failed", error);
    }
};
</script>
