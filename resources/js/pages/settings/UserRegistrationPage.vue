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
                    <div class="d-flex justify-content-center">
                        <button 
                            class="btn-login" 
                            type="submit" 
                            :disabled="isSubmitting"
                            :class="{ 'btn-disabled': isSubmitting }"
                        >
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

    </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import axios from 'axios';
import Swal from 'sweetalert2';
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
const alert = reactive({
    type: 'danger',
    message: '',
});

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

const submit = async () => {
    // Prevent double submission
    if (isSubmitting.value) {
        return;
    }
    
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        await axios.get('/sanctum/csrf-cookie');
        await axios.post('/api/auth/register', form);
        
        // Show success toast
        Toast.fire({
            icon: 'success',
            title: 'Account created successfully!'
        });
        
        // Reset form
        form.name = '';
        form.username = '';
        form.email = '';
        form.phone = '';
        form.password = '';
        form.password_confirmation = '';
        
        // Refresh users list
        fetchUsers();
        
        // Redirect to page permission after a short delay
        setTimeout(() => {
            router.push({ name: 'user-page-permission' });
        }, 1000);
    } catch (error) {
        // Always reset isSubmitting immediately on error
        isSubmitting.value = false;
        
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
        }

        // Show error toast
        Toast.fire({
            icon: 'error',
            title: error.response?.data?.message || 'Unable to create account at this moment.'
        });
    } finally {
        // Double-check: Always reset isSubmitting to ensure button is clickable again
        // Use setTimeout to ensure it happens after any potential async operations
        setTimeout(() => {
            isSubmitting.value = false;
        }, 100);
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
.btn-login {
    background-color: #20b2aa; /* Teal color */
    color: white;
    border: none;
    border-radius: 50px; /* Pill shape */
    padding: 10px 40px;
    font-size: 1rem;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
    box-shadow: 0 2px 8px rgba(32, 178, 170, 0.3);
    min-width: 120px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.btn-login:hover:not(.btn-disabled) {
    background-color: #1a9b94;
    box-shadow: 0 4px 12px rgba(32, 178, 170, 0.4);
    transform: translateY(-1px);
}

.btn-login:active:not(.btn-disabled) {
    transform: translateY(0);
    box-shadow: 0 2px 6px rgba(32, 178, 170, 0.3);
}

.btn-login.btn-disabled,
.btn-login:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    transform: none;
}

.btn-login.btn-disabled:hover,
.btn-login:disabled:hover {
    background-color: #20b2aa;
    box-shadow: 0 2px 8px rgba(32, 178, 170, 0.3);
    transform: none;
}
</style>

