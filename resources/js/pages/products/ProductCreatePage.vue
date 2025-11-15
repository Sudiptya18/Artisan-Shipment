<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <h1 class="mb-0">Create Product</h1>
        </div>

        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
            {{ alert.message }}
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <form @submit.prevent="submit">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Global Code *</label>
                            <input
                                v-model="form.global_code"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.global_code }"
                                placeholder="Global Code (EAN/UPC)"
                                required
                            />
                            <div v-if="errors.global_code" class="invalid-feedback">
                                {{ errors.global_code.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Product Title</label>
                            <input
                                v-model="form.product_title"
                                type="text"
                                class="form-control"
                                :class="{ 'is-invalid': errors.product_title }"
                                placeholder="Product name"
                            />
                            <div v-if="errors.product_title" class="invalid-feedback">
                                {{ errors.product_title.join(', ') }}
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
                                    <span v-if="origin.iso_code"> ({{ origin.iso_code }})</span>
                                </option>
                            </select>
                            <div v-if="errors.origin_id" class="invalid-feedback">
                                {{ errors.origin_id.join(', ') }}
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Status</label>
                            <select
                                v-model="form.status"
                                class="form-select"
                                :class="{ 'is-invalid': errors.status }"
                            >
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="DISCONTINUED-UI">DISCONTINUED-UI</option>
                                <option value="DISCONTINUED-ARTISAN">DISCONTINUED-ARTISAN</option>
                                <option value="REPLACEMENT">REPLACEMENT</option>
                                <option value="REPLACEMENT & DISCONTINUED">REPLACEMENT & DISCONTINUED</option>
                                <option value="NEW CODE">NEW CODE</option>
                                <option value="FUTURE DISCONTINUED">FUTURE DISCONTINUED</option>
                                <option value="NEW TENTATIVE">NEW TENTATIVE</option>
                            </select>
                            <div v-if="errors.status" class="invalid-feedback">
                                {{ errors.status.join(', ') }}
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
                        <div class="col-md-12">
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
                            
                            <!-- Image Preview -->
                            <div v-if="imagePreviews.length > 0" class="mt-3">
                                <div class="row g-2">
                                    <div v-for="(preview, index) in imagePreviews" :key="index" class="col-md-2 col-sm-4 col-6">
                                        <div class="position-relative">
                                            <img
                                                :src="preview"
                                                alt="Preview"
                                                class="img-thumbnail"
                                                style="width: 100%; height: 150px; object-fit: cover;"
                                            />
                                            <button
                                                type="button"
                                                class="btn btn-sm btn-danger position-absolute top-0 end-0 m-1"
                                                @click="removeImagePreview(index)"
                                                title="Remove"
                                            >
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex justify-content-end mt-4">
                        <RouterLink :to="{ name: 'products-list' }" class="btn btn-secondary me-2">
                            Cancel
                        </RouterLink>
                        <button class="btn btn-primary" type="submit" :disabled="isSubmitting">
                            <Loader v-if="isSubmitting" class="me-2" />
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
import Loader from '@/components/Loader.vue';

const router = useRouter();
const lookups = reactive({
    brands: [],
    categories: [],
    formats: [],
    origins: [],
});

const form = reactive({
    product_title: '',
    global_code: '',
    description: '',
    benefits: '',
    pack_size: '',
    brand_id: '',
    category_id: '',
    format_id: '',
    origin_id: '',
    status: 'ACTIVE',
    images: [],
});

const imagePreviews = ref([]);
const errors = reactive({});
const isSubmitting = ref(false);
const alert = reactive({
    type: 'success',
    message: '',
});

const loadLookups = async () => {
    try {
        const response = await axios.get('/api/products/lookups');
        const data = response.data;
        
        // Ensure we have arrays
        lookups.brands = Array.isArray(data.brands) ? data.brands : [];
        lookups.categories = Array.isArray(data.categories) ? data.categories : [];
        lookups.formats = Array.isArray(data.formats) ? data.formats : [];
        lookups.origins = Array.isArray(data.origins) ? data.origins : [];
        
        console.log('Loaded lookups:', {
            brands: lookups.brands.length,
            categories: lookups.categories.length,
            formats: lookups.formats.length,
            origins: lookups.origins.length,
        });
    } catch (error) {
        if (error.response?.status === 401) {
            router.push({ name: 'login' });
            return;
        }
        console.error('Failed to load product lookups', error);
        console.error('Error response:', error.response?.data);
        alert.type = 'danger';
        alert.message = error.response?.data?.message || 'Failed to load dropdown options. Please refresh the page.';
    }
};

const handleImageSelection = (event) => {
    const files = Array.from(event.target.files);
    form.images = files;
    
    // Create previews
    imagePreviews.value = [];
    files.forEach((file) => {
        const reader = new FileReader();
        reader.onload = (e) => {
            imagePreviews.value.push(e.target.result);
        };
        reader.readAsDataURL(file);
    });
};

const removeImagePreview = (index) => {
    imagePreviews.value.splice(index, 1);
    form.images.splice(index, 1);
};

const resetForm = () => {
    form.product_title = '';
    form.global_code = '';
    form.description = '';
    form.benefits = '';
    form.pack_size = '';
    form.brand_id = '';
    form.category_id = '';
    form.format_id = '';
    form.origin_id = '';
    form.status = 'ACTIVE';
    form.images = [];
    imagePreviews.value = [];
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

    try {
        await axios.post('/api/products', payload, {
            headers: { 'Content-Type': 'multipart/form-data' },
        });
        alert.type = 'success';
        alert.message = 'Product created successfully.';
        resetForm();
        setTimeout(() => {
            router.push({ name: 'products-list' });
        }, 1500);
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
