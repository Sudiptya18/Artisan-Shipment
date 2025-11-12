<template>
    <div id="layoutAuthentication" class="auth-layout">
        <div id="layoutAuthentication_content">
            <main>
                <div class="container">
                    <div class="row justify-content-center">
                        <RouterView />
                    </div>
                </div>
            </main>
        </div>
        <div id="layoutAuthentication_footer">
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
    document.body.style.backgroundColor = '#ffffff';
    document.body.style.backgroundImage = 'url(/assets/img/bg.png)';
    document.body.style.backgroundSize = 'cover';
    document.body.style.backgroundPosition = 'center';
    document.body.style.backgroundRepeat = 'no-repeat';
    document.body.classList.remove('sb-nav-fixed', 'sb-sidenav-toggled', 'bg-primary');
});

onBeforeUnmount(() => {
    document.body.style.backgroundColor = '';
    document.body.style.backgroundImage = '';
    document.body.style.backgroundSize = '';
    document.body.style.backgroundPosition = '';
    document.body.style.backgroundRepeat = '';
});
</script>

<style scoped>
.auth-layout {
    min-height: 100vh;
}
</style>

