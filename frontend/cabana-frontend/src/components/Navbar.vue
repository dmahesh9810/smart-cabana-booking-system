<template>
  <nav class="bg-indigo-600 text-white p-4">
    <div class="container mx-auto flex justify-between items-center">
      <router-link to="/" class="text-xl font-bold">Smart Cabana</router-link>
      <ul class="flex space-x-4 items-center">
        <li><router-link to="/">Home</router-link></li>
        <template v-if="!authStore.isAuthenticated">
          <li><router-link to="/login">Login</router-link></li>
          <li><router-link to="/register">Register</router-link></li>
        </template>
        <template v-else>
          <li v-if="authStore.isAdmin"><router-link to="/admin">Admin Panel</router-link></li>
          <li v-else>
            <router-link to="/dashboard" class="mr-4">Dashboard</router-link>
            <router-link to="/my-payments">My Payments</router-link>
          </li>
          <li>
            <button @click="handleLogout" class="hover:text-indigo-200 transition-colors">
              Logout
            </button>
          </li>
        </template>
      </ul>
    </div>
  </nav>
</template>

<script setup>
import { useAuthStore } from '../store/authStore';
import { useRouter } from 'vue-router';

const authStore = useAuthStore();
const router = useRouter();

const handleLogout = async () => {
  await authStore.logout();
  router.push('/login');
};
</script>
