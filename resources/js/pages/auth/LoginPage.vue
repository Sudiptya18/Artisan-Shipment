<template>
    <div class="col-lg-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header text-center">
                <img :src="logoUrl" alt="Logo" class="mb-3" style="max-height: 200px;" />
                <h3 class="text-center font-weight-light my-4">Login</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                        {{ alert.message }}
                    </div>
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
                    <div class="d-flex align-items-center justify-content-end mt-4 mb-0">
                        <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
                            <span
                                v-if="isSubmitting"
                                class="spinner-border spinner-border-sm me-2"
                                role="status"
                            ></span>
                            Login
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

const router = useRouter();

const form = reactive({
    login: '',
    password: '',
    remember: false,
});

const logoUrl = '/assets/img/logo.png';

const errors = reactive({});
const isSubmitting = ref(false);
const alert = reactive({
    type: 'danger',
    message: '',
});

const submit = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        // Determine if login is email or username
        const isEmail = form.login.includes('@');
        const loginData = isEmail 
            ? { email: form.login, password: form.password, remember: form.remember }
            : { username: form.login, password: form.password, remember: form.remember };
        
        await login(loginData);
        alert.type = 'success';
        alert.message = 'Logged in successfully.';
        router.push({ name: 'dashboard' });
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Invalid credentials. Please try again.';
        } else {
            alert.type = 'danger';
            alert.message = 'Unable to login at this moment.';
            console.error(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

