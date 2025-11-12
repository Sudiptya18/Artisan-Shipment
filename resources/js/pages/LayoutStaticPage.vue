<template>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Static Navigation</h1>
        <div class="card mb-4">
            <div class="card-body">
                <p class="mb-0">
                    This page demonstrates static navigation. Removing the
                    <code>.sb-nav-fixed</code>
                    class from the
                    <code>body</code>
                    element causes the navigation to remain static when scrolling. Scroll down this page to preview the effect.
                </p>
            </div>
        </div>
        <div style="height: 100vh"></div>
        <div class="card mb-4">
            <div class="card-body">
                When scrolling, the navigation stays at the top of the page. This concludes the static navigation demo.
            </div>
        </div>
    </div>
</template>

<script setup>
import { RouterLink } from 'vue-router';
</script>

