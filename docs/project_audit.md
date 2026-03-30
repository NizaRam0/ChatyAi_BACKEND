# Project Audit

## Project Overview

### What the application is

ChatyAi is a full-stack web application composed of:

- A Laravel 13 backend API (PHP 8.3) handling authentication, persistence, validation, and AI integration.
- A Vue 3 single-page frontend (Vite) for user interaction.

It is an image-to-prompt generation product: users upload an image, the backend sends image content to OpenAI Vision-capable chat completion, and the system returns a detailed text prompt suitable for image-generation workflows.

### Purpose and target users

Primary users are creators and prompt engineers who want to:

- Reverse-engineer image style/composition from existing images.
- Generate reusable prompt text quickly.
- Manage a searchable/paginated history of generated prompts.

### Core functionality

- Token-based user registration/login/logout using Laravel Sanctum personal access tokens.
- Upload image endpoint with server-side validation and persistent storage.
- OpenAI service integration using base64-encoded image payload.
- Prompt generation history with filtering, sorting controls, and pagination.
- Password reset and email verification flows.

### Domain/business context inferred from code

The product sits in AI creator tooling. It appears portfolio/prototype-stage rather than production SaaS, but with architecture choices (versioned API, resources, form requests, auth middleware, migration history) that indicate intent to evolve into a maintainable product.

## Tech Stack

### Languages

- PHP 8.3
- JavaScript (ES modules)
- HTML/CSS

### Backend framework and libraries

- Laravel 13
- Laravel Sanctum (token authentication)
- Dedoc Scramble (OpenAPI/schema generation via attributes)
- OpenAI PHP client (openai-php/client)
- Laravel Breeze (API/auth scaffolding)

### Frontend framework and libraries

- Vue 3.5
- Vue Router 5
- Vite 8
- Plain CSS with global tokens and scoped component styles

### APIs and communication style

- REST-style HTTP JSON API
- Versioned endpoints under /api/v1 for domain resources
- Auth endpoints under /api (register/login/logout/forgot/reset)
- Multipart upload for image submission

### Database and ORM

- Default local database: SQLite
- Eloquent ORM models and relationships
- Migration-driven schema management
- Schemas include users, personal access tokens, posts, and prompt_generations

### Validation and serialization

- Form Request classes for input validation
- API Resource classes (PostResource, PromptGenerationResource, UserResource)

### State management and frontend architecture

- Local component/page state via Vue refs/computed
- No global store (Pinia/Vuex not used)
- Token persistence via localStorage and window event synchronization

### Build/dev tooling

- Composer scripts for setup/dev/test lifecycle
- Laravel Pint present (code style tool)
- Pest + PHPUnit test runner

### Testing stack

- Pest 4 with pest-plugin-laravel
- Feature tests mostly focused on auth scaffolding
- In-memory SQLite testing configuration

### CI/CD and deployment

- No CI workflow files found
- No Dockerfile/compose found
- No explicit hosting target configuration found

## Architecture

### Architectural style

- Backend: Laravel MVC + layered service abstraction for AI integration.
- Frontend: Component-driven SPA with service-layer API wrappers.
- Overall: Client-server architecture with clear API boundary.

### Backend organization

- routes/api.php and routes/auth.php define API contracts.
- Controllers orchestrate request handling.
- Requests centralize validation rules.
- Services encapsulate external API logic (OpenAiService).
- Models capture domain entities and relationships.
- Resources normalize API response payloads.
- Middleware contains custom email verification policy.

### Frontend organization

- pages/ holds screen-level flows (upload/history/auth).
- components/ holds reusable UI parts.
- services/ encapsulates HTTP and domain API methods.
- utils/ centralizes constants, formatters, validators.
- router/ enforces auth-aware navigation.

### Separation of concerns evaluation

Good separation in many places:

- Backend validation is not mixed into controllers.
- OpenAI integration is moved to service class.
- Frontend HTTP transport is abstracted from pages.

However, there are notable leaks:

- Sort parameter handling inconsistency between frontend and backend.
- Some resource/model relation naming mismatches reduce contract clarity.
- Auth tests are out-of-sync with current token API behavior.

### Dependency management

- Composer manages backend dependencies cleanly.
- Frontend dependencies are intentionally minimal.
- Environment-driven configuration for sensitive keys is used.

## Design Patterns

### Patterns identified

#### 1. MVC (Backend)

- Models: User, Post, PromptGeneration.
- Controllers: PostController, PromptGenerationController, auth controllers.
- Routing and requests follow Laravel conventions.

Correctness: Mostly correct implementation. Convention drift exists (relation naming inconsistency).

#### 2. Service Layer

- OpenAiService isolates OpenAI client construction and prompt generation call.

Correctness: Good direction for external integration boundary. Could be improved with interface-based dependency inversion and better resilience handling.

#### 3. Resource/Serializer Pattern

- JsonResource classes provide response shaping.

Correctness: Good for API consistency. Some implementation mismatch (PostResource references author relation not defined on model).

#### 4. Form Object / Request Validation

- GeneratePromptRequest and StorePostRequest encapsulate validation.

Correctness: Proper use of Laravel FormRequest. Constraints are meaningful and aligned with frontend validation rules.

#### 5. Guarded Routing / Policy-like middleware

- auth:sanctum and custom verified middleware alias.

Correctness: Auth middleware is used correctly on API group. Verified middleware exists but is not actively applied to critical routes.

#### 6. Frontend Service Facade

- authService and promptGenerationService simplify API calls for pages.

Correctness: Strong readability and reuse benefits. API contract assumptions should be continuously aligned with backend responses.

## Strengths

### Engineering strengths

- Clear API segmentation and endpoint versioning for domain resources.
- Thoughtful use of Laravel FormRequest and JsonResource for contract hygiene.
- OpenAI integration extracted into dedicated service, improving maintainability.
- End-to-end feature completeness for core value proposition (upload -> generate -> history).
- Consistent frontend componentization and readable state management.
- Frontend UX includes practical quality details: drag-and-drop, inline alerts, copy-to-clipboard, pagination, filter controls, responsive layout.
- Validation parity between frontend and backend on file constraints (size/type/dimensions).
- Commit history shows iterative delivery from base project to secured API + UI integration.

### DX strengths

- Composer scripts provide setup/dev/test commands.
- Test environment configured for fast feedback (SQLite memory, lower bcrypt rounds in test env).
- Conventional Laravel structure lowers onboarding friction.

## Weaknesses

### Functional and correctness issues

- Post creation uses hard-coded author_id = 1, breaking multi-user correctness.
- PostResource expects author relation while Post model exposes user relation; serialization consistency risk.
- PromptGenerationController sort parsing appears buggy (checks starts_with on default sort field variable instead of incoming sort string), so sort behavior may not match intent.
- Backend type choices in migration store file_size as string instead of numeric type.

### Security and reliability concerns

- OpenAI service lacks explicit timeout/retry/circuit-breaker policy around external calls.
- No dedicated throttling policy for expensive prompt generation endpoint beyond global API limiter.
- CORS configured permissively on paths and credentials enabled; safe in development but needs stricter production governance.

### Testing gaps

- Current test suite fails (5 failed, 5 passed in local run).
- Auth tests still assert session-style semantics/no-content responses that conflict with token API behavior.
- No focused tests for PromptGenerationController, OpenAiService behavior boundaries, or storage side-effects.
- No contract tests for response resource structures.

### Maintainability and consistency gaps

- Method naming style inconsistency (PromptGenerations method casing).
- Minor dead/unused imports and commented code reduce signal quality.
- Frontend sort is partly client-side despite backend sort support, increasing ambiguity.

### Production readiness gaps

- No CI/CD pipelines.
- No infrastructure definitions (Docker/Kubernetes/hosting manifests).
- No environment profile strategy documented for staging/production.

## Improvements

### High-priority refactors

1. Replace hard-coded author ownership with authenticated user context in post creation.
2. Normalize relation naming across model and resources (e.g., author relation explicitly defined or resource updated to user).
3. Fix sort parsing in PromptGenerationController and align with frontend sort contract.
4. Change file_size column to integer/bigInteger and migrate data safely.

### Reliability and resilience

1. Add OpenAI request timeout, retry policy, graceful fallback messages, and failure logging with request correlation IDs.
2. Introduce per-user generation rate limits (e.g., per minute + per day quota).
3. Add background queue option for generation to absorb latency spikes and support retries.

### Security improvements

1. Tighten CORS paths/origins for production.
2. Add payload/content scanning and stricter upload constraints if product scope widens.
3. Enforce email verification middleware on protected content creation endpoints when business rules require it.

### Testing strategy upgrades

1. Update auth tests to token-flow expectations (assert JSON token + token revocation behavior).
2. Add feature tests for prompt generation upload validation, happy path, and API error paths.
3. Mock OpenAI client in tests to avoid real network dependency.
4. Add API contract snapshot tests for resources.
5. Add frontend component/unit tests for validators and service normalization logic.

### Architecture and scaling

1. Introduce API response envelope standard (data/meta/error schema) across all endpoints.
2. Add query object or repository abstraction if query complexity grows.
3. Consider Pinia only when cross-page client state complexity justifies it.
4. Add observability foundations: structured logs, request IDs, and endpoint latency metrics.

## Features

### 1. Authentication and identity

- Registration with strong password rule set.
- Login issues Sanctum token.
- Logout revokes current access token.
- Password reset request + token-based reset.
- Email verification route and notification endpoint.

Complexity: Medium (framework-driven with custom API response behavior).

### 2. Posts CRUD

- Versioned resource routes for create/read/update/delete.
- Request validation and resource serialization.

Complexity: Low to medium; currently weakened by ownership handling bug.

### 3. Prompt generation from image

- Authenticated image upload.
- Validation (type, size, dimensions).
- Safe file naming and storage under public disk.
- OpenAI call using data URL payload.
- Persist generated prompt + file metadata + owner.

Complexity: Medium to high due to external AI dependency and file pipeline.

### 4. Prompt history management

- Paginated list for current user records.
- Search and MIME-type filtering.
- Sort controls with mixed backend/client sorting behavior.

Complexity: Medium.

### 5. Frontend UX layer

- Route guards for auth-required pages.
- Upload flow with preview and regenerate action.
- History browsing with filters and pagination.
- Copy-to-clipboard interactions.
- Light/dark theme toggle.

Complexity: Medium with good UX polish for a prototype.

## Data Flow

### Prompt generation flow

1. User selects image in Upload page.
2. Frontend validates file and dimensions.
3. Frontend sends multipart request with Bearer token.
4. Backend validates via GeneratePromptRequest.
5. File stored on public disk with sanitized unique name.
6. OpenAiService encodes image, calls OpenAI chat model.
7. Generated text and file metadata saved to prompt_generations linked to user.
8. API returns PromptGenerationResource.
9. Frontend renders generated prompt card and metadata.

### History flow

1. History page builds query params from filter model.
2. Frontend calls GET prompt-generations.
3. Backend applies user scope + optional filters + order + pagination.
4. Response includes item array and pagination metadata.
5. Frontend applies optional client-side sort and renders list.

### Auth flow

1. Register endpoint creates user and authenticates session.
2. Frontend performs register-then-login to obtain API token.
3. Token stored in localStorage and attached to subsequent requests.
4. Logout endpoint revokes current token, frontend clears local token.

## Risks

### Immediate risks

- Multi-user data ownership bug in post creation.
- Auth test suite failure indicates contract drift and reduced release confidence.
- AI endpoint cost exposure due to insufficient endpoint-specific throttling.

### Medium-term risks

- Inconsistent relation naming can cause subtle serialization bugs.
- Ambiguous sorting strategy can create inconsistent UX/data behavior over time.
- Missing CI means regressions can silently reach shared branches.

### Long-term risks

- Synchronous OpenAI calls can become a scalability bottleneck.
- Lack of structured observability makes operational troubleshooting difficult.
- Prototype-era assumptions (SQLite defaults, no deployment profile) can slow production hardening.

### Confirmed evidence from runtime checks

- Local test run output: 5 failed, 5 passed.
- Primary failures: auth expectations and password reset notification assertions.
