<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <div>
                <h1 class="mt-2">Port</h1>
            </div>
        </div>

        <div class="row">
            <!-- Insertion Section -->
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Create Port</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                            {{ alert.message }}
                        </div>
                        <form @submit.prevent="save">
                            <div class="row">
                                <div class="col-md-6">
                                    <input
                                        v-model="form.name"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.name }"
                                        placeholder="Enter port name"
                                        required
                                    />
                                    <div v-if="errors.name" class="invalid-feedback">
                                        {{ errors.name.join(', ') }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3">
                                <button type="submit" class="btn btn-primary me-2" :disabled="isSubmitting">
                                    <Loader v-if="isSubmitting" class="me-2" />
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
                                        <td colspan="3" class="text-center">No ports found</td>
                                    </tr>
                                    <tr v-for="(item, index) in items" :key="item.id">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td class="text-center">{{ item.name }}</td>
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
import Loader from '@/components/Loader.vue';

const items = ref([]);
const form = reactive({
    name: '',
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
        const response = await axios.get('/api/ports');
        items.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load ports', error);
        alert.type = 'danger';
        alert.message = 'Failed to load ports.';
    }
};

const save = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        if (editingId.value) {
            await axios.put(`/api/ports/${editingId.value}`, form);
            alert.message = 'Port updated successfully!';
        } else {
            await axios.post('/api/ports', form);
            alert.message = 'Port created successfully!';
        }
        alert.type = 'success';
        form.name = '';
        editingId.value = null;
        await loadItems();
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the errors below.';
        } else {
            alert.type = 'danger';
            alert.message = error.response?.data?.message || 'Failed to save port.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const edit = (item) => {
    form.name = item.name;
    editingId.value = item.id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const confirmDelete = async (item) => {
    if (!confirm(`Are you sure you want to delete "${item.name}"?`)) {
        return;
    }

    try {
        await axios.delete(`/api/ports/${item.id}`);
        alert.type = 'success';
        alert.message = 'Port deleted successfully!';
        await loadItems();
    } catch (error) {
        alert.type = 'danger';
        alert.message = error.response?.data?.message || 'Failed to delete port.';
    }
};

const refresh = () => {
    form.name = '';
    editingId.value = null;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);
    loadItems();
};

onMounted(() => {
    loadItems();
});
</script>

