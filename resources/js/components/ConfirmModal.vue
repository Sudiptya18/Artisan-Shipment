<template>
    <div
        v-if="show"
        class="modal fade show"
        :class="{ 'd-block': show }"
        tabindex="-1"
        role="dialog"
        style="background-color: rgba(0, 0, 0, 0.5);"
        @click.self="cancel"
    >
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center py-4">
                    <!-- Animated Icon -->
                    <div class="warning-icon-container mb-3">
                        <div class="warning-icon">
                            <i class="fas fa-exclamation"></i>
                        </div>
                    </div>
                    <!-- Message -->
                    <h5 class="mb-4 fw-bold text-dark">{{ message }}</h5>
                    <!-- Buttons -->
                    <div class="d-flex justify-content-center gap-3">
                        <button type="button" class="btn btn-primary px-4" @click="confirm">Yes</button>
                        <button type="button" class="btn btn-danger px-4" @click="cancel">Cancel</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { watch } from 'vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        default: 'Confirm Delete',
    },
    message: {
        type: String,
        default: 'Are you sure you want to delete this item?',
    },
});

const emit = defineEmits(['update:show', 'confirm', 'cancel']);

const confirm = () => {
    emit('update:show', false);
    emit('confirm');
};

const cancel = () => {
    emit('update:show', false);
    emit('cancel');
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        document.body.classList.add('modal-open');
    } else {
        document.body.classList.remove('modal-open');
    }
});
</script>

<style scoped>
.warning-icon-container {
    display: flex;
    justify-content: center;
    align-items: center;
}

.warning-icon {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    border: 3px solid #ffa500;
    display: flex;
    justify-content: center;
    align-items: center;
    background-color: #fff;
    animation: pulse 2s infinite;
}

.warning-icon i {
    font-size: 40px;
    color: #ffa500;
}

@keyframes pulse {
    0% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 165, 0, 0.7);
    }
    50% {
        transform: scale(1.05);
        box-shadow: 0 0 0 10px rgba(255, 165, 0, 0);
    }
    100% {
        transform: scale(1);
        box-shadow: 0 0 0 0 rgba(255, 165, 0, 0);
    }
}

.modal-content {
    border-radius: 0.5rem;
    border: none;
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15);
}

.modal-body {
    padding: 2rem;
}
</style>

