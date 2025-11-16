<template>
    <div class="col-lg-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header text-center">
                <img :src="logoUrl" alt="Logo" class="mb-3" style="max-height: 200px;" />
                <h3 class="text-center font-weight-light my-4">Login</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="form-floating mb-3">
                        <input
                            v-model="form.login"
                            class="form-control"
                            id="loginField"
                            type="text"
                            placeholder="Username or Email"
                            :class="{ 'is-invalid': errors.email || errors.username }"
                            required
                            autocomplete="username"
                            @input="clearErrors"
                        />
                        <label for="loginField">Username or Email</label>
                        <div v-if="errors.email" class="invalid-feedback">
                            {{ errors.email.join(', ') }}
                        </div>
                        <div v-if="errors.username" class="invalid-feedback">
                            {{ errors.username.join(', ') }}
                        </div>
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            v-model="form.password"
                            class="form-control"
                            id="loginPassword"
                            type="password"
                            placeholder="Password"
                            :class="{ 'is-invalid': errors.password }"
                            required
                            autocomplete="current-password"
                            @input="clearErrors"
                        />
                        <label for="loginPassword">Password</label>
                        <div v-if="errors.password" class="invalid-feedback">
                            {{ errors.password.join(', ') }}
                        </div>
                    </div>
                    <div class="form-check mb-3">
                        <input
                            v-model="form.remember"
                            class="form-check-input"
                            id="rememberPassword"
                            type="checkbox"
                        />
                        <label class="form-check-label" for="rememberPassword">Remember me</label>
                    </div>
                    <div class="d-flex align-items-center justify-content-center mt-4 mb-0">
                        <button 
                            class="btn-login" 
                            type="submit" 
                            :disabled="isSubmitting"
                            :class="{ 'btn-disabled': isSubmitting }"
                        >
                            <Loader v-if="isSubmitting" class="me-2" />
                            Log in
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import { reactive, ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { login } from '@/stores/auth';
import Swal from 'sweetalert2';
import Loader from '@/components/Loader.vue';

const router = useRouter();

const form = reactive({
    login: '',
    password: '',
    remember: false,
});

const logoUrl = '/assets/img/logo.png';

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

const clearErrors = () => {
    // Clear errors when user starts typing
    Object.keys(errors).forEach((key) => delete errors[key]);
    // Ensure button is always enabled when user is typing
    isSubmitting.value = false;
};

const submit = async () => {
    // Prevent double submission
    if (isSubmitting.value) {
        return;
    }
    
    isSubmitting.value = true;
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        // Determine if login is email or username
        const isEmail = form.login.includes('@');
        const loginData = isEmail 
            ? { email: form.login, password: form.password, remember: form.remember }
            : { username: form.login, password: form.password, remember: form.remember };
        
        await login(loginData);
        
        // Show success toast
        Toast.fire({
            icon: 'success',
            title: 'Signed in successfully'
        });
        
        // Navigate to dashboard after a short delay
        setTimeout(() => {
            router.push({ name: 'dashboard' });
        }, 500);
    } catch (error) {
        // Always reset isSubmitting immediately on error
        isSubmitting.value = false;
        
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            // Show error toast
            Toast.fire({
                icon: 'error',
                title: 'Invalid credentials. Please try again.'
            });
        } else {
            // Show error toast
            Toast.fire({
                icon: 'error',
                title: 'Unable to login at this moment.'
            });
            console.error(error);
        }
    } finally {
        // Double-check: Always reset isSubmitting to ensure button is clickable again
        // Use setTimeout to ensure it happens after any potential async operations
        setTimeout(() => {
            isSubmitting.value = false;
        }, 100);
    }
};

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

