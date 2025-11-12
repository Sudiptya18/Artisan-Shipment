<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <div>
                <h1 class="mt-2">Create Product</h1>
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item">
                        <RouterLink :to="{ name: 'products-list' }">Products</RouterLink>
                    </li>
                    <li class="breadcrumb-item active">Create</li>
                </ol>
            </div>
        </div>

        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
            {{ alert.message }}
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Product Title *</label>
                            <input
                                v-model="form.product_title"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.product_title }"
                                placeholder="Product name"
                                required
                            />
                            <div v-if="errors.product_title" class="invalid-feedback">
                                {{ errors.product_title.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">SKU</label>
                            <input
                                v-model="form.sku"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.sku }"
                                placeholder="SKU / Item code"
                            />
                            <div v-if="errors.sku" class="invalid-feedback">
                                {{ errors.sku.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Global Code</label>
                            <input
                                v-model="form.global_code"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.global_code }"
                                placeholder="EAN / UPC"
                            />
                            <div v-if="errors.global_code" class="invalid-feedback">
                                {{ errors.global_code.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Brand</label>
                            <select
                                v-model="form.brand_id"
                                class="form-select"
                                :class="{ 'is-invalid': errors.brand_id }"
                            >
                                <option value="">Select brand</option>
                                <option v-for="brand in lookups.brands" :key="brand.id" :value="brand.id">
                                    {{ brand.name }}
                                </option>
                            </select>
                            <div v-if="errors.brand_id" class="invalid-feedback">
                                {{ errors.brand_id.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Category</label>
                            <select
                                v-model="form.category_id"
                                class="form-select"
                                :class="{ 'is-invalid': errors.category_id }"
                            >
                                <option value="">Select category</option>
                                <option v-for="category in lookups.categories" :key="category.id" :value="category.id">
                                    {{ category.name }}
                                </option>
                            </select>
                            <div v-if="errors.category_id" class="invalid-feedback">
                                {{ errors.category_id.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Format</label>
                            <select
                                v-model="form.format_id"
                                class="form-select"
                                :class="{ 'is-invalid': errors.format_id }"
                            >
                                <option value="">Select format</option>
                                <option v-for="format in lookups.formats" :key="format.id" :value="format.id">
                                    {{ format.name }}
                                </option>
                            </select>
                            <div v-if="errors.format_id" class="invalid-feedback">
                                {{ errors.format_id.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Origin</label>
                            <select
                                v-model="form.origin_id"
                                class="form-select"
                                :class="{ 'is-invalid': errors.origin_id }"
                            >
                                <option value="">Select origin</option>
                                <option v-for="origin in lookups.origins" :key="origin.id" :value="origin.id">
                                    {{ origin.name }}
                                    <span v-if="origin.iso_code">({{ origin.iso_code }})</span>
                                </option>
                            </select>
                            <div v-if="errors.origin_id" class="invalid-feedback">
                                {{ errors.origin_id.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Pack Size</label>
                            <input
                                v-model="form.pack_size"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.pack_size }"
                                placeholder="e.g. 12 x 500ml"
                            />
                            <div v-if="errors.pack_size" class="invalid-feedback">
                                {{ errors.pack_size.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="4"
                                class="form-control"
                                :class="{ 'is-invalid': errors.description }"
                                placeholder="Detailed description"
                            ></textarea>
                            <div v-if="errors.description" class="invalid-feedback">
                                {{ errors.description.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Benefits</label>
                            <textarea
                                v-model="form.benefits"
                                rows="4"
                                class="form-control"
                                :class="{ 'is-invalid': errors.benefits }"
                                placeholder="Key benefits"
                            ></textarea>
                            <div v-if="errors.benefits" class="invalid-feedback">
                                {{ errors.benefits.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Images</label>
                            <input
                                type="file"
                                class="form-control"
                                multiple
                                accept="image/*"
                                @change="handleImageSelection"
                            />
                            <div class="form-text">Maximum 10 images, 5 MB each.</div>
                            <div v-if="errors.images" class="text-danger small">
                                {{ errors.images.join(', ') }}
                            </div>
                            <div v-if="errors['images.0']" class="text-danger small">
                                {{ errors['images.0'].join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-center">
                            <div class="form-check form-switch mt-4">
                                <input
                                    v-model="form.active"
                                    class="form-check-input"
                                    type="checkbox"
                                    role="switch"
                                    id="product-active"
                                />
                                <label class="form-check-label" for="product-active">Active</label>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
                            <span v-if="isSubmitting" class="spinner-border spinner-border-sm me-2" role="status"></span>
                            Save Product
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { reactive, ref } from 'vue';
import { RouterLink, useRouter } from 'vue-router';

const router = useRouter();
const lookups = reactive({
    brands: [],
    categories: [],
    formats: [],
    origins: [],
});

const form = reactive({
    product_title: '',
    sku: '',
    global_code: '',
    description: '',
    benefits: '',
    pack_size: '',
    brand_id: '',
    category_id: '',
    format_id: '',
    origin_id: '',
    active: true,
    images: [],
});

const errors = reactive({});
const isSubmitting = ref(false);
const alert = reactive({
    type: 'success',
    message: '',
});

const loadLookups = async () => {
    try {
        const response = await axios.get('/api/products/lookups');
        Object.assign(lookups, response.data);
    } catch (error) {
        if (error.response?.status === 401) {
            router.push({ name: 'login' });
            return;
        }

        console.error('Failed to load product lookups', error);
    }
};

const resetForm = () => {
    form.product_title = '';
    form.sku = '';
    form.global_code = '';
    form.description = '';
    form.benefits = '';
    form.pack_size = '';
    form.brand_id = '';
    form.category_id = '';
    form.format_id = '';
    form.origin_id = '';
    form.active = true;
    form.images = [];
};

const handleImageSelection = (event) => {
    form.images = Array.from(event.target.files);
};

const submit = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    const payload = new FormData();
    Object.entries(form).forEach(([key, value]) => {
        if (key === 'images') {
            value.forEach((file, index) => {
                payload.append(`images[${index}]`, file);
            });
        } else if (value !== null && value !== undefined && value !== '') {
            payload.append(key, value);
        }
    });
    payload.append('active', form.active ? '1' : '0');

    try {
        await axios.post('/api/products', payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        alert.type = 'success';
        alert.message = 'Product created successfully.';
        resetForm();
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the highlighted fields.';
        } else if (error.response?.status === 401) {
            router.push({ name: 'login' });
        } else {
            alert.type = 'danger';
            alert.message = 'Something went wrong while saving the product.';
            console.error(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};

loadLookups();
</script>

