<template>
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-chart-area me-1"></i>
            Products by Brand
        </div>
        <div class="card-body position-relative">
            <div v-if="isLoading" class="text-center py-5 position-absolute top-50 start-50 translate-middle w-100">
                <Loader />
            </div>
            <canvas ref="areaChartRef" height="350" :style="{ opacity: isLoading ? 0 : 1 }"></canvas>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref, nextTick } from 'vue';
import { Chart, registerables } from 'chart.js';
import axios from 'axios';
import Loader from '@/components/Loader.vue';

Chart.register(...registerables);

const areaChartRef = ref(null);
let areaChart = null;
const data = ref([]);
const isLoading = ref(true);

const fetchData = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/dashboard/products-by-brand');
        data.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to fetch products by brand:', error);
        data.value = [];
    } finally {
        isLoading.value = false;
    }
};

const createChart = () => {
    if (!areaChartRef.value || !data.value.length) {
        return;
    }

    const labels = data.value.map(item => item.name);
    const chartData = data.value.map(item => item.count);
    const maxValue = Math.max(...chartData, 1);

    // Destroy existing chart if it exists
    if (areaChart) {
        areaChart.destroy();
        areaChart = null;
    }

    // Create new chart
    areaChart = new Chart(areaChartRef.value, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Products',
                    tension: 0.3,
                    fill: true,
                    backgroundColor: 'rgba(2, 117, 216, 0.2)',
                    borderColor: 'rgba(2, 117, 216, 1)',
                    pointRadius: 5,
                    pointBackgroundColor: 'rgba(2, 117, 216, 1)',
                    pointBorderColor: 'rgba(255, 255, 255, 0.8)',
                    pointHoverRadius: 5,
                    pointHoverBackgroundColor: 'rgba(2, 117, 216, 1)',
                    pointHitRadius: 50,
                    pointBorderWidth: 2,
                    data: chartData,
                },
            ],
        },
        options: {
            maintainAspectRatio: false,
            scales: {
                x: {
                    grid: {
                        display: false,
                    },
                    ticks: {
                        maxTicksLimit: labels.length > 10 ? 10 : labels.length,
                    },
                },
                y: {
                    min: 0,
                    max: maxValue + Math.ceil(maxValue * 0.1),
                    ticks: {
                        maxTicksLimit: 5,
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, .125)',
                    },
                },
            },
            plugins: {
                legend: {
                    display: false,
                },
            },
        },
    });
};

onMounted(async () => {
    await fetchData();
    
    // Wait for DOM to be ready and canvas to be rendered
    await nextTick();
    
    // Small delay to ensure canvas is fully rendered
    setTimeout(() => {
        if (data.value.length > 0 && areaChartRef.value) {
            createChart();
        }
    }, 100);
});

onBeforeUnmount(() => {
    if (areaChart) {
        areaChart.destroy();
        areaChart = null;
    }
});
</script>

<style scoped>
.card {
    border: none;
    box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.15);
}

.card-header {
    background-color: #f8f9fc;
    border-bottom: 1px solid #e3e6f0;
    font-weight: 600;
}
</style>

