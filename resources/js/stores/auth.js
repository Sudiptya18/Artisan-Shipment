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
    await axios.get('/sanctum/csrf-cookie');
    const response = await axios.post('/api/auth/login', credentials);
    state.user = response.data.data ?? response.data ?? null;
    state.isLoaded = true;

    return state.user;
}

export async function logout() {
    try {
        await axios.post('/api/auth/logout');
    } finally {
        state.user = null;
    }
}

