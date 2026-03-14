import { createRouter, createWebHistory } from 'vue-router'
import { useAuthStore } from '../store/authStore'

import AppLayout from '../App.vue' // Adjust if you have a main layout
import Home from '../pages/Home.vue'
import CabanaDetails from '../pages/CabanaDetails.vue'
import Booking from '../pages/Booking.vue'
import Login from '../pages/Login.vue'
import Register from '../pages/Register.vue'
import UserDashboard from '../pages/UserDashboard.vue'
import UserPayments from '../pages/UserPayments.vue'
import BookingDetails from '../pages/BookingDetails.vue'
import Payment from '../pages/Payment.vue'
import PaymentSuccess from '../pages/PaymentSuccess.vue'
import PaymentCancel from '../pages/PaymentCancel.vue'

// Admin Views
import AdminLayout from '../components/AdminLayout.vue'
import AdminDashboard from '../pages/AdminDashboard.vue'
import AdminCabanas from '../pages/AdminCabanas.vue'
import AdminBookings from '../pages/AdminBookings.vue'
import AdminPayments from '../pages/AdminPayments.vue'
import AdminReports from '../pages/AdminReports.vue'

const routes = [
    // --- User Facing Routes ---
    { path: '/', component: Home, name: 'Home' },
    { path: '/cabana/:id', component: CabanaDetails, name: 'CabanaDetails' },

    // Auth Requirements (Guests Only)
    { path: '/login', component: Login, name: 'Login', meta: { requiresGuest: true } },
    { path: '/register', component: Register, name: 'Register', meta: { requiresGuest: true } },

    // Auth Requirements (Logged In Users Only)
    { path: '/booking', component: Booking, name: 'Booking', meta: { requiresAuth: true } },
    { path: '/payment', component: Payment, name: 'Payment', meta: { requiresAuth: true } },
    { path: '/booking/success', component: PaymentSuccess, name: 'PaymentSuccess', meta: { requiresAuth: true } },
    { path: '/booking/cancel', component: PaymentCancel, name: 'PaymentCancel', meta: { requiresAuth: true } },
    { path: '/dashboard', component: UserDashboard, name: 'UserDashboard', meta: { requiresAuth: true } },
    { path: '/dashboard/bookings/:id', component: BookingDetails, name: 'BookingDetails', meta: { requiresAuth: true } },
    { path: '/my-payments', component: UserPayments, name: 'UserPayments', meta: { requiresAuth: true } },

    // --- Admin Facing Routes ---
    {
        path: '/admin',
        component: AdminLayout,
        meta: { requiresAdmin: true },
        children: [
            { path: '', component: AdminDashboard, name: 'AdminDashboard' },
            { path: 'cabanas', component: AdminCabanas, name: 'AdminCabanas' },
            { path: 'bookings', component: AdminBookings, name: 'AdminBookings' },
            { path: 'payments', component: AdminPayments, name: 'AdminPayments' },
            { path: 'reports', component: AdminReports, name: 'AdminReports' },
        ]
    },
]

const router = createRouter({
    history: createWebHistory(),
    routes,
})

// Navigation Guards
router.beforeEach(async (to, from, next) => {
    const authStore = useAuthStore()

    // Determine metadata requirements
    const requiresAuth = to.matched.some(record => record.meta.requiresAuth || record.meta.requiresAdmin)
    const requiresAdmin = to.matched.some(record => record.meta.requiresAdmin)
    const requiresGuest = to.matched.some(record => record.meta.requiresGuest)

    // Hydrate gracefully only when navigating secured zones avoiding double payload on boot.
    if ((requiresAuth || requiresAdmin || requiresGuest) && !authStore.user && authStore.token) {
        await authStore.fetchUser()
    }

    // Guard Rules:
    if (requiresAuth && !authStore.isAuthenticated) {
        // Wants secure content but unauthenticated
        next({ name: 'Login' })
    } else if (requiresAdmin && !authStore.isAdmin) {
        // Wants admin content but unauthorized
        next({ name: 'Home' })
    } else if (requiresGuest && authStore.isAuthenticated) {
        // Wants to explicitly login/register but already has state
        if (authStore.isAdmin) {
            next({ name: 'AdminDashboard' })
        } else {
            next({ name: 'UserDashboard' })
        }
    } else {
        // Normal public pass
        next()
    }
})

export default router
