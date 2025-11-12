<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <div>
                <h1 class="mt-2">Products</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item active">Products</li>
                </ol>
            </div>
            <RouterLink class="btn btn-primary" :to="{ name: 'products-create' }">
                <i class="fas fa-plus me-2"></i>
                New Product
            </RouterLink>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form class="row g-3 align-items-end" @submit.prevent="fetchProducts()">
                    <div class="col-md-6 col-lg-4">
                        <label class="form-label">Search</label>
                        <input
                            v-model="filters.search"
                            type="text"
                            class="form-control"
                            placeholder="Search by title, SKU, or global code"
                        />
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <label class="form-label">Status</label>
                        <select v-model="filters.active" class="form-select">
                            <option value="">All</option>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                    <div class="col-md-3 col-lg-2">
                        <button class="btn btn-outline-primary w-100" type="submit" :disabled="isLoading">
                            <span v-if="isLoading" class="spinner-border spinner-border-sm me-2"></span>
                            Apply
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead>
                            <tr>
                                <th>Title</th>
                                <th>SKU</th>
                                <th>Brand</th>
                                <th>Category</th>
                                <th>Format</th>
                                <th>Origin</th>
                                <th>Status</th>
                                <th>Created</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="isLoading">
                                <td colspan="8" class="text-center">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Loading products...
                                </td>
                            </tr>
                            <tr v-else-if="!products.length">
                                <td colspan="8" class="text-center text-muted">No products found.</td>
                            </tr>
                            <tr v-for="product in products" :key="product.id">
                                <td>
                                    <strong>{{ product.product_title }}</strong>
                                    <div class="text-muted small" v-if="product.global_code">
                                        Global: {{ product.global_code }}
                                    </div>
                                </td>
                                <td>{{ product.sku || '—' }}</td>
                                <td>{{ product.brand?.name || '—' }}</td>
                                <td>{{ product.category?.name || '—' }}</td>
                                <td>{{ product.format?.name || '—' }}</td>
                                <td>{{ product.origin?.name || '—' }}</td>
                                <td>
                                    <span
                                        class="badge"
                                        :class="product.active ? 'bg-success' : 'bg-secondary'"
                                    >
                                        {{ product.active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td>{{ formatDate(product.created_at) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <nav v-if="pagination.last_page > 1">
                    <ul class="pagination justify-content-end mb-0">
                        <li class="page-item" :class="{ disabled: pagination.current_page === 1 }">
                            <button class="page-link" @click="fetchProducts(pagination.current_page - 1)" :disabled="pagination.current_page === 1">
                                Previous
                            </button>
                        </li>
                        <li
                            class="page-item"
                            v-for="page in pages"
                            :key="page"
                            :class="{ active: page === pagination.current_page }"
                        >
                            <button class="page-link" @click="fetchProducts(page)">
                                {{ page }}
                            </button>
                        </li>
                        <li class="page-item" :class="{ disabled: pagination.current_page === pagination.last_page }">
                            <button
                                class="page-link"
                                @click="fetchProducts(pagination.current_page + 1)"
                                :disabled="pagination.current_page === pagination.last_page"
                            >
                                Next
                            </button>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { computed, reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

const router = useRouter();
const products = ref([]);
const filters = reactive({
    search: '',
    active: '',
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 15,
});

const isLoading = ref(false);

const fetchProducts = async (page = 1) => {
    isLoading.value = true;
    try {
        const response = await axios.get('/api/products', {
            params: {
                page,
                search: filters.search,
                active: filters.active,
            },
        });

        const payload = response.data;
        products.value = payload.data;
        if (payload.meta) {
            Object.assign(pagination, {
                current_page: payload.meta.current_page,
                last_page: payload.meta.last_page,
                per_page: payload.meta.per_page,
            });
        } else {
            pagination.current_page = 1;
            pagination.last_page = 1;
        }
    } catch (error) {
        if (error.response?.status === 401) {
            router.push({ name: 'login' });
            return;
        }

        console.error('Failed to fetch products', error);
    } finally {
        isLoading.value = false;
    }
};

const pages = computed(() => {
    const total = pagination.last_page;
    const current = pagination.current_page;

    if (total <= 7) {
        return Array.from({ length: total }, (_, index) => index + 1);
    }

    const start = Math.max(1, current - 2);
    const end = Math.min(total, current + 2);
    const set = new Set([1, total]);

    for (let page = start; page <= end; page += 1) {
        set.add(page);
    }

    return Array.from(set).sort((a, b) => a - b);
});

const formatDate = (value) => {
    if (!value) return '—';
    return new Date(value).toLocaleDateString();
};

fetchProducts();
</script>

