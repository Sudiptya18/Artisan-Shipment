<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <div>
                <h1 class="mt-2">Add Categories</h1>
            </div>
        </div>

        <div class="row">
            <!-- Insertion Section -->
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Insertion</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                            {{ alert.message }}
                        </div>
                        <form @submit.prevent="save">
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="form-label">Name:</label>
                                    <input
                                        v-model="form.category_name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.category_name }"
                                        placeholder="Enter category name"
                                        required
                                    />
                                    <div v-if="errors.category_name" class="invalid-feedback">
                                        {{ errors.category_name.join(', ') }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary me-2" :disabled="isSubmitting">
                                    <span
                                        v-if="isSubmitting"
                                        class="spinner-border spinner-border-sm me-2"
                                        role="status"
                                    ></span>
                                    Save
                                </button>
                                <button type="button" class="btn btn-secondary" @click="refresh">
                                    Refresh
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- List Section -->
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">List</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="text-center">Sl No.</th>
                                        <th class="text-center">Name</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="items.length === 0">
                                        <td colspan="3" class="text-center">No categories found</td>
                                    </tr>
                                    <tr v-for="(item, index) in items" :key="item.id">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td class="text-center">{{ item.category_name }}</td>
                                        <td class="text-center">
                                            <button
                                                class="btn btn-sm btn-primary me-1"
                                                @click="edit(item)"
                                            >
                                                Edit
                                            </button>
                                            <button
                                                class="btn btn-sm btn-danger"
                                                @click="confirmDelete(item)"
                                            >
                                                Delete
                                            </button>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import axios from 'axios';
import { onMounted, reactive, ref } from 'vue';
import { RouterLink } from 'vue-router';

const items = ref([]);
const form = reactive({
    category_name: '',
});
const errors = reactive({});
const isSubmitting = ref(false);
const editingId = ref(null);
const alert = reactive({
    type: 'success',
    message: '',
});

const loadItems = async () => {
    try {
        const response = await axios.get('/api/categories');
        items.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load categories', error);
        alert.type = 'danger';
        alert.message = 'Failed to load categories.';
    }
};

const save = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        if (editingId.value) {
            await axios.put(`/api/categories/${editingId.value}`, form);
            alert.message = 'Category updated successfully!';
        } else {
            await axios.post('/api/categories', form);
            alert.message = 'Category created successfully!';
        }
        alert.type = 'success';
        form.category_name = '';
        editingId.value = null;
        await loadItems();
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the errors below.';
        } else {
            alert.type = 'danger';
            alert.message = error.response?.data?.message || 'Failed to save category.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const edit = (item) => {
    form.category_name = item.category_name;
    editingId.value = item.id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const confirmDelete = async (item) => {
    if (!confirm(`Are you sure you want to delete "${item.category_name}"?`)) {
        return;
    }

    try {
        await axios.delete(`/api/categories/${item.id}`);
        alert.type = 'success';
        alert.message = 'Category deleted successfully!';
        await loadItems();
    } catch (error) {
        alert.type = 'danger';
        alert.message = error.response?.data?.message || 'Failed to delete category.';
    }
};

const refresh = () => {
    form.category_name = '';
    editingId.value = null;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);
    loadItems();
};

onMounted(() => {
    loadItems();
});
</script>

