<template>
  <div class="min-h-screen bg-slate-50 py-10">
    <div class="container mx-auto px-4 max-w-6xl">

      <!-- Dashboard Header -->
      <div class="flex items-center justify-between mb-8">
        <div>
          <h1 class="text-3xl font-extrabold text-slate-900">My Dashboard</h1>
          <p class="text-slate-500 mt-1">Welcome back, <span class="font-semibold text-ocean-600">{{ authStore.user?.name?.split(' ')[0] }}</span>!</p>
        </div>
        <router-link to="/" class="hidden sm:flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-ocean-600 to-teal-500 text-white rounded-xl text-sm font-semibold hover:from-ocean-700 hover:to-teal-600 transition-all shadow-sm">
          <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          Browse Cabanas
        </router-link>
      </div>

      <!-- Tabs -->
      <div class="flex gap-1 bg-white border border-slate-200 p-1 rounded-2xl mb-8 overflow-x-auto shadow-sm">
        <button
          v-for="tab in tabs"
          :key="tab.id"
          @click="activeTab = tab.id"
          class="flex items-center gap-2 px-5 py-2.5 rounded-xl text-sm font-semibold whitespace-nowrap transition-all"
          :class="activeTab === tab.id
            ? 'bg-ocean-600 text-white shadow-sm'
            : 'text-slate-500 hover:text-slate-900 hover:bg-slate-50'"
        >
          <span>{{ tab.icon }}</span>
          {{ tab.label }}
          <span v-if="tab.id === 'notifications' && notifStore.unreadCount"
            class="w-5 h-5 bg-red-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">
            {{ notifStore.unreadCount }}
          </span>
        </button>
      </div>

      <!-- ── Tab: My Bookings ─────────────────────────────── -->
      <div v-if="activeTab === 'bookings'">
        <div v-if="bookingStore.loading" class="flex justify-center items-center py-20">
          <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-ocean-600"></div>
        </div>
        <div v-else-if="!bookingStore.userBookings?.length" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
          <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-ocean-50 mb-6">
            <svg class="h-10 w-10 text-ocean-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
            </svg>
          </div>
          <p class="text-xl font-bold text-slate-800">No bookings yet</p>
          <p class="mt-2 text-slate-500">You haven't made any cabana reservations.</p>
          <router-link to="/" class="mt-6 inline-block bg-gradient-to-r from-ocean-600 to-teal-500 hover:from-ocean-700 hover:to-teal-600 text-white font-semibold py-3 px-8 rounded-xl shadow-md transition-all">Browse Cabanas</router-link>
        </div>
        <div v-else class="space-y-4">
          <div
            v-for="booking in bookingStore.userBookings"
            :key="booking.id"
            class="bg-white rounded-2xl shadow-sm border border-slate-100 hover:shadow-md transition-all duration-200"
          >
            <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-6">
              <!-- Cabana image -->
              <div class="w-full sm:w-28 h-24 rounded-xl overflow-hidden bg-slate-100 shrink-0">
                <img :src="getCabanaImage(booking)" :alt="booking.cabana?.name" class="w-full h-full object-cover"
                  @error="e => { e.target.src = PLACEHOLDER; e.target.onerror = null; }"/>
              </div>

              <!-- Main info -->
              <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-start justify-between gap-2 mb-2">
                  <div>
                    <h3 class="font-bold text-slate-900 text-lg">{{ booking.cabana?.name || 'Unknown Cabana' }}</h3>
                    <p class="text-xs text-slate-400 font-mono">Ref: {{ booking.booking_ref || `#${booking.id}` }}</p>
                  </div>
                  <span class="px-3 py-1 text-xs font-bold rounded-full border capitalize shrink-0" :class="statusClass(booking.status)">
                    {{ booking.status || 'pending' }}
                  </span>
                </div>
                <div class="flex flex-wrap gap-x-6 gap-y-1.5 text-sm text-slate-600 mb-3">
                  <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <strong>In:</strong>&nbsp;{{ formatDisplayDate(booking.check_in) }}
                  </span>
                  <span class="flex items-center gap-1">
                    <svg class="w-3.5 h-3.5 text-ocean-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <strong>Out:</strong>&nbsp;{{ formatDisplayDate(booking.check_out) }}
                  </span>
                  <span class="flex items-center font-semibold text-ocean-700">{{ formatLKR(booking.total_amount) }}</span>
                </div>
              </div>

              <!-- Actions -->
              <div class="flex items-center gap-2 shrink-0 flex-wrap">
                <router-link v-if="!booking.status || booking.status === 'pending'"
                  :to="{ path: '/payment', query: { booking_id: booking.id } }"
                  class="px-4 py-2 bg-ocean-600 hover:bg-ocean-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                  Pay Now
                </router-link>
                <router-link :to="{ name: 'BookingDetails', params: { id: booking.id } }"
                  class="px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 text-sm font-semibold rounded-xl transition-colors">
                  View Details
                </router-link>
                <button v-if="booking.status === 'completed' && !booking.has_review"
                  @click="openReviewModal(booking)"
                  class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold rounded-xl shadow-sm transition-colors">
                  Leave Review
                </button>
                <button
                  v-if="booking.status === 'pending' || booking.status === 'confirmed'"
                  @click="openCancelModal(booking)"
                  class="px-4 py-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 text-sm font-semibold rounded-xl transition-colors">
                  Cancel
                </button>
              </div>
            </div>
          </div>
        </div>

        <!-- Recommendations -->
        <div class="mt-14">
          <div class="flex items-center mb-8">
            <div class="flex-1 h-px bg-slate-200"></div>
            <p class="mx-4 text-xs font-semibold text-slate-400 uppercase tracking-wider">Recommended for You</p>
            <div class="flex-1 h-px bg-slate-200"></div>
          </div>
          <RecommendedForYou />
        </div>
      </div>

      <!-- ── Tab: My Reviews ──────────────────────────────── -->
      <div v-else-if="activeTab === 'reviews'">
        <div v-if="reviewsLoading" class="flex justify-center py-16">
          <div class="animate-spin h-10 w-10 rounded-full border-b-2 border-ocean-600"></div>
        </div>
        <div v-else-if="!userReviews.length" class="text-center py-20 bg-white rounded-2xl shadow-sm border border-slate-100">
          <div class="text-5xl mb-4">⭐</div>
          <p class="text-xl font-bold text-slate-800">No reviews yet</p>
          <p class="text-slate-500 mt-2">You haven't reviewed any cabana stays.</p>
        </div>
        <div v-else class="space-y-4">
          <div v-for="review in userReviews" :key="review.id"
            class="bg-white rounded-2xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-start justify-between">
              <div>
                <h3 class="font-bold text-slate-900">{{ review.cabana?.name || 'Cabana Stay' }}</h3>
                <p class="text-xs text-slate-400 mt-0.5">{{ formatDisplayDate(review.created_at) }}</p>
              </div>
              <div class="flex items-center gap-0.5">
                <svg v-for="i in 5" :key="i" class="w-4 h-4" :class="i <= review.rating ? 'text-amber-400' : 'text-slate-200'" fill="currentColor" viewBox="0 0 20 20">
                  <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <span class="text-sm font-bold text-amber-700 ml-1">{{ review.rating }}/5</span>
              </div>
            </div>
            <p class="mt-3 text-slate-600 text-sm leading-relaxed">{{ review.comment }}</p>
          </div>
        </div>
      </div>

      <!-- ── Tab: Notifications ──────────────────────────── -->
      <div v-else-if="activeTab === 'notifications'">
        <div class="bg-white rounded-2xl shadow-sm border border-slate-100 overflow-hidden">
          <div class="flex items-center justify-between px-6 py-4 border-b border-slate-100">
            <h2 class="text-lg font-bold text-slate-900">All Notifications</h2>
            <button v-if="notifStore.unreadCount" @click="notifStore.markAllRead()"
              class="text-sm font-semibold text-ocean-600 hover:text-ocean-700 transition-colors">Mark all read</button>
          </div>
          <div v-if="notifStore.loading" class="py-10 flex justify-center">
            <div class="animate-spin w-6 h-6 rounded-full border-2 border-ocean-200 border-t-ocean-600"></div>
          </div>
          <div v-else-if="!notifStore.notifications.length" class="py-16 text-center">
            <div class="text-4xl mb-3">🔔</div>
            <p class="text-slate-500 font-medium">No notifications</p>
          </div>
          <ul v-else class="divide-y divide-slate-50">
            <li v-for="n in notifStore.notifications" :key="n.id" @click="notifStore.markRead(n.id)"
              class="px-6 py-4 flex items-start gap-4 cursor-pointer hover:bg-slate-50 transition-colors"
              :class="{ 'bg-ocean-50/30': !n.read_at }">
              <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 text-xl" :class="notifBg(n.type)">
                {{ notifIcon(n.type) }}
              </div>
              <div class="flex-1">
                <p class="font-semibold text-slate-800">{{ n.title }}</p>
                <p class="text-sm text-slate-500 mt-0.5">{{ n.message }}</p>
                <p class="text-xs text-slate-400 mt-1">{{ formatDisplayDate(n.created_at) }}</p>
              </div>
              <span v-if="!n.read_at" class="w-2.5 h-2.5 bg-ocean-500 rounded-full shrink-0 mt-2"></span>
            </li>
          </ul>
        </div>
      </div>

      <!-- ── Tab: Profile Settings ───────────────────────── -->
      <div v-else-if="activeTab === 'profile'">
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          <!-- Profile Form -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Personal Information</h2>
            <form @submit.prevent="saveProfile" class="space-y-5">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Full Name</label>
                <input type="text" v-model="profileForm.name" required
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Email Address</label>
                <input type="email" v-model="profileForm.email" required
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Phone (optional)</label>
                <input type="tel" v-model="profileForm.phone"
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"
                  placeholder="+94 77 000 0000"/>
              </div>
              <div v-if="profileError" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-xl text-sm border border-red-200">{{ profileError }}</div>
              <div v-if="profileSuccess" class="bg-green-50 text-green-700 px-4 py-2.5 rounded-xl text-sm border border-green-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Profile updated successfully!
              </div>
              <button type="submit" :disabled="profileLoading"
                class="w-full bg-gradient-to-r from-ocean-600 to-teal-500 text-white font-bold py-3 rounded-xl hover:from-ocean-700 hover:to-teal-600 disabled:opacity-50 transition-all flex items-center justify-center gap-2">
                <span v-if="profileLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                Save Changes
              </button>
            </form>
          </div>

          <!-- Password Change -->
          <div class="bg-white rounded-2xl shadow-sm border border-slate-100 p-8">
            <h2 class="text-xl font-bold text-slate-900 mb-6">Change Password</h2>
            <form @submit.prevent="savePassword" class="space-y-5">
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Current Password</label>
                <input type="password" v-model="passwordForm.current_password" required
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"
                  placeholder="••••••••"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">New Password</label>
                <input type="password" v-model="passwordForm.password" required minlength="8"
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"
                  placeholder="••••••••"/>
              </div>
              <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1.5">Confirm New Password</label>
                <input type="password" v-model="passwordForm.password_confirmation" required
                  class="w-full border border-slate-300 rounded-xl px-4 py-2.5 text-sm focus:outline-none focus:ring-2 focus:ring-ocean-500 focus:border-ocean-500"
                  :class="{ 'border-red-300': pwMismatch }"
                  placeholder="••••••••"/>
              </div>
              <div v-if="pwMismatch" class="text-red-600 text-xs font-medium">Passwords do not match.</div>
              <div v-if="passwordError" class="bg-red-50 text-red-600 px-4 py-2.5 rounded-xl text-sm border border-red-200">{{ passwordError }}</div>
              <div v-if="passwordSuccess" class="bg-green-50 text-green-700 px-4 py-2.5 rounded-xl text-sm border border-green-200 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                Password changed!
              </div>
              <button type="submit" :disabled="passwordLoading || pwMismatch"
                class="w-full bg-slate-800 hover:bg-slate-900 text-white font-bold py-3 rounded-xl disabled:opacity-50 transition-all flex items-center justify-center gap-2">
                <span v-if="passwordLoading" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
                Update Password
              </button>
            </form>
          </div>
        </div>
      </div>

    </div>

    <!-- Review Modal -->
    <ReviewForm :is-open="isReviewModalOpen" :booking="selectedBooking"
      @close="isReviewModalOpen = false" @success="bookingStore.fetchUserBookings()" />

    <!-- Cancel Booking Modal -->
    <Teleport to="body">
      <Transition enter-active-class="transition-opacity duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100">
        <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center p-4">
          <div class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="showCancelModal = false"></div>
          <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-sm p-7 flex flex-col items-center gap-4">
            <div class="w-14 h-14 rounded-2xl bg-red-50 border-2 border-red-100 flex items-center justify-center">
              <svg class="w-7 h-7 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
              </svg>
            </div>
            <div class="text-center">
              <h3 class="text-lg font-bold text-slate-900">Cancel Booking?</h3>
              <p class="text-sm text-slate-500 mt-1">
                This will cancel your booking at <strong class="text-slate-700">{{ bookingToCancel?.cabana?.name }}</strong>. This cannot be undone.
              </p>
            </div>
            <div class="flex gap-3 w-full pt-1">
              <button @click="showCancelModal = false" class="flex-1 px-4 py-2.5 text-sm font-semibold text-slate-700 bg-white border border-slate-300 rounded-xl hover:bg-slate-50">Keep Booking</button>
              <button @click="executeCancel" :disabled="cancelLoading"
                class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-semibold text-white bg-red-600 rounded-xl hover:bg-red-700 disabled:opacity-60">
                <span v-if="cancelLoading" class="animate-spin w-4 h-4 border-2 border-white border-t-transparent rounded-full"></span>
                Yes, Cancel
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { onMounted, ref, computed, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import { useBookingStore } from '../store/bookingStore';
import { useAuthStore } from '../store/authStore';
import { useNotificationStore } from '../store/notificationStore';
import { useToast } from 'vue-toastification';
import { formatLKR } from '../utils/currency';
import RecommendedForYou from '../components/RecommendedForYou.vue';
import ReviewForm from '../components/ReviewForm.vue';
import api from '../api/axios';

const route        = useRoute();
const router       = useRouter();
const bookingStore = useBookingStore();
const authStore    = useAuthStore();
const notifStore   = useNotificationStore();
const toast        = useToast();

const PLACEHOLDER = 'https://cf.bstatic.com/xdata/images/hotel/max1024x768/654424977.jpg?k=768eca1e486393ded0556fdd3f47e9b0fbd33770a37ac7b4d99cdb53ab3a955b&o=';

// ── Tabs ────────────────────────────────────────────────────────
const tabs = [
  { id: 'bookings',      label: 'My Bookings',    icon: '🏠' },
  { id: 'reviews',       label: 'My Reviews',     icon: '⭐' },
  { id: 'notifications', label: 'Notifications',  icon: '🔔' },
  { id: 'profile',       label: 'Profile',        icon: '👤' },
];
const activeTab = ref(route.query.tab || 'bookings');
watch(() => route.query.tab, (tab) => { if (tab) activeTab.value = tab; });

// ── Reviews ─────────────────────────────────────────────────────
const userReviews  = ref([]);
const reviewsLoading = ref(false);

const fetchReviews = async () => {
  reviewsLoading.value = true;
  try {
    const res = await api.get('/user/reviews');
    userReviews.value = res.data.data ?? res.data ?? [];
  } catch { userReviews.value = []; }
  finally { reviewsLoading.value = false; }
};

watch(activeTab, (tab) => {
  if (tab === 'reviews') fetchReviews();
  if (tab === 'notifications') notifStore.fetchNotifications();
  if (tab === 'profile') initProfile();
});

// ── Review Modal ─────────────────────────────────────────────────
const isReviewModalOpen = ref(false);
const selectedBooking   = ref(null);
const openReviewModal   = (b) => { selectedBooking.value = b; isReviewModalOpen.value = true; };

// ── Cancel Modal ─────────────────────────────────────────────────
const showCancelModal  = ref(false);
const bookingToCancel  = ref(null);
const cancelLoading    = ref(false);

const openCancelModal = (b) => { bookingToCancel.value = b; showCancelModal.value = true; };
const executeCancel = async () => {
  cancelLoading.value = true;
  try {
    await bookingStore.cancelBooking(bookingToCancel.value.id);
    toast.success('Booking cancelled successfully.');
    bookingStore.fetchUserBookings();
  } catch { toast.error('Failed to cancel booking.'); }
  finally { cancelLoading.value = false; showCancelModal.value = false; bookingToCancel.value = null; }
};

// ── Profile ──────────────────────────────────────────────────────
const profileForm    = ref({ name: '', email: '', phone: '' });
const passwordForm   = ref({ current_password: '', password: '', password_confirmation: '' });
const profileLoading = ref(false);
const passwordLoading = ref(false);
const profileError   = ref('');
const profileSuccess = ref(false);
const passwordError  = ref('');
const passwordSuccess = ref(false);

const pwMismatch = computed(() =>
  passwordForm.value.password_confirmation.length > 0 &&
  passwordForm.value.password !== passwordForm.value.password_confirmation
);

const initProfile = () => {
  profileForm.value.name  = authStore.user?.name  || '';
  profileForm.value.email = authStore.user?.email || '';
  profileForm.value.phone = authStore.user?.phone || '';
};

const saveProfile = async () => {
  profileLoading.value = true;
  profileError.value   = '';
  profileSuccess.value = false;
  try {
    await authStore.updateProfile(profileForm.value);
    profileSuccess.value = true;
    toast.success('Profile updated!');
    setTimeout(() => { profileSuccess.value = false; }, 3000);
  } catch (e) {
    profileError.value = e.response?.data?.message || 'Failed to update profile.';
  } finally { profileLoading.value = false; }
};

const savePassword = async () => {
  if (pwMismatch.value) return;
  passwordLoading.value = true;
  passwordError.value   = '';
  passwordSuccess.value = false;
  try {
    await api.put('/user/password', {
      current_password:       passwordForm.value.current_password,
      password:               passwordForm.value.password,
      password_confirmation:  passwordForm.value.password_confirmation,
    });
    passwordSuccess.value = true;
    passwordForm.value    = { current_password: '', password: '', password_confirmation: '' };
    toast.success('Password changed!');
    setTimeout(() => { passwordSuccess.value = false; }, 3000);
  } catch (e) {
    passwordError.value = e.response?.data?.message || 'Failed to change password.';
  } finally { passwordLoading.value = false; }
};

// ── Helpers ──────────────────────────────────────────────────────
const getCabanaImage = (booking) => {
  const img = booking.cabana?.primary_image?.image_path || booking.cabana?.image;
  if (img && img.startsWith('http')) return img;
  return PLACEHOLDER;
};

const formatDisplayDate = (dateStr) => {
  if (!dateStr) return '—';
  const d = new Date(dateStr + (dateStr.includes('T') ? '' : 'T00:00:00'));
  return d.toLocaleDateString('en-LK', { year: 'numeric', month: 'short', day: 'numeric' });
};

const statusClass = (status) => {
  const map = {
    confirmed: 'bg-green-50 text-green-700 border-green-200',
    completed:  'bg-blue-50 text-blue-700 border-blue-200',
    pending:    'bg-amber-50 text-amber-700 border-amber-200',
    cancelled:  'bg-red-50 text-red-700 border-red-200',
  };
  return map[status] || 'bg-slate-100 text-slate-700 border-slate-200';
};

const notifIcon = (type) => {
  const map = { booking_confirmed: '✅', payment_received: '💳', booking_cancelled: '❌', booking_reminder: '⏰', info: '📣', system: '📣' };
  return map[type] ?? '🔔';
};

const notifBg = (type) => {
  const map = { booking_confirmed: 'bg-emerald-50', payment_received: 'bg-blue-50', booking_cancelled: 'bg-red-50', booking_reminder: 'bg-amber-50', info: 'bg-ocean-50', system: 'bg-ocean-50' };
  return map[type] ?? 'bg-slate-50';
};

// ── Lifecycle ─────────────────────────────────────────────────────
onMounted(() => {
  bookingStore.fetchUserBookings();
  notifStore.fetchNotifications();
  if (activeTab.value === 'profile') initProfile();
  if (activeTab.value === 'reviews') fetchReviews();
});
</script>
