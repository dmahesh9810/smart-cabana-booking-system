import axios from 'axios';
import { useToast } from 'vue-toastification';

const api = axios.create({
    baseURL: 'http://localhost:8000/api/v1', // Pointing to Laravel backend API
    headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
    },
    withCredentials: true, // Required for Sanctum cookie-based auth
});

// Configure Request Interceptor (Optional: if token needs appending manually, though Sanctum uses cookies typically)
api.interceptors.request.use((config) => {
    const token = localStorage.getItem('token');
    if (token) {
        config.headers.Authorization = `Bearer ${token}`;
    }
    return config;
});

export default api;
