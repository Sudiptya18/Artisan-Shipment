<template>
    <div>
        <TopNav @toggle-sidebar="toggleSidebar" />
        <div id="layoutSidenav">
            <SidebarNav />
            <div id="layoutSidenav_content">
                <main>
                    <RouterView />
                </main>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { RouterView, useRouter } from 'vue-router';
import { logout, useAuthStore } from '@/stores/auth';

import TopNav from '@/components/navigation/TopNav.vue';
import SidebarNav from '@/components/navigation/SidebarNav.vue';

const router = useRouter();
const authStore = useAuthStore();

const isSidebarCollapsed = ref(false);

const applySidebarClass = (value) => {
    document.body.classList.toggle('sb-sidenav-toggled', value);
};

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

// Auto-logout functionality
let activityCheckInterval = null;
let lastActivityTime = Date.now();

const checkAutoLogout = () => {
    if (!authStore.user) {
        return;
    }

    const rememberMe = localStorage.getItem('rememberMe') === 'true';
    const lastActivity = localStorage.getItem('lastActivity');
    
    if (!lastActivity) {
        return;
    }

    const now = Date.now();
    const timeSinceLastActivity = (now - parseInt(lastActivity)) / 1000 / 60; // minutes

    if (rememberMe) {
        // If remember me is checked, logout after 24 hours (1440 minutes)
        if (timeSinceLastActivity >= 1440) {
            handleAutoLogout();
        }
    } else {
        // If remember me is not checked, logout after 30 minutes
        if (timeSinceLastActivity >= 30) {
            handleAutoLogout();
        }
    }
};

const handleAutoLogout = async () => {
    try {
        await logout();
        if (window.__authBootstrapped !== undefined) {
            window.__authBootstrapped = false;
        }
        router.push({ name: 'login' });
    } catch (error) {
        console.error('Auto logout error:', error);
    }
};

const updateActivity = () => {
    if (authStore.user) {
        localStorage.setItem('lastActivity', Date.now().toString());
    }
};

onMounted(() => {
    document.body.classList.add('sb-nav-fixed');
    const storedPreference = localStorage.getItem('sb|sidebar-toggle');
    isSidebarCollapsed.value = storedPreference === 'true';
    applySidebarClass(isSidebarCollapsed.value);

    // Set up activity tracking
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    events.forEach((event) => {
        document.addEventListener(event, updateActivity, true);
    });

    // Check auto-logout every minute
    activityCheckInterval = setInterval(checkAutoLogout, 60000);
    
    // Initial check
    checkAutoLogout();
});

watch(isSidebarCollapsed, (value) => {
    applySidebarClass(value);
    localStorage.setItem('sb|sidebar-toggle', value ? 'true' : 'false');
});

onBeforeUnmount(() => {
    document.body.classList.remove('sb-nav-fixed');
    document.body.classList.remove('sb-sidenav-toggled');
    
    // Clean up activity tracking
    const events = ['mousedown', 'mousemove', 'keypress', 'scroll', 'touchstart', 'click'];
    events.forEach((event) => {
        document.removeEventListener(event, updateActivity, true);
    });
    
    if (activityCheckInterval) {
        clearInterval(activityCheckInterval);
    }
});
</script>

