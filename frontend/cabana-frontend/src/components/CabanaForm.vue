<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm" @click="$emit('close')"></div>

    <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl flex flex-col max-h-[92vh] overflow-hidden">
      <!-- Header -->
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-gradient-to-r from-indigo-50 to-slate-50 shrink-0">
        <div class="flex items-center gap-3">
          <div class="w-8 h-8 rounded-lg bg-indigo-600 flex items-center justify-center">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <h3 class="text-lg font-bold text-slate-900">{{ cabana ? 'Edit Cabana' : 'Add New Cabana' }}</h3>
        </div>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-600 transition-colors rounded-lg p-1.5 hover:bg-slate-100">
          <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>

      <!-- Scrollable Body -->
      <div class="p-6 overflow-y-auto flex-1 space-y-5">

        <!-- Cabana Name -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Cabana Name <span class="text-red-500">*</span></label>
          <input
            type="text"
            v-model="form.name"
            required
            placeholder="e.g. Ocean Breeze Cabana"
            class="w-full border border-slate-300 rounded-xl py-2.5 px-3.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-shadow"
          />
        </div>

        <!-- Price + Capacity -->
        <div class="grid grid-cols-2 gap-4">
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Price per Night (LKR) <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm font-medium">Rs.</span>
              <input
                type="number"
                step="0.01"
                v-model.number="form.price_per_night"
                required
                min="0"
                placeholder="0.00"
                class="w-full border border-slate-300 rounded-xl py-2.5 pl-10 pr-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-shadow"
              />
            </div>
          </div>
          <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Capacity <span class="text-red-500">*</span></label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">👥</span>
              <input
                type="number"
                v-model.number="form.max_guests"
                required
                min="1"
                placeholder="e.g. 6"
                class="w-full border border-slate-300 rounded-xl py-2.5 pl-10 pr-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-shadow"
              />
            </div>
          </div>
        </div>

        <!-- Location -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Location</label>
          <div class="relative">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm">📍</span>
            <input
              type="text"
              v-model="form.location"
              placeholder="e.g. Mirissa, Sri Lanka"
              class="w-full border border-slate-300 rounded-xl py-2.5 pl-9 pr-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm transition-shadow"
            />
          </div>
        </div>

        <!-- Description -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Description <span class="text-red-500">*</span></label>
          <textarea
            v-model="form.description"
            rows="3"
            required
            placeholder="Describe the cabana — amenities, views, nearby attractions..."
            class="w-full border border-slate-300 rounded-xl py-2.5 px-3.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm resize-none transition-shadow"
          ></textarea>
        </div>

        <!-- Image Upload -->
        <div>
          <label class="block text-sm font-semibold text-slate-700 mb-1.5">Cover Image</label>

          <!-- Drag & Drop Zone -->
          <div
            class="border-2 border-dashed rounded-xl transition-all duration-200 cursor-pointer"
            :class="isDragging ? 'border-indigo-500 bg-indigo-50' : 'border-slate-300 hover:border-indigo-400 hover:bg-slate-50'"
            @dragover.prevent="isDragging = true"
            @dragleave.prevent="isDragging = false"
            @drop.prevent="handleDrop"
            @click="triggerFileInput"
          >
            <!-- Preview image -->
            <div v-if="previewImage" class="relative group">
              <img
                :src="previewImage"
                class="w-full h-52 object-cover rounded-xl"
                alt="Preview"
              />
              <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity rounded-xl flex items-center justify-center">
                <span class="text-white text-sm font-semibold">Click or drop to replace</span>
              </div>
              <button
                type="button"
                @click.stop="removeImage"
                class="absolute top-2 right-2 bg-red-500 text-white rounded-full w-6 h-6 flex items-center justify-center hover:bg-red-600 transition-colors shadow"
              >
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M6 18L18 6M6 6l12 12"/>
                </svg>
              </button>
            </div>

            <!-- Upload prompt -->
            <div v-else class="py-10 flex flex-col items-center gap-3">
              <div class="w-14 h-14 rounded-full bg-indigo-50 border-2 border-indigo-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
              </div>
              <div class="text-center">
                <p class="text-sm font-semibold text-indigo-600">Click to upload or drag & drop</p>
                <p class="text-xs text-slate-400 mt-1">JPEG, PNG, GIF, WebP — max 2 MB</p>
              </div>
            </div>
          </div>

          <!-- Hidden file input -->
          <input
            ref="fileInputRef"
            type="file"
            accept="image/jpeg,image/png,image/gif,image/webp"
            class="sr-only"
            @change="handleFileChange"
          />
        </div>

        <!-- Status Toggle -->
        <div class="flex items-center justify-between p-4 bg-slate-50 rounded-xl border border-slate-200">
          <div>
            <p class="text-sm font-semibold text-slate-700">Listing Status</p>
            <p class="text-xs text-slate-500 mt-0.5">
              {{ form.is_active ? 'Cabana is visible & bookable by guests' : 'Cabana is hidden from guests' }}
            </p>
          </div>
          <button
            type="button"
            @click="form.is_active = !form.is_active"
            class="relative inline-flex h-6 w-11 flex-shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
            :class="form.is_active ? 'bg-emerald-500' : 'bg-slate-300'"
            role="switch"
            :aria-checked="form.is_active"
          >
            <span
              class="inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
              :class="form.is_active ? 'translate-x-5' : 'translate-x-0'"
            ></span>
          </button>
        </div>

        <!-- Error -->
        <div v-if="error" class="flex items-start gap-3 text-sm bg-red-50 border border-red-200 text-red-700 p-3.5 rounded-xl">
          <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ error }}
        </div>
      </div>

      <!-- Footer Actions -->
      <div class="bg-slate-50 px-6 py-4 flex items-center justify-end gap-3 border-t border-slate-100 shrink-0">
        <button
          @click="$emit('close')"
          type="button"
          class="px-5 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50 transition-colors shadow-sm"
        >
          Cancel
        </button>
        <button
          @click="submitForm"
          :disabled="loading"
          class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-semibold text-white bg-indigo-600 rounded-xl hover:bg-indigo-700 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition-colors shadow-sm"
        >
          <svg v-if="loading" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 8 8 0 12h4z"/>
          </svg>
          {{ cabana ? 'Save Changes' : 'Create Cabana' }}
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    cabana: { type: Object, default: null },
    loading: Boolean,
    error: String,
});

const emit = defineEmits(['close', 'submit']);

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

const form = ref({
    name: '',
    price_per_night: '',
    max_guests: '',
    description: '',
    location: '',
    is_active: true,
});

const imageFile = ref(null);
const previewImage = ref(null);
const isDragging = ref(false);
const fileInputRef = ref(null);

onMounted(() => {
    if (props.cabana) {
        form.value = {
            name: props.cabana.name || '',
            price_per_night: props.cabana.price_per_night || '',
            max_guests: props.cabana.max_guests || '',
            description: props.cabana.description || '',
            location: props.cabana.location || '',
            is_active: props.cabana.is_active !== undefined ? props.cabana.is_active : true,
        };
        // Show existing image as preview (not a new upload — just display)
        if (props.cabana.image && props.cabana.image !== PLACEHOLDER) {
            previewImage.value = props.cabana.image;
        }
    }
});

const triggerFileInput = () => {
    fileInputRef.value?.click();
};

const applyFile = (file) => {
    if (!file || !file.type.startsWith('image/')) return;
    if (file.size > 2 * 1024 * 1024) {
        alert('Image must be smaller than 2 MB.');
        return;
    }
    imageFile.value = file;
    // Create a local Object URL for instant preview — no server round-trip needed
    if (previewImage.value && previewImage.value.startsWith('blob:')) {
        URL.revokeObjectURL(previewImage.value);
    }
    previewImage.value = URL.createObjectURL(file);
};

const handleFileChange = (event) => {
    applyFile(event.target.files[0]);
};

const handleDrop = (event) => {
    isDragging.value = false;
    applyFile(event.dataTransfer.files[0]);
};

const removeImage = () => {
    if (previewImage.value?.startsWith('blob:')) {
        URL.revokeObjectURL(previewImage.value);
    }
    imageFile.value = null;
    previewImage.value = null;
    if (fileInputRef.value) fileInputRef.value.value = '';
};

const submitForm = () => {
    if (imageFile.value) {
        // Must use FormData so Laravel can receive the file
        const fd = new FormData();
        Object.entries(form.value).forEach(([k, v]) => {
            // Convert booleans to 1/0 — PHP/Laravel boolean handling in FormData
            fd.append(k, typeof v === 'boolean' ? (v ? 1 : 0) : v);
        });
        fd.append('image', imageFile.value);
        emit('submit', fd);
    } else {
        emit('submit', { ...form.value });
    }
};
</script>
