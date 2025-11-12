<template>
    <div class="col-lg-6">
        <div class="text-center mt-4">
            <img class="mb-4 img-error" :src="errorIllustration" alt="404 Illustration" />
            <p class="lead">The requested URL was not found on this server.</p>
            <RouterLink class="text-decoration-none" :to="{ name: 'dashboard' }">
                <i class="fas fa-arrow-left me-1"></i>
                Return to Dashboard
            </RouterLink>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';

const errorIllustration = new URL('../../../assets/img/error-404-monochrome.svg', import.meta.url).href;
</script>

