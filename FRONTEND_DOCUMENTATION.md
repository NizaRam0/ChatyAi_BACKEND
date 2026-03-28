# ChatAI Image Generator – Frontend Steps

## 1 -> Project Setup

- Framework used: Vue 3 with Vite.
- Navigation: Vue Router with 2 pages.
- Authentication pages: Login and Register with auto token handling.
- Styling: Plain CSS (scoped in components + global design tokens in main stylesheet).
- API strategy: Frontend consumes existing backend endpoints as-is using a small service layer.
- Folder structure:
    - Frontend/src/components: Reusable UI parts (header, dropzone, filters, list, pagination, alerts).
    - Frontend/src/pages: Main screens (UploadPage and HistoryPage).
    - Frontend/src/services: HTTP client and prompt-generation API methods.
    - Frontend/src/services/authService.js: Login, register-then-login, and logout API calls.
    - Frontend/src/utils: Validation, formatting, constants.
    - Frontend/src/router: Route definitions.

## 2 -> UI Design

- Upload Page:
    - File dropzone with drag and drop + button picker.
    - Live image preview.
    - Validation and status feedback.
    - Generate and Regenerate actions.
    - Prompt result panel with copy action.
- History Page:
    - Filter/search/sort control bar.
    - Paginated history list with metadata chips.
    - Copy action per item.
- Shared components:
    - AppHeader: Page navigation + access token management.
    - InlineAlert: Reusable feedback messaging.
- Why this component split:
    - Keeps business logic in pages/services while reusable UI stays simple and focused.

## 3 -> Image Upload Flow

1. User drops/picks image in FileDropzone.
2. Upload page validates file type and size using utils/validators.
3. If valid, frontend generates local preview URL with URL.createObjectURL.
4. Generate button is enabled only when:
    - File is valid
    - No active request
    - Bearer token exists
5. On submit, frontend sends multipart/form-data with field image.
6. On success, generated prompt is displayed in GeneratedPromptCard.
7. User can copy prompt or click Regenerate to re-send the same file.

Validation logic:

- Allowed MIME types: image/jpeg, image/png.
- Max size: 10 MB (aligned with backend validation max:10240).
- Dimensions: 100x100 to 10000x10000 checked on frontend before submit.
- Validation errors shown immediately before API call.

Preview handling:

- Preview URL is revoked when replaced or when page unmounts to prevent memory leaks.

## 4 -> API Integration

- Base URL:
    - Default: http://127.0.0.1:8000/api/v1
    - Can be overridden with VITE_API_BASE_URL.
- Endpoints used:
    - POST /prompt-generations for image upload and generation.
    - GET /prompt-generations for history list.
    - POST /api/login for login.
    - POST /api/register for register.
    - POST /api/logout for logout.
- Request details:
    - GET uses query params for page, per_page, search, mime_type.
    - POST uses FormData and image field.
- Response handling:
    - Supports wrapped/unwrapped payload patterns.
    - Parses Laravel validation errors and server message into one readable frontend message.
- Error handling:
    - Validation errors (422) displayed directly.
    - Unauthorized/forbidden/server errors surfaced via alert messages.
    - Empty response lists show explicit empty-state text.

## 5 -> State Management

- Pattern: Local page-level reactive state via Vue refs/computed.
- UploadPage state:
    - selectedFile, previewUrl, latestResult, loading, success/error messages.
- HistoryPage state:
    - filter model, history list, pagination meta, loading, messages.
- Auth token state:
    - Persisted in localStorage through apiClient helpers.
    - Header listens to auth-token-changed event to update authenticated UI state immediately.
    - Router guards block upload/history when not authenticated.
- Why local state:
    - Scope is small and page-specific, so global store is unnecessary overhead.

## 6 -> History Page

Pagination logic:

- Frontend sends page + per_page query params.
- Frontend reads meta.current_page, meta.last_page, meta.total, meta.per_page from response.
- Previous/Next controls are disabled at limits.

Search implementation:

- Search value sent as search query param.
- Triggered through filter form submit.

Filtering and sorting:

- MIME filter sent as mime_type query param.
- Sorting options in UI include created_at, file_size, and mime_type.
- Sorting is applied client-side on fetched page data to avoid backend dependency changes.

## 7 -> UX Improvements

- Clear loading indicator while requests are in progress.
- Generate button disabled during loading and invalid states.
- Duplicate submissions prevented by loading gate.
- Instant feedback messages for success, copy action, and errors.
- Responsive layouts for desktop/mobile with simple breakpoints.
- Accessibility basics:
    - Semantic labels
    - Live status alerts
    - Clear button states

## 8 -> Challenges & Decisions

- Challenge: Register endpoint does not return API token directly.
    - Decision: Implement register-then-login flow automatically in authService so frontend always receives/stores token without manual user action.
- Challenge: Sorting requirements without guaranteed backend sort params.
    - Decision: Keep API read-only; fetch paginated data and apply sorting client-side.
- Challenge: Different possible API payload wrappers.
    - Decision: Normalize response in service layer (response.data or response fallback).
- Challenge: Keep UI clean while showing many controls.
    - Decision: Use compact filter row and reusable components to reduce clutter.
