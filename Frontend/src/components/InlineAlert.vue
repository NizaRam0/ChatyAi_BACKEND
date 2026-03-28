<script setup>
// Reusable single-line alert for user feedback.
defineProps({
    message: {
        type: String,
        default: "",
    },
    type: {
        type: String,
        default: "info",
    },
});
</script>

<template>
    <p class="alert" :class="`alert-${type}`" role="status" aria-live="polite">
        {{ message }}
    </p>
</template>

<style scoped>
.alert {
    margin: 0 0 0.8rem;
    border-radius: 10px;
    padding: 0.65rem 0.75rem;
    border: 1px solid;
    font-size: 0.92rem;
}

.alert-info {
    background: rgba(124, 58, 237, 0.12);
    color: var(--text-soft);
    border-color: rgba(124, 58, 237, 0.35);
}

.alert-success {
    background: rgba(125, 211, 252, 0.12);
    color: var(--status-success);
    border-color: rgba(125, 211, 252, 0.38);
}

.alert-error {
    background: rgba(248, 113, 113, 0.12);
    color: var(--status-error);
    border-color: rgba(248, 113, 113, 0.42);
}
</style>
