import axios from 'axios';
import { reactive } from 'vue';

const state = reactive({
    user: null,
    isLoaded: false,
});

export function useAuthStore() {
    return state;
}

export async function fetchCurrentUser() {
    try {
        const response = await axios.get('/api/auth/me');
        state.user = response.data.data ?? response.data ?? null;
    } catch (error) {
        state.user = null;
    } finally {
        state.isLoaded = true;
    }
}

export async function login(credentials) {
    // Always fetch fresh CSRF cookie before login - critical after logout
    await axios.get('/sanctum/csrf-cookie');
    
    // Make login request - Sanctum uses cookie-based CSRF
    const response = await axios.post('/api/auth/login', credentials);
    
    state.user = response.data.data ?? response.data ?? null;
    state.isLoaded = true;
    
    // Set last activity timestamp for auto-logout
    if (state.user) {
        localStorage.setItem('lastActivity', Date.now().toString());
        localStorage.setItem('rememberMe', credentials.remember ? 'true' : 'false');
    }

    return state.user;
}

export async function logout() {
    // Clear state immediately to prevent multiple clicks
    const wasLoggedIn = !!state.user;
    state.user = null;
    state.isLoaded = false;
    localStorage.removeItem('lastActivity');
    localStorage.removeItem('rememberMe');
    
    if (!wasLoggedIn) {
        return; // Already logged out
    }

    try {
        await axios.get('/sanctum/csrf-cookie');
        await axios.post('/api/auth/logout');
    } catch (error) {
        // Logout request failed, but state is already cleared
        console.error('Logout error:', error);
    }
}

