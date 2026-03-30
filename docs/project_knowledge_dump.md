# Full Project Knowledge Dump

## 1. System Summary

ChatyAi is a full-stack AI web system that transforms user-uploaded images into detailed textual prompts suitable for image-generation use cases. It is implemented as a split architecture:

- Backend API in Laravel 13 (repository root).
- Frontend SPA in Vue 3 under Frontend/.

The backend owns business logic, validation, authentication, persistence, and AI provider communication. The frontend owns interaction workflows, local form/state handling, routing, and API consumption.

The system currently behaves as a product-ready prototype with real core functionality but incomplete production hardening.

## 2. Product Capabilities (End-User Perspective)

A user can:

1. Register an account.
2. Log in and receive API token-based access.
3. Upload an image (JPG/PNG constraints applied).
4. Receive a generated descriptive prompt based on the image.
5. Re-generate from the same selected image.
6. View historical generated prompts.
7. Filter/search/sort/paginate history records.
8. Copy generated prompts to clipboard.
9. Request password reset and complete reset.
10. Trigger email verification notification and verification flow.

## 3. Backend Runtime Architecture

### 3.1 Request entry and routing

- bootstrap/app.php wires web/api/console routes and health endpoint.
- API routes are in routes/api.php and include auth middleware + throttle middleware.
- Auth endpoints are loaded from routes/auth.php and include register/login/logout/forgot/reset/verification routes.

Important route shape:

- Protected, versioned resources under /api/v1:
    - posts (apiResource full CRUD)
    - prompt-generations (index + store)
- Auth endpoints under /api:
    - /register
    - /login
    - /logout
    - /forgot-password
    - /reset-password
    - verification routes

### 3.2 Controllers and responsibilities

#### Api/V1/PostController

- Handles CRUD for Post model.
- Uses StorePostRequest for validation.
- Uses PostResource for list serialization.
- Notable behavior: store currently sets author_id = 1 explicitly (hard-coded ownership).

#### Api/V1/PromptGenerationController

- Constructor-injects OpenAiService.
- index(): returns authenticated user prompt records with optional filters and pagination.
    - Supports search and mime_type filtering.
    - Includes sort handling logic (contains a bug in minus-prefix parsing).
- store():
    1. Validates incoming image using GeneratePromptRequest.
    2. Builds sanitized unique filename.
    3. Stores file in public disk uploads/images.
    4. Calls OpenAiService to generate descriptive prompt.
    5. Persists PromptGeneration row linked to authenticated user.
    6. Returns PromptGenerationResource.

#### Auth Controllers

- RegisteredUserController:
    - Validates registration data with strong password constraints.
    - Creates user and fires Registered event.
    - Calls Auth::login (session login semantics).
    - Returns no content.
- LoginController:
    - Validates credentials via LoginRequest.
    - Creates Sanctum token and returns JSON with user resource + token.
- LoginController destroy:
    - Deletes current access token and returns no content.
- Password reset + verification controllers:
    - Framework-style flows with JSON responses and redirect for verification completion.

### 3.3 Domain models and data mapping

#### User

- Traits: HasFactory, Notifiable, HasApiTokens.
- Fillable/Hidden via PHP attributes.
- Relations:
    - posts() hasMany Post via author_id.
    - PromptGenerations() hasMany PromptGeneration.

#### Post

- Fillable: title, content, author_id.
- Relation defined as user() belongsTo User via author_id.
- Naming mismatch with PostResource, which expects author relation.

#### PromptGeneration

- Fillable includes generated_prompt, image_path, original_file_name, file_size, mime_type, user_id.
- Relation: user() belongsTo User.

### 3.4 Validation layer

#### StorePostRequest

- title required string min 2.
- content required string.

#### GeneratePromptRequest

- Authorization requires authenticated user.
- image required.
- Must be image, mime jpeg/png/jpg.
- Max 10 MB.
- Dimension bounds 100x100 to 10000x10000.
- Custom user-readable validation messages.

### 3.5 Resource serialization layer

#### UserResource

- id, name, email, created_at, updated_at.

#### PostResource

- id, title, content, author_id, timestamps, author field.
- Risk: author field depends on relation name alignment.

#### PromptGenerationResource

- id, image_url (asset path from storage), prompt text, file metadata, timestamps.
- Optional user relation embedding via whenLoaded.

### 3.6 AI integration subsystem

#### OpenAiService

Process:

1. Reads uploaded file bytes.
2. Base64-encodes image data.
3. Gets MIME type.
4. Constructs OpenAI client from config services.openai.api_key.
5. Sends chat completion with:
    - system message describing assistant behavior.
    - user message containing image_url data URI + text instruction.
6. Returns first choice message content.

Model used: gpt-4o-mini.

Key characteristic:

- Synchronous call in request lifecycle. No queue or async offload.

## 4. Database and Schema Evolution

### Core tables relevant to app behavior

- users (framework + auth)
- personal_access_tokens (Sanctum)
- posts
- image_generations (initial)
- prompt_generations (renamed target table)

### Post table

- id, timestamps, title, content, author_id foreign key to users with cascade delete.

### Prompt generation table

Original migration creates image_generations with:

- generated_prompt (text)
- image_path (string)
- original_file_name (string)
- file_size (string)
- mime_type (string)
- user_id fk cascade

Later migration renames image_generations -> prompt_generations.

Observation:

- file_size is string, which complicates numeric ordering/aggregation.

## 5. Frontend Runtime Architecture

### 5.1 Bootstrapping

- main.js mounts App.vue and router.
- style.css defines global design tokens, themes, utilities, and layout primitives.

### 5.2 App shell

App.vue responsibilities:

- Global layout and tabs.
- Theme resolution and toggling (localStorage + data-theme attr).
- Authentication state sync using auth-token-changed event.
- Shared inline alert messaging.
- Conditional nav tabs based on token presence.

### 5.3 Router behavior

router/index.js defines routes:

- / (UploadPage) requires auth
- /history requires auth
- /login
- /register
- /password-reset/:token

Navigation guard:

- Redirects unauthenticated users from protected pages to login with next query.
- Redirects authenticated users away from auth pages to upload page.

### 5.4 Service layer

#### apiClient.js

- Centralized fetch wrappers:
    - apiGet
    - apiPostForm
    - apiPostJson
- Token persistence helpers:
    - getToken / setToken / clearToken
- Header building with Bearer token when present.
- URL construction with robust base URL parsing.
- Error normalization for readable frontend messages.

#### authService.js

- loginUser: calls /login on API root and stores token.
- registerAndLogin: calls /register then login.
- logoutUser: calls /logout then always clears local token.
- requestPasswordReset and resetPassword wrappers.

#### promptGenerationService.js

- uploadImageAndGeneratePrompt(file) posts multipart image.
- fetchPromptHistory retrieves paginated history and applies client-side sort.

### 5.5 Page-level feature modules

#### UploadPage.vue

- Handles file selection and dimension validation.
- Maintains preview URL with proper revoke cleanup.
- Handles generate/regenerate flow and messages.
- Uses GeneratedPromptCard to display result.

#### HistoryPage.vue

- Holds filter model (search, mime, perPage, sort value).
- Loads paginated history and handles loading/empty/error states.
- Supports copy from list items.

#### LoginPage.vue

- Performs login and redirect to intended route.
- Includes forgot password panel and submission flow.

#### RegisterPage.vue

- Submits registration then auto-login.

#### ResetPasswordPage.vue

- Reads route token, submits reset payload, redirects to login on success.

### 5.6 Reusable components

- AppHeader: brand, theme toggle, auth status, logout action.
- FileDropzone: file input UX.
- HistoryFilters: controlled filter form.
- HistoryTable: history list rendering + copy button.
- PaginationControls: prev/next navigation.
- GeneratedPromptCard: prompt display + copy.
- InlineAlert: standardized feedback block.

### 5.7 Utilities

- constants.js: API URLs, validation constants, options.
- validators.js: file and dimension validation + token presence check.
- formatters.js: byte/date display helpers.

## 6. Authentication and Security Model

### Actual model in use

- Bearer-token API auth via Sanctum personal access tokens.
- Frontend stores token in localStorage.
- Protected API routes use auth:sanctum middleware.

### Security controls present

- Request validation on auth and upload endpoints.
- API throttling via custom api limiter in AppServiceProvider (60/min by user/IP).
- Password rules enforce complexity and uncompromised checks at registration.

### Security gaps/risk points

- localStorage token storage increases XSS impact surface compared to HttpOnly cookie flow.
- No dedicated rate limits specifically for expensive AI generation endpoint.
- CORS is broad for paths and supports credentials; needs stricter production tuning.

## 7. Testing and Quality Signals

### Existing test infrastructure

- Pest configured and applied to feature tests with RefreshDatabase.
- PHPUnit config uses SQLite in-memory for speed.

### Existing test scope

- Mostly auth-related tests from scaffold.
- Minimal/nonexistent tests for core prompt-generation feature and OpenAI boundary.

### Current observed state from execution

Running php artisan test yields:

- 5 passing tests.
- 5 failing tests.

Main failure themes:

- Auth tests expect session-auth outcomes and no-content responses inconsistent with current token-based login response.
- Password reset notification assertions currently failing.

Implication:

- Test suite currently does not represent trustworthy release gate.

## 8. Deployment and Operations Readiness

### Present

- Environment-driven config and setup scripts.
- Laravel queue/cache/session drivers configured in .env.example (database defaults).

### Missing

- No CI pipeline definitions.
- No containerization artifacts.
- No explicit staging/production deployment manifests.
- No structured observability/monitoring setup.

## 9. Dependency and Build Landscape

### Backend dependency highlights

- laravel/framework
- laravel/sanctum
- dedoc/scramble
- openai-php/client
- pest + pest-laravel
- laravel/pint

### Frontend dependency highlights

- vue
- vue-router
- @vitejs/plugin-vue
- vite

### Build/run scripts

- composer setup script bootstraps environment/migration/frontend build (at backend root, though frontend package is in subfolder and may require adjustment depending on working directory expectations).
- frontend package has dev/build/preview scripts.

## 10. End-to-End Data Contracts

### Prompt generation POST request

- Path: /api/v1/prompt-generations
- Auth: Bearer token required
- Body: multipart form-data with image field

### Prompt generation response (normalized)

- id
- image_url
- generated_prompt
- original_file_name
- file_size
- mime_type
- created_at / updated_at
- optional user

### History GET request

- Path: /api/v1/prompt-generations
- Query options:
    - page
    - per_page
    - search
    - mime_type
    - sort (backend supports field format, frontend partly sorts client-side)

### Auth responses

- login returns user + token JSON payload.
- register returns no content.
- logout returns no content.

## 11. Engineering Assessment Narrative

If another engineer inherits this codebase, the key understanding is:

- Core product value is implemented and demonstrable.
- Structure is mostly maintainable and follows framework conventions.
- The largest quality issue is contract drift: code evolved toward token API behavior while tests and some assumptions stayed in scaffold-era semantics.
- The biggest production risks are reliability/cost controls around AI calls and absence of CI/deployment hardening.

The fastest path to a robust next release is:

1. Align tests with real auth/API contracts.
2. Fix ownership and relation consistency issues.
3. Harden AI endpoint with dedicated throttles and failure handling.
4. Add CI and basic observability.

## 12. Important Files and Their Roles (Quick Map)

Backend core:

- routes/api.php: protected API entrypoints for v1 resources.
- routes/auth.php: auth and password/email flows.
- app/Http/Controllers/Api/V1/PromptGenerationController.php: core business workflow.
- app/Services/OpenAiService.php: external AI provider integration.
- app/Http/Requests/GeneratePromptRequest.php: upload gatekeeping.
- app/Http/Resources/PromptGenerationResource.php: API output contract.
- app/Models/PromptGeneration.php: persistence model.
- app/Providers/AppServiceProvider.php: rate limiter and reset URL customization.

Frontend core:

- Frontend/src/router/index.js: route table and access rules.
- Frontend/src/services/apiClient.js: transport and error normalization.
- Frontend/src/services/authService.js: auth action wrappers.
- Frontend/src/services/promptGenerationService.js: prompt/history API calls.
- Frontend/src/pages/UploadPage.vue: upload and generation UX.
- Frontend/src/pages/HistoryPage.vue: retrieval/filter/pagination UX.
- Frontend/src/utils/validators.js: client-side upload validation.

## 13. Assumptions Made Explicitly

- The primary repository for evaluation is ChatyAi directory (contains .git and full app).
- OpenAI key is expected through OPENAI_API_KEY environment variable.
- Current deployment target is local/development unless additional infrastructure exists outside repository.
- Product intent is API-first frontend/backend split where Vue app communicates with Laravel API over HTTP.

These assumptions are based on code and config evidence, not external documentation claims.
