<template>
    <div class="container-fluid px-4">
        <div class="d-flex align-items-center justify-content-between my-4">
            <div>
                <h1 class="mt-2">HS Code</h1>
            </div>
        </div>

        <div class="row">
            <!-- Insertion Section -->
            <div class="col-lg-12 mb-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Create HS Code</h5>
                    </div>
                    <div class="card-body">
                        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                            {{ alert.message }}
                        </div>
                        <form @submit.prevent="save">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <input
                                        v-model="form.hscode"
                                        type="text"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.hscode }"
                                        placeholder="Enter HS Code"
                                        required
                                    />
                                    <div v-if="errors.hscode" class="invalid-feedback">
                                        {{ errors.hscode.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <textarea
                                        v-model="form.description"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.description }"
                                        placeholder="Enter Description"
                                        rows="2"
                                    ></textarea>
                                    <div v-if="errors.description" class="invalid-feedback">
                                        {{ errors.description.join(', ') }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.cd"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.cd }"
                                        placeholder="CD (Custom Duty)"
                                    />
                                    <div v-if="errors.cd" class="invalid-feedback">
                                        {{ errors.cd.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.rd"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.rd }"
                                        placeholder="RD (Regulatory Duty)"
                                    />
                                    <div v-if="errors.rd" class="invalid-feedback">
                                        {{ errors.rd.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.sd"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.sd }"
                                        placeholder="SD (Supplementary Duty)"
                                    />
                                    <div v-if="errors.sd" class="invalid-feedback">
                                        {{ errors.sd.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.vat"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.vat }"
                                        placeholder="VAT"
                                    />
                                    <div v-if="errors.vat" class="invalid-feedback">
                                        {{ errors.vat.join(', ') }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.ait"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.ait }"
                                        placeholder="AIT (Advance Income Tax)"
                                    />
                                    <div v-if="errors.ait" class="invalid-feedback">
                                        {{ errors.ait.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.at"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.at }"
                                        placeholder="AT (Advance Tax)"
                                    />
                                    <div v-if="errors.at" class="invalid-feedback">
                                        {{ errors.at.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.exd"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.exd }"
                                        placeholder="EXD (Export Development Duty)"
                                    />
                                    <div v-if="errors.exd" class="invalid-feedback">
                                        {{ errors.exd.join(', ') }}
                                    </div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.tti"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.tti }"
                                        placeholder="TTI (Turnover Tax on Import)"
                                    />
                                    <div v-if="errors.tti" class="invalid-feedback">
                                        {{ errors.tti.join(', ') }}
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-3 mb-3">
                                    <input
                                        v-model.number="form.min_ass_value"
                                        type="number"
                                        step="0.01"
                                        class="form-control"
                                        :class="{ 'is-invalid': errors.min_ass_value }"
                                        placeholder="Minimum Assessed Value"
                                    />
                                    <div v-if="errors.min_ass_value" class="invalid-feedback">
                                        {{ errors.min_ass_value.join(', ') }}
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
                                        <th class="text-center">HS Code</th>
                                        <th class="text-center">Description</th>
                                        <th class="text-center">CD</th>
                                        <th class="text-center">RD</th>
                                        <th class="text-center">SD</th>
                                        <th class="text-center">VAT</th>
                                        <th class="text-center">AIT</th>
                                        <th class="text-center">AT</th>
                                        <th class="text-center">EXD</th>
                                        <th class="text-center">TTI</th>
                                        <th class="text-center">Min Ass Value</th>
                                        <th class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-if="items.length === 0">
                                        <td colspan="13" class="text-center">No HS codes found</td>
                                    </tr>
                                    <tr v-for="(item, index) in items" :key="item.id">
                                        <td class="text-center">{{ index + 1 }}</td>
                                        <td class="text-center">{{ item.hscode }}</td>
                                        <td class="text-center">{{ item.description || '-' }}</td>
                                        <td class="text-center">{{ item.cd ? parseFloat(item.cd).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.rd ? parseFloat(item.rd).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.sd ? parseFloat(item.sd).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.vat ? parseFloat(item.vat).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.ait ? parseFloat(item.ait).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.at ? parseFloat(item.at).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.exd ? parseFloat(item.exd).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.tti ? parseFloat(item.tti).toFixed(2) : '-' }}</td>
                                        <td class="text-center">{{ item.min_ass_value ? parseFloat(item.min_ass_value).toFixed(2) : '-' }}</td>
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
import Swal from 'sweetalert2';
import Loader from '@/components/Loader.vue';

const items = ref([]);
const form = reactive({
    hscode: '',
    description: '',
    cd: null,
    rd: null,
    sd: null,
    vat: null,
    ait: null,
    at: null,
    exd: null,
    tti: null,
    min_ass_value: null,
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
        const response = await axios.get('/api/hscodes');
        items.value = response.data.data || [];
    } catch (error) {
        console.error('Failed to load HS codes', error);
        alert.type = 'danger';
        alert.message = 'Failed to load HS codes.';
    }
};

const save = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        const formData = { ...form };
        // Convert empty strings to null for numeric fields
        Object.keys(formData).forEach(key => {
            if (formData[key] === '' || formData[key] === null) {
                formData[key] = null;
            }
        });

        if (editingId.value) {
            await axios.put(`/api/hscodes/${editingId.value}`, formData);
            alert.message = 'HS Code updated successfully!';
        } else {
            await axios.post('/api/hscodes', formData);
            alert.message = 'HS Code created successfully!';
        }
        alert.type = 'success';
        resetForm();
        await loadItems();
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the errors below.';
        } else {
            alert.type = 'danger';
            alert.message = error.response?.data?.message || 'Failed to save HS code.';
        }
    } finally {
        isSubmitting.value = false;
    }
};

const edit = (item) => {
    form.hscode = item.hscode;
    form.description = item.description || '';
    form.cd = item.cd ? parseFloat(item.cd) : null;
    form.rd = item.rd ? parseFloat(item.rd) : null;
    form.sd = item.sd ? parseFloat(item.sd) : null;
    form.vat = item.vat ? parseFloat(item.vat) : null;
    form.ait = item.ait ? parseFloat(item.ait) : null;
    form.at = item.at ? parseFloat(item.at) : null;
    form.exd = item.exd ? parseFloat(item.exd) : null;
    form.tti = item.tti ? parseFloat(item.tti) : null;
    form.min_ass_value = item.min_ass_value ? parseFloat(item.min_ass_value) : null;
    editingId.value = item.id;
    window.scrollTo({ top: 0, behavior: 'smooth' });
};

const confirmDelete = async (item) => {
    const result = await Swal.fire({
        title: 'Are you sure?',
        text: `Do you want to delete HS Code "${item.hscode}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Yes, delete it!',
        cancelButtonText: 'Cancel'
    });

    if (result.isConfirmed) {
        try {
            await axios.delete(`/api/hscodes/${item.id}`);
            Swal.fire({
                title: 'Deleted!',
                text: 'HS Code has been deleted successfully.',
                icon: 'success'
            });
            await loadItems();
        } catch (error) {
            Swal.fire({
                title: 'Error!',
                text: error.response?.data?.message || 'Failed to delete HS code.',
                icon: 'error'
            });
        }
    }
};

const resetForm = () => {
    form.hscode = '';
    form.description = '';
    form.cd = null;
    form.rd = null;
    form.sd = null;
    form.vat = null;
    form.ait = null;
    form.at = null;
    form.exd = null;
    form.tti = null;
    form.min_ass_value = null;
    editingId.value = null;
};

const refresh = () => {
    resetForm();
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);
    loadItems();
};

onMounted(() => {
    loadItems();
});
</script>

