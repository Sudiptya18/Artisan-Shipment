import { createRouter, createWebHistory } from 'vue-router';
import axios from 'axios';
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
import ProductEditPage from '@/pages/products/ProductEditPage.vue';
import ProductListPage from '@/pages/products/ProductListPage.vue';
import ProductMultipleCreatePage from '@/pages/products/ProductMultipleCreatePage.vue';
import BrandsPage from '@/pages/products/brands/BrandsPage.vue';
import CategoriesPage from '@/pages/products/categories/CategoriesPage.vue';
import FormatsPage from '@/pages/products/formats/FormatsPage.vue';
import HscodePage from '@/pages/products/hscodes/HscodePage.vue';
import OriginsPage from '@/pages/products/origins/OriginsPage.vue';
import PortPage from '@/pages/products/ports/PortPage.vue';
import TitlePage from '@/pages/products/titles/TitlePage.vue';
import GroupPage from '@/pages/products/groups/GroupPage.vue';
import ProductDetailsMultiplePage from '@/pages/products/ProductDetailsMultiplePage.vue';
import UserPagePermissionPage from '@/pages/settings/UserPagePermissionPage.vue';
import UserRegistrationPage from '@/pages/settings/UserRegistrationPage.vue';
import ForgetPasswordPage from '@/pages/settings/ForgetPasswordPage.vue';
import ChangePasswordPage from '@/pages/settings/ChangePasswordPage.vue';
import ContactAdministratorPage from '@/pages/ContactAdministratorPage.vue';
import ActivityLogPage from '@/pages/ActivityLogPage.vue';

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
                    path: 'products/multiple-create',
                    name: 'products-multiple-create',
                    component: ProductMultipleCreatePage,
                    meta: { title: 'Create Multiple Products', requiresAuth: true },
                },
                {
                    path: 'products/edit/:id',
                    name: 'products-edit',
                    component: ProductEditPage,
                    meta: { title: 'Edit Product', requiresAuth: true },
                },
                {
                    path: 'products',
                    name: 'products-list',
                    component: ProductListPage,
                    meta: { title: 'Products', requiresAuth: true },
                },
                {
                    path: 'products/brands',
                    name: 'brands',
                    component: BrandsPage,
                    meta: { title: 'Add Brands', requiresAuth: true },
                },
                {
                    path: 'products/categories',
                    name: 'categories',
                    component: CategoriesPage,
                    meta: { title: 'Add Categories', requiresAuth: true },
                },
                {
                    path: 'products/formats',
                    name: 'formats',
                    component: FormatsPage,
                    meta: { title: 'Add Format', requiresAuth: true },
                },
                {
                    path: 'products/hscodes',
                    name: 'hscodes',
                    component: HscodePage,
                    meta: { title: 'HS Code', requiresAuth: true },
                },
                {
                    path: 'products/origins',
                    name: 'origins',
                    component: OriginsPage,
                    meta: { title: 'Add Country of Origin', requiresAuth: true },
                },
                {
                    path: 'products/ports',
                    name: 'ports',
                    component: PortPage,
                    meta: { title: 'Port', requiresAuth: true },
                },
                {
                    path: 'products/titles',
                    name: 'titles',
                    component: TitlePage,
                    meta: { title: 'Shipment Title', requiresAuth: true },
                },
                {
                    path: 'products/groups',
                    name: 'groups',
                    component: GroupPage,
                    meta: { title: 'Shipment Group', requiresAuth: true },
                },
                {
                    path: 'products/details-multiple',
                    name: 'products-details-multiple',
                    component: ProductDetailsMultiplePage,
                    meta: { title: 'Multiple Product Details', requiresAuth: true },
                },
                {
                    path: 'user-page-permission',
                    name: 'user-page-permission',
                    component: UserPagePermissionPage,
                    meta: { title: 'User Page Permission', requiresAuth: true },
                },
                {
                    path: 'user-registration',
                    name: 'user-registration',
                    component: UserRegistrationPage,
                    meta: { title: 'User Registration', requiresAuth: true },
                },
                {
                    path: 'forget-password',
                    name: 'forget-password',
                    component: ForgetPasswordPage,
                    meta: { title: 'Forget Password', requiresAuth: true },
                },
                {
                    path: 'change-password',
                    name: 'change-password',
                    component: ChangePasswordPage,
                    meta: { title: 'Change Password', requiresAuth: true },
                },
                {
                    path: 'contactadministrator',
                    name: 'contactadministrator',
                    component: ContactAdministratorPage,
                    meta: { title: 'Contact Administrator', requiresAuth: true },
                },
                {
                    path: 'activity-log',
                    name: 'activity-log',
                    component: ActivityLogPage,
                    meta: { title: 'Activity Log', requiresAuth: true },
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

// Store in window for logout to reset
window.__authBootstrapped = false;

router.beforeEach(async (to) => {
    const authStore = useAuthStore();

    // Reset if explicitly set to false (e.g., after logout)
    if (window.__authBootstrapped === false) {
        authBootstrapped = false;
    }

    if (!authBootstrapped && !authStore.isLoaded) {
        await fetchCurrentUser();
        authBootstrapped = true;
        window.__authBootstrapped = true;
    }

    const isAuthenticated = !!authStore.user;

    if (to.meta?.requiresAuth && !isAuthenticated) {
        return { name: 'login' };
    }

    if (to.meta?.guest && isAuthenticated) {
        return { name: 'dashboard' };
    }

    // Check permissions for authenticated users (except dashboard, contactadministrator, activity-log, and error pages)
    // activity-log is handled by ActivityLogController which checks for role_id = 1
    if (isAuthenticated && to.meta?.requiresAuth && to.name !== 'dashboard' && to.name !== 'contactadministrator' && to.name !== 'activity-log' && !to.name?.startsWith('error-')) {
        try {
            const response = await axios.get(`/api/navigations/check-permission/${to.name}`);
            if (!response.data.allowed) {
                return { name: 'error-401' };
            }
        } catch (error) {
            // If permission check fails, deny access
            return { name: 'error-401' };
        }
    }

    // Special check for activity-log: only allow if role_id = 1
    if (isAuthenticated && to.name === 'activity-log') {
        if (authStore.user?.role_id !== 1) {
            return { name: 'error-401' };
        }
    }

    // Check if user has no permissions and redirect to contactadministrator
    if (isAuthenticated && to.name === 'dashboard') {
        try {
            const permissionsResponse = await axios.get(`/api/user-permissions/user/${authStore.user.id}`);
            const permissions = permissionsResponse.data.data ?? [];
            if (permissions.length === 0) {
                return { name: 'contactadministrator' };
            }
        } catch (error) {
            // If check fails, allow dashboard
        }
    }

    return true;
});

router.afterEach((to) => {
    const title = to.meta?.title ? `${to.meta.title} | ${baseTitle}` : baseTitle;
    document.title = title;
});

export default router;

