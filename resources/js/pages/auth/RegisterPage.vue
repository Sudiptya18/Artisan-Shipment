<template>
    <div class="col-lg-7">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header text-center">
                <img :src="logoUrl" alt="Logo" class="mb-3" style="max-height: 200px;" />
                <h3 class="text-center font-weight-light my-4">Create Account</h3>
            </div>
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                        {{ alert.message }}
                    </div>
                    <div class="form-floating mb-3">
                        <input
                            v-model="form.name"
                            class="form-control"
                            id="inputName"
                            type="text"
                            placeholder="Enter your full name"
                            :class="{ 'is-invalid': errors.name }"
                            required
                            autocomplete="name"
                        />
                        <label for="inputName">Full Name *</label>
                        <div v-if="errors.name" class="invalid-feedback">
                            {{ errors.name.join(', ') }}
                        </div>
                    </div>
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
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <div class="form-floating mb-3 mb-md-0">
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
                            <div class="form-floating mb-3 mb-md-0">
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
                    <div class="mt-4 mb-0">
                        <div class="d-grid">
                            <button class="btn btn-primary btn-block" type="submit" :disabled="isSubmitting">
                                <span
                                    v-if="isSubmitting"
                                    class="spinner-border spinner-border-sm me-2"
                                    role="status"
                                ></span>
                                Create Account
                            </button>
                        </div>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <RouterLink :to="{ name: 'login' }">Have an account? Go to login</RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { reactive, ref } from 'vue';
import { useRouter, RouterLink } from 'vue-router';
import { fetchCurrentUser } from '@/stores/auth';

const router = useRouter();

const form = reactive({
    name: '',
    username: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
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
        await axios.get('/sanctum/csrf-cookie');
        await axios.post('/api/auth/register', form);
        
        await fetchCurrentUser();
        
        alert.type = 'success';
        alert.message = 'Account created successfully!';
        
        setTimeout(() => {
            router.push({ name: 'dashboard' });
        }, 1000);
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the errors below.';
        } else {
            alert.type = 'danger';
            alert.message = error.response?.data?.message || 'Unable to create account at this moment.';
            console.error(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};
</script>

