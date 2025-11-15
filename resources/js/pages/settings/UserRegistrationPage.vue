<template>
    <div class="container-fluid px-4">
        <h1 class="mt-4">User Registration</h1>

        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-user-plus me-1"></i>
                Create New User Account
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                        {{ alert.message }}
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input
                                    v-model="form.name"
                                    class="form-control"
                                    id="inputName"
                                    type="text"
                                    placeholder="Enter full name"
                                    :class="{ 'is-invalid': errors.name }"
                                    required
                                    autocomplete="name"
                                />
                                <label for="inputName">Full Name *</label>
                                <div v-if="errors.name" class="invalid-feedback">
                                    {{ errors.name.join(', ') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input
                                    v-model="form.username"
                                    class="form-control"
                                    id="inputUsername"
                                    type="text"
                                    placeholder="username"
                                    :class="{ 'is-invalid': errors.username }"
                                    required
                                    autocomplete="username"
                                />
                                <label for="inputUsername">Username *</label>
                                <div v-if="errors.username" class="invalid-feedback">
                                    {{ errors.username.join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input
                                    v-model="form.email"
                                    class="form-control"
                                    id="inputEmail"
                                    type="email"
                                    placeholder="name@example.com"
                                    :class="{ 'is-invalid': errors.email }"
                                    required
                                    autocomplete="email"
                                />
                                <label for="inputEmail">Email address *</label>
                                <div v-if="errors.email" class="invalid-feedback">
                                    {{ errors.email.join(', ') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input
                                    v-model="form.phone"
                                    class="form-control"
                                    id="inputPhone"
                                    type="tel"
                                    placeholder="Phone number"
                                    :class="{ 'is-invalid': errors.phone }"
                                    autocomplete="tel"
                                />
                                <label for="inputPhone">Phone number</label>
                                <div v-if="errors.phone" class="invalid-feedback">
                                    {{ errors.phone.join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input
                                    v-model="form.password"
                                    class="form-control"
                                    id="inputPassword"
                                    type="password"
                                    placeholder="Create a password"
                                    :class="{ 'is-invalid': errors.password }"
                                    required
                                    autocomplete="new-password"
                                />
                                <label for="inputPassword">Password *</label>
                                <div v-if="errors.password" class="invalid-feedback">
                                    {{ errors.password.join(', ') }}
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-floating mb-3">
                                <input
                                    v-model="form.password_confirmation"
                                    class="form-control"
                                    id="inputPasswordConfirm"
                                    type="password"
                                    placeholder="Confirm password"
                                    :class="{ 'is-invalid': errors.password_confirmation }"
                                    required
                                    autocomplete="new-password"
                                />
                                <label for="inputPasswordConfirm">Confirm Password *</label>
                                <div v-if="errors.password_confirmation" class="invalid-feedback">
                                    {{ errors.password_confirmation.join(', ') }}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end">
                        <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
                            <Loader v-if="isSubmitting" class="me-2" />
                            Create Account
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Users List Card -->
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-users me-1"></i>
                Users List
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead>
                            <tr>
                                <th class="text-center">Sl No.</th>
                                <th class="text-center">Name</th>
                                <th class="text-center">Phone</th>
                                <th class="text-center">Email</th>
                                <th class="text-center">Roles</th>
                                <th class="text-center">Designation</th>
                                <th class="text-center">Activity Performed</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="(user, index) in users" :key="user.id">
                                <td class="text-center">{{ index + 1 }}</td>
                                <td class="text-center">{{ user.name || '-' }}</td>
                                <td class="text-center">{{ user.phone || '-' }}</td>
                                <td class="text-center">{{ user.email || '-' }}</td>
                                <td class="text-center">{{ user.role?.role_name || '-' }}</td>
                                <td class="text-center">{{ user.designation_id || 'Blank' }}</td>
                                <td class="text-center">{{ user.activity_logs_count || 0 }}</td>
                            </tr>
                            <tr v-if="users.length === 0">
                                <td colspan="7" class="text-center">No users found.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Success/Failed Modal -->
        <div
            v-if="showModal"
            class="modal fade show"
            :class="{ 'd-block': showModal }"
            tabindex="-1"
            role="dialog"
            style="background-color: rgba(0, 0, 0, 0.5);"
        >
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-body text-center p-5">
                        <div v-if="modalSuccess" class="mb-3">
                            <div class="success-icon">
                                <i class="fas fa-check-circle text-success" style="font-size: 4rem;"></i>
                            </div>
                        </div>
                        <div v-else class="mb-3">
                            <div class="error-icon">
                                <i class="fas fa-times-circle text-danger" style="font-size: 4rem;"></i>
                            </div>
                        </div>
                        <h4 :class="modalSuccess ? 'text-success' : 'text-danger'">
                            {{ modalMessage }}
                        </h4>
                        <button
                            type="button"
                            class="btn btn-primary mt-4"
                            @click="closeModal"
                        >
                            OK
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import { fetchCurrentUser } from '@/stores/auth';
import Loader from '@/components/Loader.vue';

const router = useRouter();

const users = ref([]);

const form = reactive({
    name: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const errors = reactive({});
const isSubmitting = ref(false);
const showModal = ref(false);
const modalSuccess = ref(false);
const modalMessage = ref('');
const alert = reactive({
    type: 'danger',
    message: '',
});

const submit = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        await axios.get('/sanctum/csrf-cookie');
        await axios.post('/api/auth/register', form);
        
        await fetchCurrentUser();
        
        modalSuccess.value = true;
        modalMessage.value = 'Account created successfully!';
        showModal.value = true;

        // Auto close after 5 seconds or redirect on OK
        setTimeout(() => {
            closeModal();
        }, 5000);
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
        }

        modalSuccess.value = false;
        modalMessage.value =
            error.response?.data?.message || 'Unable to create account at this moment.';
        showModal.value = true;

        setTimeout(() => {
            closeModal();
        }, 5000);
    } finally {
        isSubmitting.value = false;
    }
};

const closeModal = () => {
    showModal.value = false;
    if (modalSuccess.value) {
        fetchUsers(); // Refresh users list
        router.push({ name: 'user-page-permission' });
    }
};

const fetchUsers = async () => {
    try {
        const response = await axios.get('/api/user-permissions/users');
        users.value = response.data.data ?? [];
    } catch (error) {
        console.error('Error fetching users:', error);
    }
};

onMounted(() => {
    fetchUsers();
});
</script>

<style scoped>
.success-icon,
.error-icon {
    animation: scaleIn 0.5s ease-out;
}

@keyframes scaleIn {
    from {
        transform: scale(0);
        opacity: 0;
    }
    to {
        transform: scale(1);
        opacity: 1;
    }
}

.modal.show {
    display: block;
}
</style>

