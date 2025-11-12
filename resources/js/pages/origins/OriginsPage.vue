<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <div>
                <h1 class="mt-2">Add Country of Origin</h1>
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
                                        v-model="form.origin_name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.origin_name }"
                                        placeholder="Enter country name"
                                        required
                                    />
                                    <div v-if="errors.origin_name" class="invalid-feedback">
                                        {{ errors.origin_name.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ISO Code (Optional):</label>
                                    <input
                                        v-model="form.iso_code"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.iso_code }"
                                        placeholder="e.g. BD, US, UK"
                                        maxlength="8"
                                    />
                                    <div v-if="errors.iso_code" class="invalid-feedback">
                                        {{ errors.iso_code.join(', ') }}
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
                                        <th class="text-center">ISO Code</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="items.length === 0">
                                        <td colspan="4" class="text-center">No origins found</td>
                                    </tr>
                                    <tr v-for="(item, index) in items" :key="item.id">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td class="text-center">{{ item.origin_name }}</td>
                                        <td class="text-center">{{ item.iso_code || '-' }}</td>
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
    origin_name: '',
    iso_code: '',
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
        const response = await axios.get('/api/origins');
        items.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load origins', error);
        alert.type = 'danger';
        alert.message = 'Failed to load origins.';
    }
};

const save = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        if (editingId.value) {
            await axios.put(`/api/origins/${editingId.value}`, form);
            alert.message = 'Origin updated successfully!';
        } else {
            await axios.post('/api/origins', form);
            alert.message = 'Origin created successfully!';
        }
        alert.type = 'success';
        form.origin_name = '';
        form.iso_code = '';
        editingId.value = null;
        await loadItems();
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the errors below.';
        } else {
            alert.type = 'danger';
            alert.message = error.response?.data?.message || 'Failed to save origin.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const edit = (item) => {
    form.origin_name = item.origin_name;
    form.iso_code = item.iso_code || '';
    editingId.value = item.id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const confirmDelete = async (item) => {
    if (!confirm(`Are you sure you want to delete "${item.origin_name}"?`)) {
        return;
    }

    try {
        await axios.delete(`/api/origins/${item.id}`);
        alert.type = 'success';
        alert.message = 'Origin deleted successfully!';
        await loadItems();
    } catch (error) {
        alert.type = 'danger';
        alert.message = error.response?.data?.message || 'Failed to delete origin.';
    }
};

const refresh = () => {
    form.origin_name = '';
    form.iso_code = '';
    editingId.value = null;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);
    loadItems();
};

onMounted(() => {
    loadItems();
});
</script>

