import { defineStore } from 'pinia';
import api from '../api/axios';

export const useNotificationStore = defineStore('notification', {
    state: () => ({
        notifications: [],
        unreadCount: 0,
        loading: false,
        error: null,
        pollingInterval: null,
    }),

    actions: {
        async fetchNotifications() {
            this.loading = true;
            try {
                const response = await api.get('/notifications');
                // Extracting the paginated data array
                const rawNotifications = response.data.data.notifications.data;
                
                // Mapping to flatten the data structure for the UI
                this.notifications = rawNotifications.map(n => ({
                    id: n.id,
                    title: n.data?.title || 'Notification',
                    message: n.data?.message || '',
                    type: n.data?.type || 'info',
                    read_at: n.read_at,
                    created_at: n.created_at,
                    link: n.data?.booking_id ? `/dashboard/bookings/${n.data.booking_id}` : null
                }));
                
                this.unreadCount = response.data.data.unread_count;
            } catch (err) {
                console.error('Failed to fetch notifications:', err);
                this.error = 'Could not load notifications.';
            } finally {
                this.loading = false;
            }
        },

        async markAsRead(id) {
            try {
                await api.post(`/notifications/${id}/read`);
                // Update local state
                const index = this.notifications.findIndex(n => n.id === id);
                if (index !== -1 && !this.notifications[index].read_at) {
                    this.notifications[index].read_at = new Date().toISOString();
                    this.unreadCount = Math.max(0, this.unreadCount - 1);
                }
            } catch (err) {
                console.error('Failed to mark notification as read:', err);
            }
        },

        async markAllAsRead() {
            try {
                await api.post('/notifications/mark-all-read');
                this.notifications.forEach(n => {
                    if (!n.read_at) n.read_at = new Date().toISOString();
                });
                this.unreadCount = 0;
            } catch (err) {
                console.error('Failed to mark all notifications as read:', err);
            }
        },

        startPolling(ms = 60000) {
            if (this.pollingInterval) return;
            this.pollingInterval = setInterval(() => {
                this.fetchNotifications();
            }, ms);
        },

        stopPolling() {
            if (this.pollingInterval) {
                clearInterval(this.pollingInterval);
                this.pollingInterval = null;
            }
        }
    }
});
