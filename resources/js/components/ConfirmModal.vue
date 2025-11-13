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
                <div class="modal-header">
                    <h5 class="modal-title">{{ title }}</h5>
                    <button type="button" class="btn-close" @click="cancel" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>{{ message }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" @click="cancel">Cancel</button>
                    <button type="button" class="btn btn-danger" @click="confirm">OK</button>
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

