<script setup>
// Filter panel for history query parameters and sorting preferences.
import { PER_PAGE_OPTIONS, SORT_OPTIONS } from "../utils/constants";

const model = defineModel({
    type: Object,
    required: true,
});

const emit = defineEmits(["search"]);

// Emits search event so parent page triggers API refresh.
function submitFilters() {
    emit("search");
}
</script>

<template>
    <form class="filters glass-card" @submit.prevent="submitFilters">
        <div class="field">
            <label for="search">Search Prompt</label>
            <input
                id="search"
                v-model="model.search"
                class="input"
                type="text"
                placeholder="night, portrait, marble..."
            />
        </div>

        <div class="field">
            <label for="mime">MIME Type</label>
            <input
                id="mime"
                v-model="model.mimeType"
                class="input"
                type="text"
                placeholder="image/png"
            />
        </div>

        <div class="field">
            <label for="per-page">Per Page</label>
            <select id="per-page" v-model.number="model.perPage" class="select">
                <option
                    v-for="value in PER_PAGE_OPTIONS"
                    :key="value"
                    :value="value"
                >
                    {{ value }}
                </option>
            </select>
        </div>

        <div class="field">
            <label for="sort">Sort By</label>
            <select id="sort" v-model="model.sortValue" class="select">
                <option
                    v-for="option in SORT_OPTIONS"
                    :key="option.value"
                    :value="option.value"
                >
                    {{ option.label }}
                </option>
            </select>
        </div>

        <button class="btn btn-primary" type="submit">Apply Filters</button>
    </form>
</template>

<style scoped>
.filters {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 0.8rem;
    padding: 1rem;
    margin-bottom: 0.8rem;
}

.field label {
    display: block;
    margin-bottom: 0.3rem;
    font-weight: 600;
    font-size: 0.84rem;
    color: var(--text-secondary);
}

.filters button {
    align-self: end;
}

@media (max-width: 940px) {
    .filters {
        grid-template-columns: 1fr;
    }
}
</style>
