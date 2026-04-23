import axios from 'axios';
import { router } from '@inertiajs/vue3';

let csrfInitialized = false;
let isRedirecting = false;

async function ensureCsrfCookie() {
    if (!csrfInitialized) {
        await axios.get('/sanctum/csrf-cookie', { withCredentials: true });
        csrfInitialized = true;
    }
}

const api = axios.create({
    baseURL: '/api',
    withCredentials: true,
    headers: {
        'X-Requested-With': 'XMLHttpRequest',
        Accept: 'application/json',
    },
});

api.interceptors.request.use(async (config) => {
    await ensureCsrfCookie();
    return config;
});

api.interceptors.response.use(
    (response) => response,
    (error) => {
        if (error.response?.status === 401 && !isRedirecting) {
            isRedirecting = true;

            console.warn('Sessao expirada -> redirecionando para login');

            router.visit('/login', {
                replace: true,
                preserveState: false,
            });
        }

        return Promise.reject(error);
    },
);

export default api;
