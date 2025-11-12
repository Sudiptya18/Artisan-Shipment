<template>
    <div id="layoutSidenav_nav">
        <nav :class="['sb-sidenav', 'accordion', sidebarThemeClass]" id="sidenavAccordion">
            <div class="sb-sidenav-menu">
                <div class="nav">
                    <template v-for="group in groupedNavigations" :key="group.label">
                        <div v-if="group.label" class="sb-sidenav-menu-heading">
                            {{ group.label }}
                        </div>
                        <template v-for="item in group.items" :key="item.key">
                            <template v-if="item.children && item.children.length">
                                <a
                                    class="nav-link collapsed"
                                    href="#"
                                    role="button"
                                    :aria-expanded="isExpanded(item.key)"
                                    :aria-controls="`nav-${item.key}`"
                                    @click.prevent="toggleSection(item.key)"
                                >
                                    <div class="sb-nav-link-icon">
                                        <i :class="item.icon || 'fas fa-circle'"></i>
                                    </div>
                                    {{ item.title }}
                                    <div class="sb-sidenav-collapse-arrow">
                                        <i class="fas fa-angle-down"></i>
                                    </div>
                                </a>
                                <div
                                    :id="`nav-${item.key}`"
                                    class="collapse"
                                    :class="{ show: isExpanded(item.key) }"
                                >
                                    <nav class="sb-sidenav-menu-nested nav">
                                        <RouterLink
                                            v-for="child in item.children"
                                            :key="child.key"
                                            class="nav-link"
                                            :class="{ active: isActive(child.route) }"
                                            :to="{ name: child.route }"
                                        >
                                            {{ child.title }}
                                        </RouterLink>
                                    </nav>
                                </div>
                            </template>
                            <RouterLink
                                v-else
                                class="nav-link"
                                :class="{ active: isActive(item.route) }"
                                :to="{ name: item.route }"
                            >
                                <div class="sb-nav-link-icon">
                                    <i :class="item.icon || 'fas fa-circle'"></i>
                                </div>
                                {{ item.title }}
                            </RouterLink>
                        </template>
                    </template>
                </div>
            </div>
            <div class="sb-sidenav-footer">
                <div class="small">Logged in as:</div>
                Artisan Shipment
            </div>
        </nav>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute } from 'vue-router';

const route = useRoute();
const navigationItems = ref([]);
const expandedSections = reactive({});

const headingMap = [
    { label: 'Core', keys: ['dashboard'] },
    { label: 'Interface', keys: ['layouts', 'products', 'auth', 'errors'] },
    { label: 'Addons', keys: ['charts', 'tables'] },
];

const loadNavigation = async () => {
    try {
        const response = await axios.get('/api/navigations');
        navigationItems.value = response.data.data ?? response.data ?? [];
        initializeExpanded();
    } catch (error) {
        console.error('Failed to load navigation', error);
    }
};

const initializeExpanded = () => {
    navigationItems.value.forEach((item) => {
        if (item.children && item.children.length) {
            expandedSections[item.key] = item.children.some((child) => isActive(child.route));
        }
    });
};

const groupedNavigations = computed(() => {
    const items = navigationItems.value;
    const grouped = headingMap
        .map((group) => ({
            label: group.label,
            items: items.filter((item) => group.keys.includes(item.key)),
        }))
        .filter((group) => group.items.length > 0);

    const ungroupedKeys = headingMap.flatMap((group) => group.keys);
    const remainder = items.filter((item) => !ungroupedKeys.includes(item.key));
    if (remainder.length) {
        grouped.push({ label: null, items: remainder });
    }

    return grouped;
});

const toggleSection = (key) => {
    expandedSections[key] = !expandedSections[key];
};

const isExpanded = (key) => expandedSections[key];

const isActive = (routeName) => route.name === routeName;

watch(
    () => route.name,
    () => initializeExpanded()
);

const sidebarThemeClass = computed(() =>
    route.name === 'layout-sidenav-light' ? 'sb-sidenav-light' : 'sb-sidenav-dark'
);

onMounted(() => {
    loadNavigation();
});
</script>

