<template>
    <div class="col-lg-5">
        <div class="card shadow-lg border-0 rounded-lg mt-5">
            <div class="card-header">
                <h3 class="text-center font-weight-light my-4">Password Recovery</h3>
            </div>
            <div class="card-body">
                <div class="small mb-3 text-muted">
                    Enter your email address and we will send you a link to reset your password.
                </div>
                <form>
                    <div class="form-floating mb-3">
                        <input class="form-control" id="resetEmail" type="email" placeholder="name@example.com" />
                        <label for="resetEmail">Email address</label>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-4 mb-0">
                        <RouterLink class="small" :to="{ name: 'login' }">Return to login</RouterLink>
                        <RouterLink class="btn btn-primary" :to="{ name: 'dashboard' }">Reset Password</RouterLink>
                    </div>
                </form>
            </div>
            <div class="card-footer text-center py-3">
                <div class="small">
                    <RouterLink :to="{ name: 'register' }">Need an account? Sign up!</RouterLink>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

