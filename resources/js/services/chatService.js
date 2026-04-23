import api from '@/services/api';

export default {
    sidebar() {
        return api.get('/chat/sidebar');
    },
    users() {
        return api.get('/chat/users?exclude_self=1');
    },
    adminUsers() {
        return api.get('/chat/admin/users');
    },
    createUser(payload) {
        return api.post('/chat/users', payload);
    },
    rooms() {
        return api.get('/chat/rooms');
    },
    createRoom(payload) {
        return api.post('/chat/rooms', payload);
    },
    roomMessages(roomId) {
        return api.get(`/chat/rooms/${roomId}/messages`);
    },
    sendRoomMessage(roomId, payload) {
        return api.post(`/chat/rooms/${roomId}/messages`, payload, {
            headers: {
                'Content-Type': 'multipart/form-data',
            },
        });
    },
    markRoomRead(roomId) {
        return api.post(`/chat/rooms/${roomId}/read`);
    },
    joinRoom(roomId) {
        return api.post(`/chat/rooms/${roomId}/join`);
    },
    deleteRoom(roomId) {
        return api.delete(`/chat/rooms/${roomId}`);
    },
    directConversations() {
        return api.get('/chat/direct-conversations');
    },
    openDirect(userId) {
        return api.post('/chat/direct-conversations', { user_id: userId });
    },
    directMessages(conversationId) {
        return api.get(`/chat/direct-conversations/${conversationId}/messages`);
    },
    sendDirectMessage(conversationId, payload) {
        return api.post(`/chat/direct-conversations/${conversationId}/messages`, payload);
    },
    markDirectRead(conversationId) {
        return api.post(`/chat/direct-conversations/${conversationId}/read`);
    },
    deleteDirect(conversationId) {
        return api.delete(`/chat/direct-conversations/${conversationId}`);
    },
    pingPresence() {
        return api.post('/chat/presence/ping');
    },
    addMessageReaction(messageId, emoji) {
        return api.post(`/chat/messages/${messageId}/reactions`, { emoji });
    },
    removeMessageReaction(messageId, emoji) {
        return api.delete(`/chat/messages/${messageId}/reactions`, {
            data: { emoji },
        });
    },
};
