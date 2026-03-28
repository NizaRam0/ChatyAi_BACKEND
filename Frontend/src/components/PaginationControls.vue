<script setup>
// Pagination controls for navigating paginated history responses.
const props = defineProps({
    currentPage: {
        type: Number,
        default: 1,
    },
    lastPage: {
        type: Number,
        default: 1,
    },
    total: {
        type: Number,
        default: 0,
    },
});

const emit = defineEmits(["page-change"]);

// Moves to previous page when available.
function prev() {
    if (props.currentPage <= 1) return;
    emit("page-change", props.currentPage - 1);
}

// Moves to next page when available.
function next() {
    if (props.currentPage >= props.lastPage) return;
    emit("page-change", props.currentPage + 1);
}
</script>

<template>
    <footer class="pager glass-card">
        <p class="muted">Total: {{ total }}</p>

        <div class="pager-controls">
            <button
                class="btn btn-ghost"
                type="button"
                :disabled="currentPage <= 1"
                @click="prev"
            >
                Previous
            </button>
            <span class="pager-current"
                >Page {{ currentPage }} / {{ lastPage }}</span
            >
            <button
                class="btn btn-ghost"
                type="button"
                :disabled="currentPage >= lastPage"
                @click="next"
            >
                Next
            </button>
        </div>
    </footer>
</template>

<style scoped>
.pager {
    margin-top: 0.8rem;
    padding: 0.75rem 1rem;
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 1rem;
}

.pager-controls {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.pager-current {
    font-weight: 600;
    color: var(--text-secondary);
}

@media (max-width: 620px) {
    .pager {
        flex-direction: column;
        align-items: stretch;
    }

    .pager-controls {
        justify-content: space-between;
    }
}
</style>
