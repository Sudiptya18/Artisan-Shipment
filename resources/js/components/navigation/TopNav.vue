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
                    <Loader v-if="isLoggingOut" class="me-1" />
                    <i v-else class="fas fa-user fa-fw me-1"></i>
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
                        <button class="dropdown-item" type="button" @click="showLogoutConfirm">
                            <i class="fas fa-sign-out-alt me-2"></i>Logout
                        </button>
                    </li>
                </ul>
            </li>
        </ul>
    </nav>

</template>

<script setup>
import { computed, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import { logout, useAuthStore } from '@/stores/auth';
import Swal from 'sweetalert2';
import Loader from '@/components/Loader.vue';

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

const isLoggingOut = ref(false);

const showLogoutConfirm = () => {
    Swal.fire({
        title: 'Are you sure?',
        text: 'Do you want to logout?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, logout!',
        cancelButtonText: 'Cancel'
    }).then(async (result) => {
        if (result.isConfirmed) {
            isLoggingOut.value = true;
            try {
                await logout();
                
                // Reset router's auth bootstrapped state
                if (window.__authBootstrapped !== undefined) {
                    window.__authBootstrapped = false;
                }
                
                // Show success message and auto-redirect after 2 seconds
                Swal.fire({
                    title: 'Logged out!',
                    text: 'You have been successfully logged out.',
                    icon: 'success',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                }).then(() => {
                    isLoggingOut.value = false;
                    router.push({ name: 'login' });
                });
            } catch (error) {
                console.error('Logout error:', error);
                isLoggingOut.value = false;
                
                // Even if logout failed, try to redirect
                if (window.__authBootstrapped !== undefined) {
                    window.__authBootstrapped = false;
                }
                
                Swal.fire({
                    title: 'Logged out!',
                    text: 'You have been logged out.',
                    icon: 'info',
                    timer: 2000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                }).then(() => {
                    router.push({ name: 'login' });
                });
            }
        }
    });
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

