<template>
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
        <RouterLink class="navbar-brand ps-3" :to="{ name: 'dashboard' }">Artisan Shipment</RouterLink>
        <button
            class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0"
            id="sidebarToggle"
            type="button"
            aria-label="Toggle navigation"
            @click="emit('toggle-sidebar')"
        >
            <i class="fas fa-bars"></i>
        </button>
        <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0" role="search">
            <div class="input-group">
                <input class="form-control" type="search" placeholder="Search for..." aria-label="Search" />
                <button class="btn btn-primary" type="button">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </form>
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
                    <li>
                        <RouterLink class="dropdown-item" :to="{ name: 'dashboard' }">Dashboard</RouterLink>
                    </li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li>
                        <button class="dropdown-item" type="button" @click="handleLogout">
                            Logout
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

const userName = computed(() => {
    if (!authStore.user) {
        return 'Account';
    }

    return authStore.user.name || authStore.user.first_name || 'Account';
});

const userEmail = computed(() => authStore.user?.email ?? 'Not signed in');

const handleLogout = async () => {
    await logout();
    router.push({ name: 'login' });
};
</script>

