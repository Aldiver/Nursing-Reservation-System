import axios from "axios";

const fetchNotifications = async () => {
    try {
        const response = await axios.get("/api/notifications", {
            withCredentials: true,
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching notifications:", error);
        return [];
    }
};

const fetchUnreadNotifications = async () => {
    try {
        const response = await axios.get("/api/notifications-unread", {
            withCredentials: true,
        });
        return response.data;
    } catch (error) {
        console.error("Error fetching unread notifications:", error);
        return [];
    }
};

const markNotificationAsRead = async (id) => {
    try {
        const response = await axios.patch(
            `/api/mark-notification/${id}`,
            {},
            {
                withCredentials: true,
            }
        );
        console.log(`Data response: ${response.data}`);
        return response.data;
    } catch (error) {
        console.error(`Error marking notification ${id} as read:`, error);
    }
};

const markAllNotificationsAsRead = async () => {
    try {
        const response = await axios.patch(
            "/api/mark-notifications",
            {},
            {
                withCredentials: true,
            }
        );
        return response.data;
    } catch (error) {
        console.error("Error marking all notifications as read:", error);
    }
};

const deleteNotification = async (id) => {
    try {
        const response = await axios.delete(`/api/notification/${id}`, {
            withCredentials: true,
        });
        return response.data;
    } catch (error) {
        console.error(`Error deleting notification ${id}:`, error);
    }
};

export default {
    fetchNotifications,
    fetchUnreadNotifications,
    markNotificationAsRead,
    markAllNotificationsAsRead,
    deleteNotification,
};
