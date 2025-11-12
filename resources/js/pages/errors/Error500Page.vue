<template>
    <div class="col-lg-6">
        <div class="text-center mt-4">
            <h1 class="display-1">500</h1>
            <p class="lead">Something went wrong on our end. Please try again later.</p>
            <RouterLink class="text-decoration-none" :to="{ name: 'dashboard' }">
                <i class="fas fa-arrow-left me-1"></i>
                Return to Dashboard
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

