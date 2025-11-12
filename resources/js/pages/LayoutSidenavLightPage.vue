<template>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Sidenav Light</h1>
        <ol class="breadcrumb mb-4">
            <li class="breadcrumb-item">
                <RouterLink :to="{ name: 'dashboard' }">Dashboard</RouterLink>
            </li>
            <li class="breadcrumb-item active">Sidenav Light</li>
        </ol>
        <div class="card mb-4">
            <div class="card-body">
                This page illustrates the light side navigation option. Adding the
                <code>.sb-sidenav-light</code>
                class to the
                <code>.sb-sidenav</code>
                element switches the navigation to a light color scheme. The default
                <code>.sb-sidenav-dark</code>
                class is also available for a darker variant.
            </div>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

