<template>
    <div class="d-flex align-items-center justify-content-center min-vh-100 bg-light">
        <div class="col-lg-5">
            <div class="card shadow-lg border-0 rounded-lg mt-5">
                <div class="card-header text-center bg-white border-0 pt-4">
                    <img :src="logoUrl" alt="Logo" class="mb-3" style="max-height: 150px;" />
                    <h3 class="text-center font-weight-light my-4">Change Password</h3>
                </div>
                <div class="card-body px-5 pb-5">
                    <form @submit.prevent="submit">
                        <div v-if="alert.message" :class="`alert alert-${alert.type}`" role="alert">
                            {{ alert.message }}
                        </div>
                        <div class="form-floating mb-3">
                            <input
                                v-model="form.password"
                                class="form-control"
                                id="inputPassword"
                                type="password"
                                placeholder="Enter new password"
                                :class="{ 'is-invalid': errors.password }"
                                required
                                autocomplete="new-password"
                            />
                            <label for="inputPassword">New Password *</label>
                            <div v-if="errors.password" class="invalid-feedback">
                                {{ errors.password.join(', ') }}
                            </div>
                        </div>
                        <div class="form-floating mb-3">
                            <input
                                v-model="form.password_confirmation"
                                class="form-control"
                                id="inputPasswordConfirm"
                                type="password"
                                placeholder="Confirm new password"
                                :class="{ 'is-invalid': errors.password_confirmation }"
                                required
                                autocomplete="new-password"
                            />
                            <label for="inputPasswordConfirm">Confirm Password *</label>
                            <div v-if="errors.password_confirmation" class="invalid-feedback">
                                {{ errors.password_confirmation.join(', ') }}
                            </div>
                        </div>
                        <div class="d-flex justify-content-center mt-4">
                            <button class="btn btn-teal text-white w-100" type="submit" :disabled="isSubmitting" style="background-color: #20b2aa; border-color: #20b2aa;">
                                <Loader v-if="isSubmitting" class="me-2" />
                                Change Password
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Success Modal -->
    <SuccessModal
        v-model:show="showSuccessModal"
        title="Success!"
        message="Password changed successfully."
        @close="handleSuccessClose"
    />
</template>

<script setup>
import axios from 'axios';
import { reactive, ref, onMounted } from 'vue';
import SuccessModal from '@/components/SuccessModal.vue';
import Loader from '@/components/Loader.vue';

const logoUrl = '/assets/img/logo.png';

const form = reactive({
    password: '',
    password_confirmation: '',
});

const errors = reactive({});
const isSubmitting = ref(false);
const showSuccessModal = ref(false);
const alert = reactive({
    type: 'success',
    message: '',
});

const submit = async () => {
    isSubmitting.value = true;
    alert.message = '';
    Object.keys(errors).forEach((key) => delete errors[key]);

    try {
        await axios.post('/api/auth/change-password', form);
        showSuccessModal.value = true;
        
        // Reset form
        form.password = '';
        form.password_confirmation = '';
    } catch (error) {
        if (error.response?.status === 422) {
            Object.assign(errors, error.response.data.errors);
            alert.type = 'danger';
            alert.message = 'Please correct the highlighted fields.';
        } else if (error.response?.status === 401) {
            alert.type = 'danger';
            alert.message = 'Unauthorized. Please login again.';
        } else {
            alert.type = 'danger';
            alert.message = error.response?.data?.message || 'Failed to change password.';
            console.error(error);
        }
    } finally {
        isSubmitting.value = false;
    }
};

const handleSuccessClose = () => {
    showSuccessModal.value = false;
};
</script>

<style scoped>
.min-vh-100 {
    min-height: 100vh;
}
</style>

