<template>
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between my-4">
            <h1 class="mb-0">Product List</h1>
            <div class="d-flex align-items-center gap-3">
                <div class="search-box">
                    <input
                        v-model="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Search..."
                        @input="debouncedSearch"
                    />
                </div>
                <RouterLink :to="{ name: 'products-create' }" class="btn-add-product">
                    <i class="fas fa-plus"></i>
                </RouterLink>
            </div>
        </div>

        <!-- Table Section -->
        <div v-if="alert.message" :class="`alert alert-${alert.type} mx-4 mt-4`" role="alert">
            {{ alert.message }}
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0 product-list-table">
                        <thead>
                            <tr>
                                <th>
                                    <div class="th-content">
                                        <span>Serial</span>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Global Code</span>
                                        <input
                                            v-model="columnFilters.global_code"
                                            type="text"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Filter Global Code..."
                                            @input="debouncedColumnFilter"
                                        />
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Product Title</span>
                                        <input
                                            v-model="columnFilters.product_title"
                                            type="text"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Filter Title..."
                                            @input="debouncedColumnFilter"
                                        />
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Brand</span>
                                        <select
                                            v-model="columnFilters.brand_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">All Brands</option>
                                            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                                {{ brand.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Category</span>
                                        <select
                                            v-model="columnFilters.category_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">All Categories</option>
                                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Format</span>
                                        <select
                                            v-model="columnFilters.format_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">All Formats</option>
                                            <option v-for="format in formats" :key="format.id" :value="format.id">
                                                {{ format.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Origin</span>
                                        <select
                                            v-model="columnFilters.origin_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">All Origins</option>
                                            <option v-for="origin in origins" :key="origin.id" :value="origin.id">
                                                {{ origin.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Status</span>
                                        <select
                                            v-model="columnFilters.active"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">All</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </th>
                                <th>
                                    <div class="th-content">
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="isLoading">
                                <td colspan="9" class="text-center py-4">
                                    <span class="spinner-border spinner-border-sm me-2"></span>
                                    Loading products...
                                </td>
                            </tr>
                            <tr v-else-if="!products.length">
                                <td colspan="9" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                            <tr v-for="(product, index) in products" :key="product.id">
                                <td>{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
                                <td>{{ product.global_code || '—' }}</td>
                                <td>{{ product.product_title || '—' }}</td>
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
                                <td>
                                    <div class="d-flex gap-2 justify-content-center">
                                        <button
                                            class="btn btn-sm btn-primary"
                                            @click="editProduct(product)"
                                            title="Edit"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button
                                            class="btn btn-sm btn-danger"
                                            @click="deleteProduct(product)"
                                            title="Delete"
                                        >
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="pagination.last_page > 1" class="d-flex justify-content-center p-3 border-top">
                    <vue-awesome-paginate
                        :total-items="pagination.total || 0"
                        v-model="pagination.current_page"
                        :items-per-page="pagination.per_page"
                        :max-pages-shown="5"
                        paginate-buttons-class="btn"
                        active-page-class="btn-active"
                        back-button-class="back-btn"
                        next-button-class="next-btn"
                        @click="handlePageChange"
                    />
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { reactive, ref, onMounted } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import VueAwesomePaginate from 'vue-awesome-paginate';
import 'vue-awesome-paginate/dist/style.css';

const router = useRouter();
const products = ref([]);
const brands = ref([]);
const categories = ref([]);
const formats = ref([]);
const origins = ref([]);

const filters = reactive({
    search: '',
});

const columnFilters = reactive({
    global_code: '',
    product_title: '',
    brand_id: '',
    category_id: '',
    format_id: '',
    origin_id: '',
    active: '',
});

const pagination = reactive({
    current_page: 1,
    last_page: 1,
    per_page: 20,
    total: 0,
});

const isLoading = ref(false);
let searchTimeout = null;
let columnFilterTimeout = null;

const fetchLookups = async () => {
    try {
        const response = await axios.get('/api/products/lookups');
        brands.value = response.data.brands || [];
        categories.value = response.data.categories || [];
        formats.value = response.data.formats || [];
        origins.value = response.data.origins || [];
    } catch (error) {
        console.error('Failed to fetch lookups:', error);
    }
};

const fetchProducts = async (page = 1) => {
    isLoading.value = true;
    try {
        const params = {
            per_page: pagination.per_page,
            page: page,
        };

        // Add search filter
        if (filters.search) {
            params.search = filters.search;
        }

        // Add column-specific filters
        if (columnFilters.global_code) {
            params.filter_global_code = columnFilters.global_code;
        }
        if (columnFilters.product_title) {
            params.filter_product_title = columnFilters.product_title;
        }
        if (columnFilters.brand_id) {
            params.filter_brand_id = columnFilters.brand_id;
        }
        if (columnFilters.category_id) {
            params.filter_category_id = columnFilters.category_id;
        }
        if (columnFilters.format_id) {
            params.filter_format_id = columnFilters.format_id;
        }
        if (columnFilters.origin_id) {
            params.filter_origin_id = columnFilters.origin_id;
        }
        if (columnFilters.active !== '') {
            params.filter_active = columnFilters.active;
        }

        const response = await axios.get('/api/products', { params });

        const payload = response.data;
        products.value = payload.data || [];
        if (payload.meta) {
            Object.assign(pagination, {
                current_page: payload.meta.current_page,
                last_page: payload.meta.last_page,
                per_page: payload.meta.per_page || pagination.per_page,
                total: payload.meta.total || 0,
            });
        } else if (payload.current_page) {
            Object.assign(pagination, {
                current_page: payload.current_page,
                last_page: payload.last_page,
                per_page: payload.per_page || pagination.per_page,
                total: payload.total || 0,
            });
        } else {
            pagination.current_page = 1;
            pagination.last_page = 1;
            pagination.total = products.value.length;
        }
    } catch (error) {
        if (error.response?.status === 401) {
            router.push({ name: 'login' });
            return;
        }
        console.error('Failed to fetch products:', error);
    } finally {
        isLoading.value = false;
    }
};

const handlePageChange = () => {
    fetchProducts(pagination.current_page);
};

const debouncedSearch = () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        pagination.current_page = 1;
        fetchProducts(1);
    }, 500);
};

const debouncedColumnFilter = () => {
    clearTimeout(columnFilterTimeout);
    columnFilterTimeout = setTimeout(() => {
        pagination.current_page = 1;
        fetchProducts(1);
    }, 500);
};

const handleColumnFilter = () => {
    pagination.current_page = 1;
    fetchProducts(1);
};

const editProduct = (product) => {
    router.push({ name: 'products-edit', params: { id: product.id } });
};

const deleteProduct = async (product) => {
    if (!confirm(`Are you sure you want to delete "${product.product_title || product.global_code}"?`)) {
        return;
    }

    try {
        await axios.delete(`/api/products/${product.id}`);
        alert.type = 'success';
        alert.message = 'Product deleted successfully.';
        fetchProducts(pagination.current_page);
    } catch (error) {
        console.error('Failed to delete product:', error);
        alert.type = 'danger';
        alert.message = error.response?.data?.message || 'Failed to delete product.';
    }
};

const alert = reactive({
    type: '',
    message: '',
});

onMounted(() => {
    fetchLookups();
    fetchProducts();
});
</script>

<style scoped>
.search-box {
    width: 300px;
}

.btn-add-product {
    width: 40px;
    height: 40px;
    background-color: #28a745;
    color: white;
    border: none;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    text-decoration: none;
    transition: background-color 0.2s;
}

.btn-add-product:hover {
    background-color: #218838;
    color: white;
}

.product-list-table {
    font-size: 0.9rem;
}

.product-list-table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    padding: 12px 8px;
    font-weight: 600;
    vertical-align: middle;
}

.product-list-table tbody td {
    padding: 12px 8px;
    vertical-align: middle;
}

.th-content {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.th-content span {
    font-weight: 600;
    font-size: 0.875rem;
}

.column-filter {
    font-size: 0.8rem;
    padding: 4px 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
}

.column-filter:focus {
    border-color: #80bdff;
    outline: 0;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}

/* Pagination Styles */
.btn {
    height: 40px;
    width: 40px;
    border: 1px solid #dee2e6;
    margin-inline: 5px;
    cursor: pointer;
    background-color: #fff;
    color: #212529;
    border-radius: 0.375rem;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.15s ease-in-out;
}

.btn:hover {
    background-color: #e9ecef;
    border-color: #adb5bd;
}

.back-btn,
.next-btn {
    background-color: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}

.back-btn:hover,
.next-btn:hover {
    background-color: #0b5ed7;
    border-color: #0a58ca;
}

.btn-active {
    background-color: #0d6efd;
    color: #fff;
    border-color: #0d6efd;
}
</style>
