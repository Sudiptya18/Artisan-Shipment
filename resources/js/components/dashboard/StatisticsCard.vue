<template>
    <div class="col-xl-3 col-md-6">
        <div :class="`card bg-${color} text-white mb-4`">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="small text-white-50">{{ title }}</div>
                        <div v-if="isLoading" class="h3 mb-0">
                            <Loader class="text-white-50" />
                        </div>
                        <div v-else class="h3 mb-0">{{ value || 0 }}</div>
                    </div>
                    <div>
                        <i :class="`${icon} fa-2x text-white-50`"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';
import Loader from '@/components/Loader.vue';

const props = defineProps({
    title: {
        type: String,
        required: true,
    },
    endpoint: {
        type: String,
        required: true,
    },
    color: {
        type: String,
        default: 'primary',
    },
    icon: {
        type: String,
        required: true,
    },
});

const value = ref(0);
const isLoading = ref(true);

const fetchData = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/dashboard/${props.endpoint}`);
        value.value = response.data.total || 0;
    } catch (error) {
        console.error(`Failed to fetch ${props.title}:`, error);
        value.value = 0;
    } finally {
        isLoading.value = false;
    }
};

onMounted(() => {
    fetchData();
});
</script>

<style scoped>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}
</style>

