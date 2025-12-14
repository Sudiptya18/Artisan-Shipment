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
    // CSRF not required for API routes - using Sanctum token-based auth
    const response = await axios.post('/api/auth/login', credentials, {
        withCredentials: true,
    });
    
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
    // Don't clear state immediately - wait for successful logout
    const wasLoggedIn = !!state.user;
    
    if (!wasLoggedIn) {
        return; // Already logged out
    }

    try {
        // CSRF not required for API routes - using Sanctum token-based auth
        // Make logout request
        await axios.post('/api/auth/logout', {}, {
            withCredentials: true,
        });
        
        // Only clear state after successful logout
        state.user = null;
        state.isLoaded = false;
        localStorage.removeItem('lastActivity');
        localStorage.removeItem('rememberMe');
    } catch (error) {
        // Even if logout request fails, clear state to prevent stuck sessions
        console.error('Logout error:', error);
        state.user = null;
        state.isLoaded = false;
        localStorage.removeItem('lastActivity');
        localStorage.removeItem('rememberMe');
        throw error; // Re-throw to allow caller to handle
    }
}

