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
                                        <template v-for="child in item.children" :key="child.key">
                                            <RouterLink
                                                v-if="child.route && routeExists(child.route)"
                                                class="nav-link"
                                                :class="{ active: isActive(child.route) }"
                                                :to="{ name: child.route }"
                                            >
                                                {{ child.title }}
                                            </RouterLink>
                                        </template>
                                    </nav>
                                </div>
                            </template>
                            <RouterLink
                                v-else-if="item.route && routeExists(item.route)"
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
                <div class="small">All rights reserved.</div>
                <div class="small">© {{ currentYear }} Artisan Shipment</div>
            </div>
        </nav>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, onMounted, reactive, ref, watch } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';

const currentYear = computed(() => new Date().getFullYear());

const route = useRoute();
const router = useRouter();
const navigationItems = ref([]);
const expandedSections = reactive({});

const headingMap = [
    { label: 'Core', keys: ['dashboard', 'settings'] },
    { label: 'Interface', keys: ['layouts', 'products', 'auth', 'errors'] },
    { label: 'Addons', keys: ['charts', 'tables'] },
];

// Check if a route exists
const routeExists = (routeName) => {
    if (!routeName) return false;
    try {
        router.resolve({ name: routeName });
        return true;
    } catch {
        return false;
    }
};

// Filter navigation items to only include valid routes
const filterValidRoutes = (items) => {
    return items.map(item => {
        const filtered = { ...item };
        
        // Check if main route exists
        if (item.route && !routeExists(item.route)) {
            filtered.route = null; // Mark as invalid
        }
        
        // Filter children to only include valid routes
        if (item.children && item.children.length) {
            filtered.children = item.children.filter(child => {
                if (!child.route) return false;
                return routeExists(child.route);
            });
        }
        
        return filtered;
    }).filter(item => {
        // Remove items with no valid route and no valid children
        if (item.route === null && (!item.children || item.children.length === 0)) {
            return false;
        }
        return true;
    });
};

const loadNavigation = async () => {
    try {
        const response = await axios.get('/api/navigations');
        const rawItems = response.data.data ?? response.data ?? [];
        navigationItems.value = filterValidRoutes(rawItems);
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
    const wasExpanded = expandedSections[key];
    
    // Close all other sections (accordion behavior - only one open at a time)
    Object.keys(expandedSections).forEach((sectionKey) => {
        expandedSections[sectionKey] = false;
    });
    
    // Toggle the clicked section (open if it was closed, close if it was open)
    expandedSections[key] = !wasExpanded;
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

