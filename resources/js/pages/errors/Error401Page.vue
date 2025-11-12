<template>
    <div class="col-lg-6">
        <div class="text-center mt-4">
            <h1 class="display-1">401</h1>
            <p class="lead">You do not have access to this resource.</p>
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

