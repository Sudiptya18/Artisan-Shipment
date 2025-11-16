<template>
    <div class="container-fluid px-4">
        <h1 class="mt-4">User Page Permission</h1>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-shield me-1"></i>
                Set User Page Permissions
            </div>
            <div class="card-body">
                <form @submit.prevent="savePermissions">
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label for="userId" class="form-label">Select User *</label>
                            <select
                                id="userId"
                                v-model="form.user_id"
                                class="form-select"
                                :class="{ 'is-invalid': errors.user_id }"
                                required
                                @change="loadUserPermissions"
                            >
                                <option value="">-- Select User --</option>
                                <option v-for="user in users" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.username }})
                                </option>
                            </select>
                            <div v-if="errors.user_id" class="invalid-feedback">
                                {{ errors.user_id.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label for="roleId" class="form-label">Select Role *</label>
                            <select
                                id="roleId"
                                v-model="form.role_id"
                                class="form-select"
                                :class="{ 'is-invalid': errors.role_id }"
                                required
                            >
                                <option value="">-- Select Role --</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.role_name }}
                                </option>
                            </select>
                            <div v-if="errors.role_id" class="invalid-feedback">
                                {{ errors.role_id.join(', ') }}
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Select Page Permissions *</label>
                        <div class="border rounded p-3" style="max-height: 400px; overflow-y: auto;">
                            <div class="form-check mb-3 pb-2 border-bottom">
                                <input
                                    id="selectAll"
                                    :checked="isAllSelected"
                                    class="form-check-input"
                                    type="checkbox"
                                    @change="handleSelectAll"
                                />
                                <label for="selectAll" class="form-check-label fw-bold">
                                    <i class="fas fa-check-double"></i> Select All
                                </label>
                            </div>
                            <div v-for="nav in navigations" :key="nav.id" class="mb-3">
                                <div class="form-check">
                                    <input
                                        :id="`nav-${nav.id}`"
                                        :checked="isParentSelected(nav.id)"
                                        class="form-check-input"
                                        type="checkbox"
                                        @change="handleParentChange(nav, $event)"
                                    />
                                    <label :for="`nav-${nav.id}`" class="form-check-label fw-bold">
                                        <i :class="nav.icon" v-if="nav.icon"></i> {{ nav.title }}
                                    </label>
                                </div>
                                <div v-if="nav.children && nav.children.length > 0" class="ms-4 mt-2">
                                    <div
                                        v-for="child in nav.children"
                                        :key="child.id"
                                        class="form-check mb-2"
                                    >
                                        <input
                                            :id="`nav-${child.id}`"
                                            :checked="selectedNavigations.includes(child.id)"
                                            class="form-check-input"
                                            type="checkbox"
                                            @change="handleChildChange(nav, child, $event)"
                                        />
                                        <label :for="`nav-${child.id}`" class="form-check-label">
                                            <i :class="child.icon" v-if="child.icon"></i> {{ child.title }}
                                        </label>
                                    </div>
                                </div>
                            </div>
                            <div v-if="navigations.length === 0" class="text-muted">
                                No navigations available.
                            </div>
                        </div>
                        <div v-if="errors.navigation_ids" class="invalid-feedback d-block">
                            {{ errors.navigation_ids.join(', ') }}
                        </div>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary" :disabled="isSubmitting">
                            <Loader v-if="isSubmitting" class="me-2" />
                            Save Permissions
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { RouterLink } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
import Loader from '@/components/Loader.vue';

const users = ref([]);
const roles = ref([]);
const navigations = ref([]);
const selectedNavigations = ref([]);

const form = reactive({
    user_id: '',
    role_id: '',
});

const errors = reactive({});
const isSubmitting = ref(false);

// SweetAlert2 Toast Mixin
const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.onmouseenter = Swal.stopTimer;
        toast.onmouseleave = Swal.resumeTimer;
    }
});

const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/user-permissions/users');
        users.value = response.data.data;
    } catch (error) {
        console.error('Error fetching users:', error);
    }
};

const fetchRoles = async () => {
    try {
        const response = await axios.get('/api/user-permissions/roles');
        roles.value = response.data.data;
    } catch (error) {
        console.error('Error fetching roles:', error);
    }
};

const fetchNavigations = async () => {
    try {
        const response = await axios.get('/api/user-permissions/navigations');
        navigations.value = response.data.data;
    } catch (error) {
        console.error('Error fetching navigations:', error);
    }
};

const loadUserPermissions = async () => {
    if (!form.user_id) {
        selectedNavigations.value = [];
        form.role_id = '';
        return;
    }

    try {
        const response = await axios.get(`/api/user-permissions/user/${form.user_id}`);
        const permissions = response.data.data;

        // Set selected navigations
        const permissionIds = permissions.map((p) => p.navigation_id);
        selectedNavigations.value = [...permissionIds];

        // Ensure parents are selected if any child is selected
        navigations.value.forEach((nav) => {
            if (nav.children && nav.children.length > 0) {
                const hasSelectedChild = nav.children.some((child) =>
                    permissionIds.includes(child.id)
                );
                if (hasSelectedChild && !permissionIds.includes(nav.id)) {
                    selectedNavigations.value.push(nav.id);
                }
            }
        });

        // Set role if permissions exist
        if (permissions.length > 0 && permissions[0].role_id) {
            form.role_id = permissions[0].role_id;
        }
    } catch (error) {
        console.error('Error loading user permissions:', error);
        selectedNavigations.value = [];
    }
};

const isParentSelected = (parentId) => {
    const parent = navigations.value.find((nav) => nav.id === parentId);
    if (!parent || !parent.children || parent.children.length === 0) {
        return selectedNavigations.value.includes(parentId);
    }
    // Parent is selected if it's directly selected OR all children are selected
    return (
        selectedNavigations.value.includes(parentId) ||
        parent.children.every((child) => selectedNavigations.value.includes(child.id))
    );
};

const handleParentChange = (parent, event) => {
    const isChecked = event.target.checked;

    if (isChecked) {
        // Add parent and all children
        if (!selectedNavigations.value.includes(parent.id)) {
            selectedNavigations.value.push(parent.id);
        }
        if (parent.children && parent.children.length > 0) {
            parent.children.forEach((child) => {
                if (!selectedNavigations.value.includes(child.id)) {
                    selectedNavigations.value.push(child.id);
                }
            });
        }
    } else {
        // Remove parent and all children
        selectedNavigations.value = selectedNavigations.value.filter(
            (id) => id !== parent.id && !parent.children?.some((child) => child.id === id)
        );
    }
};

const handleChildChange = (parent, child, event) => {
    const isChecked = event.target.checked;

    if (isChecked) {
        // Add child
        if (!selectedNavigations.value.includes(child.id)) {
            selectedNavigations.value.push(child.id);
        }
        // Add parent if not already selected
        if (!selectedNavigations.value.includes(parent.id)) {
            selectedNavigations.value.push(parent.id);
        }
    } else {
        // Remove child
        selectedNavigations.value = selectedNavigations.value.filter((id) => id !== child.id);

        // Check if all children are deselected, then remove parent
        if (parent.children && parent.children.length > 0) {
            const allChildrenDeselected = parent.children.every(
                (c) => !selectedNavigations.value.includes(c.id)
            );
            if (allChildrenDeselected) {
                selectedNavigations.value = selectedNavigations.value.filter(
                    (id) => id !== parent.id
                );
            }
        }
    }
};

const getAllNavigationIds = () => {
    const allIds = [];
    navigations.value.forEach((nav) => {
        allIds.push(nav.id);
        if (nav.children && nav.children.length > 0) {
            nav.children.forEach((child) => {
                allIds.push(child.id);
            });
        }
    });
    return allIds;
};

const isAllSelected = computed(() => {
    const allIds = getAllNavigationIds();
    if (allIds.length === 0) return false;
    return allIds.every((id) => selectedNavigations.value.includes(id));
});

const handleSelectAll = (event) => {
    const isChecked = event.target.checked;
    const allIds = getAllNavigationIds();

    if (isChecked) {
        // Select all
        allIds.forEach((id) => {
            if (!selectedNavigations.value.includes(id)) {
                selectedNavigations.value.push(id);
            }
        });
    } else {
        // Deselect all
        selectedNavigations.value = [];
    }
};

const savePermissions = async () => {
    isSubmitting.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        // Fetch CSRF cookie before making POST request
        await axios.get('/sanctum/csrf-cookie', {
            withCredentials: true,
        });

        const payload = {
            user_id: form.user_id,
            role_id: form.role_id,
            navigation_ids: selectedNavigations.value,
        };

        const response = await axios.post('/api/user-permissions/set', payload, {
            withCredentials: true,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
            },
        });

        if (response.data.success) {
            // Show success toast
            Toast.fire({
                icon: 'success',
                title: 'Permissions set successfully!'
            });
        } else {
            // Show error toast
            Toast.fire({
                icon: 'error',
                title: response.data.message || 'Failed to set permissions.'
            });
        }
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
        }

        // Show error toast
        Toast.fire({
            icon: 'error',
            title: error.response?.data?.message || 'An error occurred while setting permissions.'
        });
    } finally {
        isSubmitting.value = false;
    }
};

onMounted(() => {
    fetchUsers();
    fetchRoles();
    fetchNavigations();
});
</script>


