<template>
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <RouterLink class="navbar-brand ps-3 d-flex align-items-center" :to="{ name: 'dashboard' }">
            <img :src="logoUrl" alt="Logo" class="navbar-brand-logo mx-3" />
            <span>Artisan Shipment</span>
        </RouterLink>
        <button
            class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0"
            id="sidebarToggle"
            type="button"
            aria-label="Toggle navigation"
            @click="emit('toggle-sidebar')"
        >
            <i class="fas fa-bars"></i>
        </button>
        <div class="d-none d-md-inline-block ms-auto me-0 me-md-3 my-2 my-md-0">
            <button class="btn btn-link text-white" type="button" title="Notifications" style="background: transparent; border: none;">
                <i class="fas fa-bell"></i>
            </button>
        </div>
        <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
            <li class="nav-item dropdown">
                <a
                    class="nav-link dropdown-toggle"
                    href="#"
                    role="button"
                    id="navbarDropdown"
                    data-bs-toggle="dropdown"
                    aria-expanded="false"
                >
                    <i class="fas fa-user fa-fw me-1"></i>
                    <span class="d-none d-lg-inline">{{ userName }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li class="dropdown-header text-muted">
                        {{ userEmail }}
                    </li>
                    <li v-if="isSuperAdmin">
                        <RouterLink class="dropdown-item" :to="{ name: 'activity-log' }">
                            <i class="fas fa-history me-2"></i>Activity Log
                        </RouterLink>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <button class="dropdown-item" type="button" @click="handleLogout">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>
</template>

<script setup>
import { computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { logout, useAuthStore } from '@/stores/auth';

const emit = defineEmits(['toggle-sidebar']);

const router = useRouter();
const authStore = useAuthStore();
const logoUrl = '/assets/img/logo.png';

const userName = computed(() => {
    if (!authStore.user) {
        return 'Account';
    }

    return authStore.user.name || authStore.user.first_name || 'Account';
});

const userEmail = computed(() => authStore.user?.email ?? 'Not signed in');

const isSuperAdmin = computed(() => {
    return authStore.user?.role_id === 1;
});

const handleLogout = async () => {
    try {
        await logout();
    } catch (error) {
        console.error('Logout error:', error);
    } finally {
        // Reset router's auth bootstrapped state
        if (window.__authBootstrapped !== undefined) {
            window.__authBootstrapped = false;
        }
        router.push({ name: 'login' });
    }
};
</script>

<style scoped>
.navbar-brand-logo {
    height: 35px;
    width: 35px;
    margin-left: 1rem !important;
    margin-right: 1rem !important;
}
</style>

