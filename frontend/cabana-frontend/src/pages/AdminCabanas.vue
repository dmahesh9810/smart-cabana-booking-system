<template>
  <div class="space-y-6">
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Cabana Management</h1>
        <p class="text-slate-500 text-sm mt-1">Add, edit, and organize system cabanas.</p>
      </div>
      <button @click="openCreateModal" class="inline-flex items-center justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none transition-colors">
        <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
        </svg>
        Add Cabana
      </button>
    </div>

    <!-- Error/Loading -->
    <div v-if="adminStore.error" class="bg-red-50 text-red-600 p-4 rounded-xl border border-red-200">
      {{ adminStore.error }}
    </div>

    <div v-if="adminStore.loading && !cabanas.length" class="flex justify-center p-12">
      <div class="animate-spin rounded-full h-10 w-10 border-b-2 border-indigo-600"></div>
    </div>

    <!-- Cabanas Table -->
    <div v-else class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-200">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cabana</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Price / Night</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Capacity</th>
              <th class="px-6 py-4 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-4 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-100">
            <tr v-for="cabana in cabanas" :key="cabana.id" class="hover:bg-slate-50 transition-colors">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center">
                  <div class="flex-shrink-0 h-10 w-10 bg-slate-200 rounded-lg overflow-hidden border border-slate-100">
                    <img v-if="cabana.image" :src="cabana.image" loading="lazy" alt="" class="h-10 w-10 object-cover" />
                  </div>
                  <div class="ml-4 flex flex-col justify-center">
                    <div class="text-sm font-bold text-slate-900">{{ cabana.name }}</div>
                    <div class="text-xs text-slate-500">📍 {{ cabana.location || 'N/A' }}</div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900 font-medium">${{ cabana.price_per_night }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-slate-900">{{ cabana.max_guests }} Persons</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">
                  Active
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                <button @click="openEditModal(cabana)" class="text-indigo-600 hover:text-indigo-900 hover:underline">Edit</button>
                <button @click="confirmDelete(cabana.id)" class="text-red-600 hover:text-red-900 hover:underline">Delete</button>
              </td>
            </tr>
            <tr v-if="!cabanas.length">
               <td colspan="5" class="py-12 text-center text-slate-500">No cabanas found.</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Modals -->
    <Teleport to="body">
       <CabanaForm 
         v-if="showModal" 
         :cabana="selectedCabana"
         :loading="adminStore.loading"
         :error="adminStore.error"
         @close="closeModal"
         @submit="handleSubmit"
       />
    </Teleport>

  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue';
import { useAdminStore } from '../store/adminStore';
import CabanaForm from '../components/CabanaForm.vue';

const adminStore = useAdminStore();
const cabanas = computed(() => adminStore.cabanas);

const showModal = ref(false);
const selectedCabana = ref(null);

onMounted(() => {
    adminStore.fetchCabanas();
});

const openCreateModal = () => {
    adminStore.error = null;
    selectedCabana.value = null;
    showModal.value = true;
};

const openEditModal = (cabana) => {
    adminStore.error = null;
    selectedCabana.value = cabana;
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const handleSubmit = async (payload) => {
    try {
        if (selectedCabana.value) {
            await adminStore.updateCabana(selectedCabana.value.id, payload);
        } else {
            await adminStore.createCabana(payload);
        }
        closeModal();
    } catch (e) {
        // Handled in store
    }
};

const confirmDelete = async (id) => {
    if (confirm('Are you sure you want to delete this cabana? This action cannot be undone.')) {
        await adminStore.deleteCabana(id);
    }
};
</script>
