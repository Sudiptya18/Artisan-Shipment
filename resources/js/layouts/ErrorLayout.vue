<template>
    <div id="layoutError">
        <div id="layoutError_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <RouterView />
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutError_footer">
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">© {{ currentYear }} Artisan Shipment</div>
                        <div class="links">
                            <a href="#">Privacy Policy</a>
                            <span class="mx-1">·</span>
                            <a href="#">Terms &amp; Conditions</a>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted } from 'vue';
import { RouterView } from 'vue-router';

const currentYear = computed(() => new Date().getFullYear());

onMounted(() => {
    document.body.classList.remove('sb-nav-fixed', 'sb-sidenav-toggled', 'bg-primary');
});

onBeforeUnmount(() => {
    document.body.classList.remove('bg-primary');
});
</script>

