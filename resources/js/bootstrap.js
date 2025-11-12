import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.baseURL = '/';

// Set CSRF token from meta tag
const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
} else {
    console.error('CSRF token not found: https://laravel.com/docs/csrf#csrf-x-csrf-token');
}

// Intercept requests to ensure CSRF token is set
// Note: For Sanctum SPA mode, we use cookie-based CSRF, not header-based
window.axios.interceptors.request.use(
    (config) => {
        // For Sanctum SPA, don't add X-CSRF-TOKEN header - use cookies only
        // Only add header for non-Sanctum routes if needed
        if (['post', 'put', 'delete', 'patch'].includes(config.method?.toLowerCase())) {
            // Remove X-CSRF-TOKEN header for API routes (Sanctum handles via cookies)
            if (config.url?.startsWith('/api/')) {
                delete config.headers['X-CSRF-TOKEN'];
            }
        }
        return config;
    },
    (error) => {
        return Promise.reject(error);
    }
);
