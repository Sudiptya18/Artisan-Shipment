<template>
    <RouterView />
</template>

<script setup>
import { onMounted } from 'vue';
import { RouterView } from 'vue-router';
import { fetchCurrentUser } from './stores/auth';

onMounted(() => {
    fetchCurrentUser();
});
</script>

