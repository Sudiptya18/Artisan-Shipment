<template>
    <div class="container-fluid px-4">
        <h1 class="mt-4 mb-4">Dashboard</h1>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <StatisticsCard
                title="Total Products"
                endpoint="total-products"
                color="primary"
                icon="fas fa-box"
            />
            <StatisticsCard
                title="Total Brands"
                endpoint="total-brands"
                color="success"
                icon="fas fa-tags"
            />
            <StatisticsCard
                title="Total Categories"
                endpoint="total-categories"
                color="warning"
                icon="fas fa-folder"
            />
            <StatisticsCard
                title="Total Formats"
                endpoint="total-formats"
                color="danger"
                icon="fas fa-file-alt"
            />
        </div>

        <!-- Area Chart -->
        <ProductsByBrandChart />
    </div>
</template>

<script setup>
import StatisticsCard from '@/components/dashboard/StatisticsCard.vue';
import ProductsByBrandChart from '@/components/dashboard/ProductsByBrandChart.vue';
</script>

