<template>
    <div class="container-fluid px-4">
        <!-- Header Section -->
        <div class="d-flex align-items-center justify-content-between my-4">
            <h1 class="mb-0">Products List</h1>
            <div class="d-flex align-items-center gap-3">
                <span class="text-muted showing-text">
                    Showing {{ showingCount }}  out of {{ pagination.total }}
                </span>
                <div class="search-box">
                    <input
                        v-model="filters.search"
                        type="text"
                        class="form-control"
                        placeholder="Search..."
                        @input="debouncedSearch"
                    />
                </div>
                <select v-model="pagination.per_page" class="form-select" style="width: auto;" @change="handlePerPageChange">
                    <option :value="20">20 per page</option>
                    <option :value="50">50 per page</option>
                    <option :value="100">100 per page</option>
                    <option :value="150">150 per page</option>
                    <option :value="200">200 per page</option>
                    <option :value="0">All</option>
                </select>
                <RouterLink :to="{ name: 'products-create' }" class="btn-add-product" title="Add Single Product">
                    <i class="fas fa-plus"></i>
                </RouterLink>
                <RouterLink :to="{ name: 'products-multiple-create' }" class="btn-add-product btn-multiple-add" title="Add Multiple Products">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16" style="display: inline-block;">
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" transform="translate(4, 4)"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" transform="translate(-4, 4)"/>
                        <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" transform="translate(4, -4)"/>
                    </svg>
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
                                <th class="text-center">
                                    <div class="th-content">
                                        <span>Serial</span>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <input
                                            v-model="columnFilters.global_code"
                                            type="text"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Global Code..."
                                            @input="debouncedColumnFilter"
                                        />
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <input
                                            v-model="columnFilters.product_title"
                                            type="text"
                                            class="form-control form-control-sm column-filter"
                                            placeholder="Product Title..."
                                            @input="debouncedColumnFilter"
                                        />
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <select
                                            v-model="columnFilters.brand_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">Brands</option>
                                            <option v-for="brand in brands" :key="brand.id" :value="brand.id">
                                                {{ brand.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <select
                                            v-model="columnFilters.category_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">Categories</option>
                                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                                {{ category.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <select
                                            v-model="columnFilters.format_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">Formats</option>
                                            <option v-for="format in formats" :key="format.id" :value="format.id">
                                                {{ format.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <select
                                            v-model="columnFilters.origin_id"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">Origins</option>
                                            <option v-for="origin in origins" :key="origin.id" :value="origin.id">
                                                {{ origin.name }}
                                            </option>
                                        </select>
                                    </div>
                                </th>
                                <th class="text-center">
                                    <div class="th-content">
                                        <select
                                            v-model="columnFilters.active"
                                            class="form-control form-control-sm column-filter"
                                            @change="handleColumnFilter"
                                        >
                                            <option value="">Status</option>
                                            <option value="1">Active</option>
                                            <option value="0">Inactive</option>
                                        </select>
                                    </div>
                                </th>
                                <th v-if="hasEditPermission" class="text-center">
                                    <div class="th-content">
                                        <span>Actions</span>
                                    </div>
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-if="isLoading">
                                <td :colspan="hasEditPermission ? 9 : 8" class="text-center py-4">
                                    <Loader class="me-2" />
                                    Loading products...
                                </td>
                            </tr>
                            <tr v-else-if="!products.length">
                                <td :colspan="hasEditPermission ? 9 : 8" class="text-center py-4 text-muted">No products found.</td>
                            </tr>
                            <tr v-for="(product, index) in products" :key="product.id">
                                <td class="text-center">{{ (pagination.current_page - 1) * pagination.per_page + index + 1 }}</td>
                                <td class="text-center">{{ product.global_code || '—' }}</td>
                                <td class="text-center">{{ product.product_title || '—' }}</td>
                                <td class="text-center">{{ product.brand?.name || '—' }}</td>
                                <td class="text-center">{{ product.category?.name || '—' }}</td>
                                <td class="text-center">{{ product.format?.name || '—' }}</td>
                                <td class="text-center">{{ product.origin?.name || '—' }}</td>
                                <td class="text-center">
                                    <span
                                        class="badge"
                                        :class="product.active ? 'bg-success' : 'bg-secondary'"
                                    >
                                        {{ product.active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td v-if="hasEditPermission" class="text-center">
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
                <div v-if="pagination.last_page > 1 && pagination.per_page > 0" class="d-flex justify-content-center p-3 border-top">
                    <vue-awesome-paginate
                        :total-items="pagination.total || 0"
                        v-model="pagination.current_page"
                        :items-per-page="pagination.per_page"
                        :max-pages-shown="5"
                        :show-first-last-button="true"
                        paginate-buttons-class="btn"
                        active-page-class="btn-active"
                        back-button-class="back-btn"
                        next-button-class="next-btn"
                        first-button-class="back-btn"
                        last-button-class="next-btn"
                        @click="handlePageChange"
                    />
                </div>
            </div>
        </div>
    </div>

</template>

<script setup>
import axios from 'axios';
import { reactive, ref, onMounted, computed } from 'vue';
import { RouterLink, useRouter } from 'vue-router';
import VueAwesomePaginate from 'vue-awesome-paginate';
import Swal from 'sweetalert2';
import Loader from '@/components/Loader.vue';
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

const showingCount = computed(() => {
    if (pagination.per_page === 0) {
        return pagination.total;
    }
    const start = (pagination.current_page - 1) * pagination.per_page + 1;
    const end = Math.min(pagination.current_page * pagination.per_page, pagination.total);
    if (start > end) return 0;
    return end - start + 1;
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
        };

        // Only add page parameter if per_page > 0
        if (pagination.per_page > 0) {
            params.page = page;
        }

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

const handlePerPageChange = () => {
    pagination.current_page = 1;
    fetchProducts(1);
};

const editProduct = (product) => {
    router.push({ name: 'products-edit', params: { id: product.id } });
};

const productToDelete = ref(null);
const hasEditPermission = ref(false);

const deleteProduct = (product) => {
    productToDelete.value = product;
    const productName = product.product_title || product.global_code || 'this product';
    
    Swal.fire({
        title: 'Are you sure?',
        text: `You want to delete "${productName}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!'
    }).then(async (result) => {
        if (result.isConfirmed) {
            try {
                await axios.delete(`/api/products/${productToDelete.value.id}`);
                Swal.fire({
                    title: 'Deleted!',
                    text: 'Product has been deleted successfully.',
                    icon: 'success'
                });
                fetchProducts(pagination.current_page);
            } catch (error) {
                console.error('Failed to delete product:', error);
                Swal.fire({
                    title: 'Error!',
                    text: error.response?.data?.message || 'Failed to delete product.',
                    icon: 'error'
                });
            } finally {
                productToDelete.value = null;
            }
        } else {
            productToDelete.value = null;
        }
    });
};

const alert = reactive({
    type: '',
    message: '',
});

const checkEditPermission = async () => {
    try {
        const response = await axios.get('/api/navigations/check-permission/products-edit');
        hasEditPermission.value = response.data.allowed || false;
    } catch (error) {
        console.error('Failed to check edit permission:', error);
        hasEditPermission.value = false;
    }
};

onMounted(() => {
    fetchLookups();
    fetchProducts();
    checkEditPermission();
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

.btn-multiple-add {
    background-color: #17a2b8;
}

.btn-multiple-add:hover {
    background-color: #138496;
    color: white;
}

.btn-multiple-add svg {
    width: 16px;
    height: 16px;
}

.product-list-table {
    font-size: 0.9rem;
}

.product-list-table thead th {
    background-color: #f8f9fa;
    border-bottom: 2px solid #dee2e6;
    padding: 0;
    font-weight: 600;
    vertical-align: top;
    position: relative;
    min-width: 120px;
}

.product-list-table thead th:first-child {
    min-width: 80px;
}

.product-list-table tbody td {
    padding: 12px 8px;
    vertical-align: middle;
}

.th-content {
    display: flex;
    flex-direction: column;
    gap: 0;
    padding: 10px 8px;
    width: 100%;
    align-items: center;
    justify-content: center;
}

.th-content span {
    font-weight: 600;
    font-size: 0.875rem;
    color: #212529;
    white-space: nowrap;
    line-height: 1.2;
}

.column-filter {
    font-size: 0.8rem;
    padding: 4px 8px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    width: 100%;
    margin-top: 0;
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
