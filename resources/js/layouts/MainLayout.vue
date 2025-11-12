<template>
    <div>
        <TopNav @toggle-sidebar="toggleSidebar" />
        <div id="layoutSidenav">
            <SidebarNav />
            <div id="layoutSidenav_content">
                <main>
                    <RouterView />
                </main>
                <FooterBar />
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { RouterView } from 'vue-router';

import TopNav from '@/components/navigation/TopNav.vue';
import SidebarNav from '@/components/navigation/SidebarNav.vue';
import FooterBar from '@/components/navigation/FooterBar.vue';

const isSidebarCollapsed = ref(false);

const applySidebarClass = (value) => {
    document.body.classList.toggle('sb-sidenav-toggled', value);
};

const toggleSidebar = () => {
    isSidebarCollapsed.value = !isSidebarCollapsed.value;
};

onMounted(() => {
    document.body.classList.add('sb-nav-fixed');
    const storedPreference = localStorage.getItem('sb|sidebar-toggle');
    isSidebarCollapsed.value = storedPreference === 'true';
    applySidebarClass(isSidebarCollapsed.value);
});

watch(isSidebarCollapsed, (value) => {
    applySidebarClass(value);
    localStorage.setItem('sb|sidebar-toggle', value ? 'true' : 'false');
});

onBeforeUnmount(() => {
    document.body.classList.remove('sb-nav-fixed');
    document.body.classList.remove('sb-sidenav-toggled');
});
</script>

