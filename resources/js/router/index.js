import { createRouter, createWebHistory } from 'vue-router';
import { fetchCurrentUser, useAuthStore } from '@/stores/auth';

import MainLayout from '@/layouts/MainLayout.vue';
import AuthLayout from '@/layouts/AuthLayout.vue';
import ErrorLayout from '@/layouts/ErrorLayout.vue';

import DashboardPage from '@/pages/DashboardPage.vue';
import ChartsPage from '@/pages/ChartsPage.vue';
import TablesPage from '@/pages/TablesPage.vue';
import LayoutStaticPage from '@/pages/LayoutStaticPage.vue';
import LayoutSidenavLightPage from '@/pages/LayoutSidenavLightPage.vue';
import ProductCreatePage from '@/pages/products/ProductCreatePage.vue';
import ProductListPage from '@/pages/products/ProductListPage.vue';
import BrandsPage from '@/pages/brands/BrandsPage.vue';
import CategoriesPage from '@/pages/categories/CategoriesPage.vue';
import FormatsPage from '@/pages/formats/FormatsPage.vue';
import OriginsPage from '@/pages/origins/OriginsPage.vue';

import LoginPage from '@/pages/auth/LoginPage.vue';
import RegisterPage from '@/pages/auth/RegisterPage.vue';
import PasswordPage from '@/pages/auth/PasswordPage.vue';

import Error401Page from '@/pages/errors/Error401Page.vue';
import Error404Page from '@/pages/errors/Error404Page.vue';
import Error500Page from '@/pages/errors/Error500Page.vue';

const baseTitle = 'Artisan Shipment Admin';

const router = createRouter({
    history: createWebHistory(),
    scrollBehavior() {
        return { top: 0 };
    },
    routes: [
        {
            path: '/',
            component: MainLayout,
            children: [
                {
                    path: '',
                    name: 'dashboard',
                    component: DashboardPage,
                    meta: { title: 'Dashboard', requiresAuth: true },
                },
                {
                    path: 'charts',
                    name: 'charts',
                    component: ChartsPage,
                    meta: { title: 'Charts', requiresAuth: true },
                },
                {
                    path: 'tables',
                    name: 'tables',
                    component: TablesPage,
                    meta: { title: 'Tables', requiresAuth: true },
                },
                {
                    path: 'layout-static',
                    name: 'layout-static',
                    component: LayoutStaticPage,
                    meta: { title: 'Static Navigation', requiresAuth: true },
                },
                {
                    path: 'layout-sidenav-light',
                    name: 'layout-sidenav-light',
                    component: LayoutSidenavLightPage,
                    meta: { title: 'Light Sidenav', requiresAuth: true },
                },
                {
                    path: 'products/create',
                    name: 'products-create',
                    component: ProductCreatePage,
                    meta: { title: 'Create Product', requiresAuth: true },
                },
                {
                    path: 'products',
                    name: 'products-list',
                    component: ProductListPage,
                    meta: { title: 'Products', requiresAuth: true },
                },
                {
                    path: 'brands',
                    name: 'brands',
                    component: BrandsPage,
                    meta: { title: 'Add Brands', requiresAuth: true },
                },
                {
                    path: 'categories',
                    name: 'categories',
                    component: CategoriesPage,
                    meta: { title: 'Add Categories', requiresAuth: true },
                },
                {
                    path: 'formats',
                    name: 'formats',
                    component: FormatsPage,
                    meta: { title: 'Add Format', requiresAuth: true },
                },
                {
                    path: 'origins',
                    name: 'origins',
                    component: OriginsPage,
                    meta: { title: 'Add Country of Origin', requiresAuth: true },
                },
            ],
        },
        {
            path: '/auth',
            component: AuthLayout,
            children: [
                {
                    path: 'login',
                    name: 'login',
                    component: LoginPage,
                    meta: { title: 'Login', guest: true },
                },
                {
                    path: 'register',
                    name: 'register',
                    component: RegisterPage,
                    meta: { title: 'Register', guest: true },
                },
                {
                    path: 'password',
                    name: 'password',
                    component: PasswordPage,
                    meta: { title: 'Forgot Password', guest: true },
                },
            ],
        },
        {
            path: '/error',
            component: ErrorLayout,
            children: [
                {
                    path: '401',
                    name: 'error-401',
                    component: Error401Page,
                    meta: { title: '401 Error' },
                },
                {
                    path: '404',
                    name: 'error-404',
                    component: Error404Page,
                    meta: { title: '404 Error' },
                },
                {
                    path: '500',
                    name: 'error-500',
                    component: Error500Page,
                    meta: { title: '500 Error' },
                },
            ],
        },
        {
            path: '/:pathMatch(.*)*',
            redirect: { name: 'error-404' },
        },
    ],
});

let authBootstrapped = false;

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    if (!authBootstrapped && !authStore.isLoaded) {
        await fetchCurrentUser();
        authBootstrapped = true;
    }

    const isAuthenticated = !!authStore.user;

    if (to.meta?.requiresAuth && !isAuthenticated) {
        return { name: 'login' };
    }

    if (to.meta?.guest && isAuthenticated) {
        return { name: 'dashboard' };
    }

    return true;
});

router.afterEach((to) => {
    const title = to.meta?.title ? `${to.meta.title} | ${baseTitle}` : baseTitle;
    document.title = title;
});

export default router;

