<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <h1 class="mb-0">Product Details</h1>
            <div class="d-flex gap-2">
                <RouterLink :to="{ name: 'products-list' }" class="btn btn-secondary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </RouterLink>
                <RouterLink 
                    v-if="hasEditPermission"
                    :to="{ name: 'products-edit', params: { id: productId } }" 
                    class="btn btn-edit-product"
                >
                    <i class="fas fa-edit me-2"></i>Edit Product
                </RouterLink>
            </div>
        </div>

        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
            {{ alert.message }}
        </div>

        <div v-if="isLoading" class="text-center py-5">
            <Loader class="me-2" />
            Loading product details...
        </div>

        <div v-else-if="product" class="row g-4">
            <!-- Basic Information Card -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-custom-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-info-circle me-2"></i>Basic Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Global Code</label>
                                <p class="form-control-plaintext">{{ product.global_code || '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Product Title</label>
                                <p class="form-control-plaintext">{{ product.product_title || '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Brand</label>
                                <p class="form-control-plaintext">{{ product.brand?.name || '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Category</label>
                                <p class="form-control-plaintext">{{ product.category?.name || '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Format</label>
                                <p class="form-control-plaintext">{{ product.format?.name || '—' }}</p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Origin</label>
                                <p class="form-control-plaintext">
                                    {{ product.origin?.name || '—' }}
                                    <span v-if="product.origin?.iso_code"> ({{ product.origin.iso_code }})</span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Status</label>
                                <p class="form-control-plaintext">
                                    <span class="badge" :class="getStatusBadgeClass(product.status)">
                                        {{ product.status || '—' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Active</label>
                                <p class="form-control-plaintext">
                                    <span class="badge" :class="product.active ? 'bg-success' : 'bg-secondary'">
                                        {{ product.active ? 'Active' : 'Inactive' }}
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Pack Size</label>
                                <p class="form-control-plaintext">{{ product.pack_size || '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Description</label>
                                <p class="form-control-plaintext">{{ product.description || '—' }}</p>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label fw-bold">Benefits</label>
                                <p class="form-control-plaintext">{{ product.benefits || '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Images Card -->
            <div v-if="product.images && product.images.length > 0" class="col-12">
                <div class="card">
                    <div class="card-header bg-custom-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-images me-2"></i>Product Images</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div v-for="image in product.images" :key="image.id" class="col-md-3 col-sm-4 col-6">
                                <div class="position-relative">
                                    <img
                                        :src="image.url"
                                        :alt="image.alt_text || 'Product Image'"
                                        class="img-thumbnail w-100"
                                        style="height: 200px; object-fit: cover; cursor: pointer;"
                                        @click="openImageModal(image.url)"
                                    />
                                    <div v-if="image.alt_text" class="text-center mt-2 small text-muted">
                                        {{ image.alt_text }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Product Details Card -->
            <div v-if="product.product_detail" class="col-12">
                <div class="card">
                    <div class="card-header bg-custom-primary text-white">
                        <h5 class="mb-0"><i class="fas fa-shipping-fast me-2"></i>Shipment Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Pcs/Cases</label>
                                <p class="form-control-plaintext">{{ product.product_detail.pcs_cases || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cases/Pal</label>
                                <p class="form-control-plaintext">{{ product.product_detail.cases_pal || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cases/Lay</label>
                                <p class="form-control-plaintext">{{ product.product_detail.cases_lay || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Container Load</label>
                                <p class="form-control-plaintext">{{ product.product_detail.container_load || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cases / 20 ft Container</label>
                                <p class="form-control-plaintext">{{ product.product_detail.cases_20ft_container || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Cases / 40 ft Container</label>
                                <p class="form-control-plaintext">{{ product.product_detail.cases_40ft_container || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Total Shelf Life</label>
                                <p class="form-control-plaintext">{{ product.product_detail.total_shelf_life || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Gross Weight (CS) - KG</label>
                                <p class="form-control-plaintext">{{ product.product_detail.gross_weight_cs_kg || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Net Weight (CS) - KG</label>
                                <p class="form-control-plaintext">{{ product.product_detail.net_weight_cs_kg || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">CBM</label>
                                <p class="form-control-plaintext">{{ product.product_detail.cbm || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">HS Code</label>
                                <p class="form-control-plaintext">{{ product.product_detail.hs_code || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Rate</label>
                                <p class="form-control-plaintext">{{ product.product_detail.rate || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Shipment Title</label>
                                <p class="form-control-plaintext">{{ product.product_detail.shipment_title || '—' }}</p>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label fw-bold">Commodity</label>
                                <p class="form-control-plaintext">{{ product.product_detail.commodity || '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Timestamps Card -->
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-secondary text-white">
                        <h5 class="mb-0"><i class="fas fa-clock me-2"></i>Timestamps</h5>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Created At</label>
                                <p class="form-control-plaintext">{{ formatDate(product.created_at) || '—' }}</p>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Updated At</label>
                                <p class="form-control-plaintext">{{ formatDate(product.updated_at) || '—' }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div v-else class="card">
            <div class="card-body text-center py-5">
                <p class="text-muted">Product not found.</p>
                <RouterLink :to="{ name: 'products-list' }" class="btn btn-primary">
                    <i class="fas fa-arrow-left me-2"></i>Back to List
                </RouterLink>
            </div>
        </div>

        <!-- Image Modal -->
        <div v-if="selectedImage" class="modal fade show" style="display: block; background: rgba(0,0,0,0.8);" @click="closeImageModal">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Product Image</h5>
                        <button type="button" class="btn-close" @click="closeImageModal"></button>
                    </div>
                    <div class="modal-body text-center">
                        <img :src="selectedImage" alt="Product Image" class="img-fluid" style="max-height: 70vh;" />
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { ref, reactive, onMounted } from 'vue';
import { RouterLink, useRoute, useRouter } from 'vue-router';
import Loader from '@/components/Loader.vue';

const route = useRoute();
const router = useRouter();
const productId = route.params.id;

const product = ref(null);
const isLoading = ref(true);
const hasEditPermission = ref(false);
const selectedImage = ref(null);
const alert = reactive({
    type: '',
    message: '',
});

const loadProduct = async () => {
    isLoading.value = true;
    try {
        const response = await axios.get(`/api/products/${productId}`);
        product.value = response.data.data || response.data;
    } catch (error) {
        if (error.response?.status === 401) {
            router.push({ name: 'login' });
            return;
        }
        if (error.response?.status === 404) {
            alert.type = 'danger';
            alert.message = 'Product not found.';
        } else {
            alert.type = 'danger';
            alert.message = 'Failed to load product details.';
            console.error(error);
        }
    } finally {
        isLoading.value = false;
    }
};

const checkEditPermission = async () => {
    try {
        const response = await axios.get('/api/navigations/check-permission/products-edit');
        hasEditPermission.value = response.data.allowed || false;
    } catch (error) {
        console.error('Failed to check edit permission:', error);
        hasEditPermission.value = false;
    }
};

const getStatusBadgeClass = (status) => {
    const statusClasses = {
        'ACTIVE': 'bg-success',
        'DISCONTINUED-UI': 'bg-warning',
        'DISCONTINUED-ARTISAN': 'bg-warning',
        'REPLACEMENT': 'bg-info',
        'REPLACEMENT & DISCONTINUED': 'bg-danger',
        'NEW CODE': 'bg-custom-primary',
        'FUTURE DISCONTINUED': 'bg-secondary',
        'NEW TENTATIVE': 'bg-secondary',
    };
    return statusClasses[status] || 'bg-secondary';
};

const formatDate = (dateString) => {
    if (!dateString) return '—';
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};

const openImageModal = (imageUrl) => {
    selectedImage.value = imageUrl;
};

const closeImageModal = () => {
    selectedImage.value = null;
};

onMounted(() => {
    loadProduct();
    checkEditPermission();
});
</script>

<style scoped>
.form-control-plaintext {
    padding: 0.375rem 0;
    margin-bottom: 0;
    line-height: 1.5;
    color: #212529;
    background-color: transparent;
    border: solid transparent;
    border-width: 1px 0;
    min-height: 2.5rem;
}

.card-header {
    padding: 1rem 1.5rem;
}

.card-body {
    padding: 1.5rem;
}

.form-label {
    margin-bottom: 0.5rem;
    color: #495057;
}

.badge {
    font-size: 0.875rem;
    padding: 0.35em 0.65em;
}

.modal.show {
    display: block;
}

.modal-content {
    border: none;
    border-radius: 0.5rem;
}

.modal-header {
    border-bottom: 1px solid #dee2e6;
}

.btn-close {
    background: none;
    border: none;
    font-size: 1.5rem;
    opacity: 0.5;
    cursor: pointer;
}

.btn-close:hover {
    opacity: 1;
}

.bg-custom-primary {
    background-color: #0e9351 !important;
}

.btn-edit-product {
    background-color: #0e9351 !important;
    border-color: #0e9351 !important;
    color: #fff !important;
}

.btn-edit-product:hover {
    background-color: #18a950 !important;
    border-color: #18a950 !important;
    color: #fff !important;
}
</style>
