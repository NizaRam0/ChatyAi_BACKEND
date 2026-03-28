<script setup>
// Shared header that provides navigation and auth session actions.
import { computed, onMounted, onUnmounted, ref } from "vue";
import { useRouter } from "vue-router";
import { getToken } from "../services/apiClient";
import { logoutUser } from "../services/authService";

const emit = defineEmits(["notify", "toggle-theme"]);
defineProps({
    theme: {
        type: String,
        default: "dark",
    },
});

const router = useRouter();

const authToken = ref(getToken());
const logoutLoading = ref(false);

// Reactive auth state driven by token storage.
const isAuthenticated = computed(() => Boolean(authToken.value));

// Syncs local token snapshot with storage changes.
function syncTokenState() {
    authToken.value = getToken();
}

// Revokes token on backend and redirects to login.
async function handleLogout() {
    logoutLoading.value = true;

    try {
        await logoutUser();
        emit("notify", "Logged out successfully.", "success");
        await router.push("/login");
    } catch (error) {
        emit("notify", error?.message || "Logout failed.", "error");
    } finally {
        logoutLoading.value = false;
    }
}

onMounted(() => {
    window.addEventListener("auth-token-changed", syncTokenState);
});

onUnmounted(() => {
    window.removeEventListener("auth-token-changed", syncTokenState);
});
</script>

<template>
    <header class="header-wrap">
        <div class="container header-inner glass-card">
            <div class="brand-area">
                <div class="brand-mark">
                    <img
                        src="/Prompty.png"
                        alt="prompty logo"
                        class="brand-logo"
                    />
                    <p class="brand-kicker">prompty</p>
                </div>
                <h1 class="brand-title">Image Prompt Generator</h1>
            </div>

            <div class="token-area">
                <p class="token-state" :class="isAuthenticated ? 'ok' : 'warn'">
                    {{
                        isAuthenticated ? "Authenticated" : "Not authenticated"
                    }}
                </p>
                <button
                    class="btn btn-ghost"
                    type="button"
                    @click="$emit('toggle-theme')"
                >
                    {{ theme === "dark" ? "Light Mode" : "Dark Mode" }}
                </button>
                <button
                    v-if="isAuthenticated"
                    class="btn btn-secondary"
                    type="button"
                    :disabled="logoutLoading"
                    @click="handleLogout"
                >
                    {{ logoutLoading ? "Logging out..." : "Logout" }}
                </button>
            </div>
        </div>
    </header>
</template>

<style scoped>
.header-wrap {
    position: sticky;
    top: 0;
    z-index: 30;
    padding: 1rem 0 0;
}

.header-inner {
    padding: 1rem;
    display: grid;
    grid-template-columns: 1fr auto;
    gap: 1rem;
    align-items: end;
}

.brand-kicker {
    margin: 0;
    font-weight: 600;
    color: var(--accent);
    text-transform: uppercase;
    letter-spacing: 0.08em;
    font-size: 0.75rem;
}

.brand-mark {
    display: inline-flex;
    align-items: center;
    gap: 0.45rem;
}

.brand-logo {
    width: 20px;
    height: 20px;
    object-fit: contain;
}

.brand-title {
    margin: 0.2rem 0 0;
    font-family: var(--font-heading);
    font-size: clamp(1rem, 2vw, 1.45rem);
}

.token-label {
    font-size: 0.85rem;
    font-weight: 600;
    color: var(--text-secondary);
}

.token-state {
    margin: 0 0 0.4rem;
    font-size: 0.85rem;
    font-weight: 700;
}

.token-state.ok {
    color: var(--status-success);
}

.token-state.warn {
    color: var(--status-warning);
}

.token-area {
    display: flex;
    align-items: center;
    justify-content: flex-end;
    flex-wrap: wrap;
    gap: 0.5rem;
}

@media (max-width: 940px) {
    .header-inner {
        grid-template-columns: 1fr;
        align-items: start;
    }

    .token-area {
        justify-content: flex-start;
    }
}
</style>
