<template>
    <div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
        <div class="col-lg-5">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header text-center bg-white border-0 pt-4">
                    <img :src="logoUrl" alt="Logo" class="mb-3" style="max-height: 150px;" />
                    <h3 class="text-center font-weight-light my-4">Forget Password</h3>
                </div>
                <div class="card-body px-5 pb-5">
                    <form @submit.prevent="submit">
                        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                            {{ alert.message }}
                        </div>
                        <div class="form-floating mb-3">
                            <select
                                v-model="form.role_id"
                                class="form-select"
                                id="selectRole"
                                :class="{ 'is-invalid': errors.role_id }"
                                @change="onRoleChange"
                                required
                            >
                                <option value="">Select Role</option>
                                <option v-for="role in roles" :key="role.id" :value="role.id">
                                    {{ role.name }}
                                </option>
                            </select>
                            <label for="selectRole">Select Role *</label>
                            <div v-if="errors.role_id" class="invalid-feedback">
                                {{ errors.role_id.join(', ') }}
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <select
                                v-model="form.user_id"
                                class="form-select"
                                id="selectUser"
                                :class="{ 'is-invalid': errors.user_id }"
                                :disabled="!form.role_id || usersByRole.length === 0"
                                required
                            >
                                <option value="">Select User</option>
                                <option v-for="user in usersByRole" :key="user.id" :value="user.id">
                                    {{ user.name }} ({{ user.username }})
                                </option>
                            </select>
                            <label for="selectUser">Select User *</label>
                            <div v-if="errors.user_id" class="invalid-feedback">
                                {{ errors.user_id.join(', ') }}
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input
                                v-model="form.password"
                                class="form-control"
                                id="inputPassword"
                                type="password"
                                placeholder="Enter new password"
                                :class="{ 'is-invalid': errors.password }"
                                required
                                autocomplete="new-password"
                            />
                            <label for="inputPassword">New Password *</label>
                            <div v-if="errors.password" class="invalid-feedback">
                                {{ errors.password.join(', ') }}
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input
                                v-model="form.password_confirmation"
                                class="form-control"
                                id="inputPasswordConfirm"
                                type="password"
                                placeholder="Confirm new password"
                                :class="{ 'is-invalid': errors.password_confirmation }"
                                required
                                autocomplete="new-password"
                            />
                            <label for="inputPasswordConfirm">Confirm Password *</label>
                            <div v-if="errors.password_confirmation" class="invalid-feedback">
                                {{ errors.password_confirmation.join(', ') }}
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            <button class="btn btn-teal text-white w-100" type="submit" :disabled="isSubmitting" style="background-color: #20b2aa; border-color: #20b2aa;">
                                <Loader v-if="isSubmitting" class="me-2" />
                                Reset Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
        v-model:show="showSuccessModal"
        title="Success!"
        message="Password reset successfully."
        @close="handleSuccessClose"
    />
</template>

<script setup>
import axios from 'axios';
import { reactive, ref, computed, onMounted } from 'vue';
import SuccessModal from '@/components/SuccessModal.vue';
import Loader from '@/components/Loader.vue';

const logoUrl = '/assets/img/logo.png';

const form = reactive({
    role_id: '',
    user_id: '',
    password: '',
    password_confirmation: '',
});

const roles = ref([]);
const users = ref([]);
const errors = reactive({});
const isSubmitting = ref(false);
const showSuccessModal = ref(false);
const alert = reactive({
    type: 'success',
    message: '',
});

const usersByRole = computed(() => {
    if (!form.role_id) {
        return [];
    }
    return users.value.filter((user) => user.role_id === parseInt(form.role_id));
});

const fetchRoles = async () => {
    try {
        const response = await axios.get('/api/user-permissions/roles');
        roles.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching roles:', error);
        alert.type = 'danger';
        alert.message = 'Failed to load roles. Please refresh the page.';
    }
};

const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/user-permissions/users');
        users.value = response.data.data || [];
    } catch (error) {
        console.error('Error fetching users:', error);
        alert.type = 'danger';
        alert.message = 'Failed to load users. Please refresh the page.';
    }
};

const onRoleChange = () => {
    form.user_id = '';
};

const submit = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        await axios.post('/api/auth/reset-password', form);
        showSuccessModal.value = true;
        
        // Reset form
        form.role_id = '';
        form.user_id = '';
        form.password = '';
        form.password_confirmation = '';
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the highlighted fields.';
        } else if (error.response?.status === 401) {
            alert.type = 'danger';
            alert.message = 'Unauthorized. Please login again.';
        } else {
            alert.type = 'danger';
            alert.message = error.response?.data?.message || 'Failed to reset password.';
            console.error(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};

const handleSuccessClose = () => {
    showSuccessModal.value = false;
};

onMounted(() => {
    fetchRoles();
    fetchUsers();
});
</script>

<style scoped>
.min-vh-100 {
    min-height: 100vh;
}
</style>
