<template>
  <div class="space-y-6">

    <!-- Toast Notification -->
    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-3">
      <div v-if="adminStore.successMessage" class="fixed top-5 right-5 z-[60] flex items-center gap-3 bg-emerald-600 text-white px-5 py-3.5 rounded-2xl shadow-xl shadow-emerald-900/20">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
        </svg>
        <span class="text-sm font-semibold">{{ adminStore.successMessage }}</span>
      </div>
    </Transition>

    <!-- Error Toast -->
    <Transition enter-active-class="transition-all duration-300 ease-out" enter-from-class="opacity-0 -translate-y-3" enter-to-class="opacity-100 translate-y-0" leave-active-class="transition-all duration-200 ease-in" leave-from-class="opacity-100 translate-y-0" leave-to-class="opacity-0 -translate-y-3">
      <div v-if="adminStore.error" class="flex items-center gap-3 bg-red-50 text-red-700 border border-red-200 px-4 py-3.5 rounded-xl">
        <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <span class="text-sm font-medium">{{ adminStore.error }}</span>
        <button @click="adminStore.error = null" class="ml-auto text-red-400 hover:text-red-600">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
        </button>
      </div>
    </Transition>

    <!-- Page Header -->
    <div class="flex items-center justify-between">
      <div>
        <h1 class="text-2xl font-bold text-slate-900">Cabana Management</h1>
        <p class="text-slate-500 text-sm mt-1">{{ cabanas.length }} cabana{{ cabanas.length !== 1 ? 's' : '' }} registered in the system.</p>
      </div>
      <button
        @click="openCreateModal"
        class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 active:scale-95 transition-all"
      >
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
        </svg>
        Add Cabana
      </button>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="adminStore.loading && !cabanas.length" class="flex justify-center py-16">
      <div class="flex flex-col items-center gap-3">
        <div class="animate-spin rounded-full h-10 w-10 border-[3px] border-indigo-200 border-t-indigo-600"></div>
        <p class="text-sm text-slate-400">Loading cabanas...</p>
      </div>
    </div>

    <!-- Table -->
    <div v-else class="bg-white rounded-2xl shadow-sm overflow-hidden border border-slate-200">
      <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100">
          <thead class="bg-slate-50">
            <tr>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Cabana</th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Price / Night</th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Capacity</th>
              <th class="px-6 py-3.5 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Status</th>
              <th class="px-6 py-3.5 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Actions</th>
            </tr>
          </thead>
          <tbody class="bg-white divide-y divide-slate-50">
            <tr
              v-for="cabana in cabanas"
              :key="cabana.id"
              class="hover:bg-slate-50/70 transition-colors group"
            >
              <!-- Cabana Info -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-4">
                  <div class="h-14 w-20 rounded-xl overflow-hidden border border-slate-100 bg-slate-100 shrink-0">
                    <img
                      :src="cabana.image || PLACEHOLDER"
                      :alt="cabana.name"
                      loading="lazy"
                      class="h-full w-full object-cover"
                      @error="e => e.target.src = PLACEHOLDER"
                    />
                  </div>
                  <div>
                    <p class="text-sm font-bold text-slate-900">{{ cabana.name }}</p>
                    <p class="text-xs text-slate-400 mt-0.5">📍 {{ cabana.location || 'Location not set' }}</p>
                  </div>
                </div>
              </td>

              <!-- Price -->
              <td class="px-6 py-4 whitespace-nowrap">
                <p class="text-sm font-semibold text-slate-800">{{ formatLKR(cabana.price_per_night) }}</p>
                <p class="text-xs text-slate-400">per night</p>
              </td>

              <!-- Capacity -->
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="flex items-center gap-1.5 text-sm text-slate-700">
                  <span class="text-base">👥</span>
                  <span class="font-medium">{{ cabana.max_guests }}</span>
                  <span class="text-slate-400">guests</span>
                </div>
              </td>

              <!-- Status Badge -->
              <td class="px-6 py-4 whitespace-nowrap">
                <span
                  class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold"
                  :class="cabana.is_active ? 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-200' : 'bg-amber-50 text-amber-700 ring-1 ring-amber-200'"
                >
                  <span class="w-1.5 h-1.5 rounded-full" :class="cabana.is_active ? 'bg-emerald-500' : 'bg-amber-500'"></span>
                  {{ cabana.is_active ? 'Active' : 'Inactive' }}
                </span>
              </td>

              <!-- Actions -->
              <td class="px-6 py-4 whitespace-nowrap text-right">
                <div class="flex items-center justify-end gap-2">
                  <!-- Toggle Status -->
                  <button
                    @click="toggleStatus(cabana)"
                    :disabled="togglingId === cabana.id"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition-all border"
                    :class="cabana.is_active
                      ? 'bg-amber-50 text-amber-700 border-amber-200 hover:bg-amber-100'
                      : 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100'"
                  >
                    <svg v-if="togglingId === cabana.id" class="animate-spin w-3 h-3" fill="none" viewBox="0 0 24 24">
                      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 8 8 0 12h4z"/>
                    </svg>
                    <svg v-else class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 11V7a4 4 0 118 0m-4 8v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2z"/>
                    </svg>
                    {{ cabana.is_active ? 'Deactivate' : 'Activate' }}
                  </button>

                  <!-- Edit -->
                  <button
                    @click="openEditModal(cabana)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-indigo-50 text-indigo-700 border border-indigo-200 hover:bg-indigo-100 transition-colors"
                  >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                    </svg>
                    Edit
                  </button>

                  <!-- Delete -->
                  <button
                    @click="openDeleteModal(cabana)"
                    class="inline-flex items-center gap-1 px-3 py-1.5 rounded-lg text-xs font-semibold bg-red-50 text-red-700 border border-red-200 hover:bg-red-100 transition-colors"
                  >
                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Delete
                  </button>
                </div>
              </td>
            </tr>

            <!-- Empty state -->
            <tr v-if="!cabanas.length && !adminStore.loading">
              <td colspan="5" class="py-16 text-center">
                <div class="flex flex-col items-center gap-3">
                  <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center">
                    <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                      <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                  </div>
                  <p class="text-slate-500 font-medium">No cabanas found</p>
                  <p class="text-slate-400 text-xs">Click <strong>Add Cabana</strong> to get started.</p>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- CabanaForm Modal (Create / Edit) -->
    <Teleport to="body">
      <CabanaForm
        v-if="showFormModal"
        :cabana="selectedCabana"
        :loading="adminStore.loading"
        :error="adminStore.error"
        @close="closeFormModal"
        @submit="handleSubmit"
      />
    </Teleport>

    <!-- Delete Confirmation Modal -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-opacity duration-150" leave-from-class="opacity-100" leave-to-class="opacity-0">
        <div v-if="showDeleteModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showDeleteModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-6 flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-red-50 border-2 border-red-100 flex items-center justify-center">
              <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-bold text-slate-900">Delete Cabana?</h3>
              <p class="text-sm text-slate-500 mt-1">
                <strong class="text-slate-700">{{ cabanaToDelete?.name }}</strong> will be permanently removed along with all its images. This cannot be undone.
              </p>
            </div>
            <div class="flex gap-3 w-full pt-2">
              <button
                @click="showDeleteModal = false"
                class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors"
              >
                Cancel
              </button>
              <button
                @click="confirmDelete"
                :disabled="adminStore.loading"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 disabled:opacity-60 transition-colors"
              >
                <svg v-if="adminStore.loading" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 8 8 0 12h4z"/>
                </svg>
                Yes, Delete
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { onMounted, computed, ref } from 'vue';
import { useAdminStore } from '../store/adminStore';
import CabanaForm from '../components/CabanaForm.vue';

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const adminStore = useAdminStore();
const cabanas = computed(() => adminStore.cabanas);

// Form Modal
const showFormModal = ref(false);
const selectedCabana = ref(null);

// Delete Modal
const showDeleteModal = ref(false);
const cabanaToDelete = ref(null);

// Toggle loading per-row
const togglingId = ref(null);

onMounted(() => {
    adminStore.fetchCabanas();
});

// ── Format currency ──────────────────────────────────────────
const formatLKR = (value) => {
    if (value === null || value === undefined) return 'N/A';
    return new Intl.NumberFormat('si-LK', {
        style: 'currency',
        currency: 'LKR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(value);
};

// ── Form Modal ────────────────────────────────────────────────
const openCreateModal = () => {
    adminStore.error = null;
    selectedCabana.value = null;
    showFormModal.value = true;
};

const openEditModal = (cabana) => {
    adminStore.error = null;
    selectedCabana.value = cabana;
    showFormModal.value = true;
};

const closeFormModal = () => {
    showFormModal.value = false;
};

const handleSubmit = async (payload) => {
    try {
        if (selectedCabana.value) {
            await adminStore.updateCabana(selectedCabana.value.id, payload);
        } else {
            await adminStore.createCabana(payload);
        }
        closeFormModal();
    } catch (e) {
        // Error is stored in adminStore.error — shown in the modal
    }
};

// ── Delete Modal ──────────────────────────────────────────────
const openDeleteModal = (cabana) => {
    cabanaToDelete.value = cabana;
    showDeleteModal.value = true;
};

const confirmDelete = async () => {
    try {
        await adminStore.deleteCabana(cabanaToDelete.value.id);
        showDeleteModal.value = false;
        cabanaToDelete.value = null;
    } catch (e) {
        showDeleteModal.value = false;
    }
};

// ── Toggle Status ─────────────────────────────────────────────
const toggleStatus = async (cabana) => {
    togglingId.value = cabana.id;
    try {
        await adminStore.toggleCabanaStatus(cabana.id);
    } finally {
        togglingId.value = null;
    }
};
</script>
