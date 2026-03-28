<script setup>
// FileDropzone combines drag-and-drop and native picker for image selection.
import { ref } from "vue";

const emit = defineEmits(["file-selected"]);
const fileInput = ref(null);
const dragging = ref(false);

// Forwards file from list and emits the first selected file.
function emitFile(fileList) {
    const file = fileList?.[0];
    if (!file) return;
    emit("file-selected", file);
}

// Handles native input picker change event.
function onInputChange(event) {
    emitFile(event.target.files);
}

// Handles drop event and prevents browser from opening the file.
function onDrop(event) {
    event.preventDefault();
    dragging.value = false;
    emitFile(event.dataTransfer.files);
}

// Opens hidden file input when user clicks the dropzone button.
function openPicker() {
    fileInput.value?.click();
}
</script>

<template>
    <section
        class="dropzone"
        :class="{ dragging }"
        @dragover.prevent="dragging = true"
        @dragleave.prevent="dragging = false"
        @drop="onDrop"
    >
        <p class="dropzone-title">Drop image here</p>
        <p class="muted">or choose a file manually</p>

        <button class="btn btn-secondary" type="button" @click="openPicker">
            Choose Image
        </button>

        <input
            ref="fileInput"
            class="hidden-input"
            type="file"
            accept="image/png,image/jpeg"
            @change="onInputChange"
        />
    </section>
</template>

<style scoped>
.dropzone {
    border: 1.5px dashed var(--stroke-strong);
    background: linear-gradient(180deg, var(--surface), var(--surface-muted));
    border-radius: 16px;
    padding: 1.2rem;
    text-align: center;
    transition:
        transform 180ms ease,
        border-color 180ms ease,
        background 180ms ease;
}

.dropzone.dragging {
    transform: scale(1.02);
    border-color: var(--accent);
    box-shadow: 0 0 0 1px rgba(124, 58, 237, 0.25);
    background: linear-gradient(
        180deg,
        rgba(124, 58, 237, 0.14),
        rgba(124, 58, 237, 0.05)
    );
}

.dropzone-title {
    font-family: var(--font-heading);
    margin: 0;
    font-size: 1.2rem;
}

.hidden-input {
    display: none;
}
</style>
