<template>
    <div
        v-if="show"
        class="modal fade show"
        :class="{ 'd-block': show }"
        tabindex="-1"
        role="dialog"
        style="background-color: rgba(0, 0, 0, 0.5);"
        @click.self="close"
    >
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-body text-center p-5">
                    <div class="failed-animation mb-4">
                        <div class="failed-icon">
                            <i class="fas fa-times"></i>
                        </div>
                    </div>
                    <h5 class="mb-3">{{ title }}</h5>
                    <p class="text-muted mb-4">{{ message }}</p>
                    <button class="btn btn-danger" @click="close">OK</button>
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
        default: 'Failed!',
    },
    message: {
        type: String,
        default: 'Operation failed. Please try again.',
    },
});

const emit = defineEmits(['update:show', 'close']);

const close = () => {
    emit('update:show', false);
    emit('close');
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
.failed-animation {
    margin: 0 auto;
}

.failed-icon {
    width: 80px;
    height: 80px;
    margin: 0 auto;
    position: relative;
    border-radius: 50%;
    border: 4px solid #f44336;
    display: flex;
    align-items: center;
    justify-content: center;
    animation: scale-in 0.3s ease-out;
}

.failed-icon i {
    font-size: 40px;
    color: #f44336;
    animation: rotate-in 0.5s ease-out;
}

@keyframes scale-in {
    0% {
        transform: scale(0);
    }
    50% {
        transform: scale(1.1);
    }
    100% {
        transform: scale(1);
    }
}

@keyframes rotate-in {
    0% {
        transform: rotate(-180deg);
        opacity: 0;
    }
    100% {
        transform: rotate(0deg);
        opacity: 1;
    }
}
</style>

