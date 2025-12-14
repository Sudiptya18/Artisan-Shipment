import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.axios.defaults.withCredentials = true;
window.axios.defaults.baseURL = '/';

// CSRF protection disabled for API routes - using Sanctum token-based authentication
// No CSRF token handling needed for API endpoints
