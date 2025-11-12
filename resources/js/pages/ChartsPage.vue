<template>
    <div class="container-fluid px-4">
        <h1 class="mt-4">Charts</h1>
        <div class="card mb-4">
            <div class="card-body">
                Chart.js is a third party plugin that is used to generate the charts in this template. The charts below have
                been customized - further customization options can be found in the
                <a href="https://www.chartjs.org/docs/latest/" target="_blank" rel="noopener noreferrer">Chart.js documentation</a>.
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-area me-1"></i>
                        Area Chart Example
                    </div>
                    <div class="card-body">
                        <canvas ref="areaChartRef" height="350"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="fas fa-chart-bar me-1"></i>
                        Bar Chart Example
                    </div>
                    <div class="card-body">
                        <canvas ref="barChartRef" height="350"></canvas>
                    </div>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <i class="fas fa-chart-pie me-1"></i>
                Pie Chart Example
            </div>
            <div class="card-body d-flex justify-content-center">
                <div class="w-50">
                    <canvas ref="pieChartRef" height="350"></canvas>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onBeforeUnmount, onMounted, ref } from 'vue';
import { RouterLink } from 'vue-router';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const areaChartRef = ref(null);
const barChartRef = ref(null);
const pieChartRef = ref(null);

let areaChart;
let barChart;
let pieChart;

const areaChartConfig = {
    type: 'line',
    data: {
        labels: ['Mar 1', 'Mar 2', 'Mar 3', 'Mar 4', 'Mar 5', 'Mar 6', 'Mar 7', 'Mar 8', 'Mar 9', 'Mar 10', 'Mar 11', 'Mar 12', 'Mar 13'],
        datasets: [
            {
                label: 'Sessions',
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
                data: [10000, 30162, 26263, 18394, 18287, 28682, 31274, 33259, 25849, 24159, 32651, 31984, 38451],
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
                    maxTicksLimit: 7,
                },
            },
            y: {
                min: 0,
                max: 40000,
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
};

const barChartConfig = {
    type: 'bar',
    data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June'],
        datasets: [
            {
                label: 'Revenue',
                backgroundColor: 'rgba(2, 117, 216, 1)',
                borderColor: 'rgba(2, 117, 216, 1)',
                data: [4215, 5312, 6251, 7841, 9821, 14984],
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
                    maxTicksLimit: 6,
                },
            },
            y: {
                min: 0,
                max: 15000,
                ticks: {
                    maxTicksLimit: 5,
                },
                grid: {
                    display: true,
                },
            },
        },
        plugins: {
            legend: {
                display: false,
            },
        },
    },
};

const pieChartConfig = {
    type: 'pie',
    data: {
        labels: ['Blue', 'Red', 'Yellow'],
        datasets: [
            {
                data: [12.21, 15.58, 11.25],
                backgroundColor: ['#007bff', '#dc3545', '#ffc107'],
            },
        ],
    },
};

onMounted(() => {
    if (areaChartRef.value) {
        areaChart = new Chart(areaChartRef.value, areaChartConfig);
    }
    if (barChartRef.value) {
        barChart = new Chart(barChartRef.value, barChartConfig);
    }
    if (pieChartRef.value) {
        pieChart = new Chart(pieChartRef.value, pieChartConfig);
    }
});

onBeforeUnmount(() => {
    areaChart?.destroy();
    barChart?.destroy();
    pieChart?.destroy();
});
</script>

