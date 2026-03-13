<template>
  <div class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 pb-20 pt-4 sm:py-20 lg:py-24">
    <!-- Overlay -->
    <div class="fixed inset-0 bg-slate-900/75 backdrop-blur-sm transition-opacity" @click="$emit('close')"></div>

    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden flex flex-col max-h-full">
      <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100 bg-slate-50/50">
        <h3 class="text-lg font-bold text-slate-900">{{ cabana ? 'Edit Cabana' : 'Add New Cabana' }}</h3>
        <button @click="$emit('close')" class="text-slate-400 hover:text-slate-500 transition-colors bg-white rounded-full p-1 hover:bg-slate-100">
          <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
          </svg>
        </button>
      </div>
      
      <div class="p-6 overflow-y-auto flex-1">
         <form @submit.prevent="submitForm" class="space-y-5">
            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Cabana Name</label>
              <input type="text" v-model="form.name" required class="w-full border-slate-300 rounded-lg py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
            </div>

            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Price per Night ($)</label>
                <input type="number" step="0.01" v-model.number="form.price_per_night" required class="w-full border-slate-300 rounded-lg py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
              </div>
              <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Capacity (Persons)</label>
                <input type="number" v-model.number="form.max_guests" required class="w-full border-slate-300 rounded-lg py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
              </div>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Description</label>
              <textarea v-model="form.description" rows="3" required class="w-full border-slate-300 rounded-lg py-3 px-3 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm resize-none"></textarea>
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Location</label>
              <input type="text" v-model="form.location" class="w-full border-slate-300 rounded-lg py-2.5 px-3 focus:ring-indigo-500 focus:border-indigo-500 shadow-sm" />
            </div>

            <div>
              <label class="block text-sm font-medium text-slate-700 mb-1">Cover Image</label>
              <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-slate-300 border-dashed rounded-xl overflow-hidden relative" :class="{'bg-slate-50': !previewImage}">
                <div v-if="previewImage" class="absolute inset-0">
                   <img :src="previewImage" class="w-full h-full object-cover opacity-50" />
                </div>
                <div class="space-y-1 text-center relative z-10">
                  <svg class="mx-auto h-12 w-12 text-slate-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                    <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                  </svg>
                  <div class="flex text-sm text-slate-600 justify-center">
                    <label class="relative cursor-pointer bg-white rounded-md font-medium text-indigo-600 hover:text-indigo-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-indigo-500 px-2 shadow-sm">
                      <span>Upload a file</span>
                      <input type="file" @change="handleImageUpload" accept="image/*" class="sr-only" />
                    </label>
                    <p class="pl-1">or drag and drop</p>
                  </div>
                  <p class="text-xs text-slate-500">PNG, JPG, GIF up to 10MB</p>
                </div>
              </div>
            </div>

            <!-- Error Feed -->
            <div v-if="error" class="text-red-600 text-sm bg-red-50 p-3 rounded-lg border border-red-100">
               {{ error }}
            </div>
         </form>
      </div>

      <div class="bg-slate-50 px-6 py-4 flex flex-row-reverse border-t border-slate-100 gap-3">
         <button @click="submitForm" :disabled="loading" class="w-full inline-flex justify-center rounded-xl border border-transparent shadow-sm px-5 py-2.5 bg-indigo-600 text-base font-medium text-white hover:bg-indigo-700 focus:outline-none disabled:opacity-50 sm:w-auto sm:text-sm transition-colors">
            <span v-if="loading" class="animate-spin h-4 w-4 border-2 border-white border-t-transparent rounded-full mr-2 self-center"></span>
            {{ cabana ? 'Save Changes' : 'Create Cabana' }}
         </button>
         <button @click="$emit('close')" type="button" class="mt-3 w-full inline-flex justify-center rounded-xl border border-slate-300 shadow-sm px-5 py-2.5 bg-white text-base font-medium text-slate-700 hover:text-slate-500 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:w-auto sm:text-sm transition-colors">
            Cancel
         </button>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';

const props = defineProps({
    cabana: {
        type: Object,
        default: null
    },
    loading: Boolean,
    error: String,
});

const emit = defineEmits(['close', 'submit']);

const form = ref({
    name: '',
    price_per_night: '',
    max_guests: '',
    description: '',
    location: '',
});
const imageFile = ref(null);
const previewImage = ref(null);

onMounted(() => {
    if (props.cabana) {
        form.value = { ...props.cabana };
        previewImage.value = props.cabana.image;
    }
});

const handleImageUpload = (event) => {
    const file = event.target.files[0];
    if (file) {
        imageFile.value = file;
        previewImage.value = URL.createObjectURL(file);
    }
};

const submitForm = () => {
    // Determine payload based on image presence. 
    // Laravel typically requires FormData for file uploads.
    let payload = { ...form.value };
    
    if (imageFile.value) {
        const formData = new FormData();
        Object.keys(form.value).forEach(key => formData.append(key, form.value[key]));
        formData.append('image', imageFile.value);
        payload = formData;
    }
    
    emit('submit', payload);
};
</script>
