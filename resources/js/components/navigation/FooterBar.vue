<template>
    <footer class="py-4 bg-light mt-auto">
        <div class="container-fluid px-4">
            <div class="d-flex align-items-center justify-content-between small">
                <div class="text-muted">All rights reserved. © {{ currentYear }} Artisan Shipment</div>
                <!-- <div class="links">
                    <a href="#">Privacy Policy</a>
                    <span class="mx-1">·</span>
                    <a href="#">Terms &amp; Conditions</a>
                </div> -->
            </div>
        </div>
    </footer>
</template>

<script setup>
import { computed } from 'vue';

const currentYear = computed(() => new Date().getFullYear());
</script>

