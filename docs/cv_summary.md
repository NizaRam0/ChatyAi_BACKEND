# CV Summary

## Senior Full-Stack AI Engineer Project Entry

Built and delivered ChatyAi, a full-stack AI-powered web platform that converts uploaded images into high-fidelity generation prompts using OpenAI Vision workflows. Architected and implemented a Laravel 13 API with token-based authentication, validated file ingestion, prompt-generation orchestration, and user-scoped prompt history; developed a Vue 3 SPA with protected routing, upload/preview UX, filterable history, and production-ready API service abstractions.

### Technologies

- Backend: PHP 8.3, Laravel 13, Sanctum, Eloquent ORM, Form Requests, API Resources, Dedoc Scramble
- AI Integration: openai-php/client (GPT-4o-mini image analysis prompt generation)
- Frontend: Vue 3, Vue Router 5, Vite 8, modular service/component architecture
- Data & Testing: SQLite, migration-based schema evolution, Pest/PHPUnit feature testing

### Responsibilities

- Designed versioned REST API contracts and authentication flows.
- Implemented image upload pipeline with validation, sanitization, storage, and metadata persistence.
- Integrated OpenAI prompt generation through a dedicated service layer.
- Built responsive frontend pages for upload, generation result handling, and searchable history.
- Implemented auth-aware route guards, local token lifecycle management, and user feedback patterns.
- Established foundational engineering practices via request validation, resource serialization, and test scaffolding.

### Key Achievements

- Delivered complete image-to-prompt feature loop end-to-end (upload -> AI generation -> persisted history -> retrieval UI).
- Introduced maintainable separation of concerns across controllers, services, resources, and frontend API clients.
- Improved developer productivity through structured project conventions and scriptable setup/test workflows.
- Added extensibility points for future scaling (API versioning, middleware-based controls, OpenAPI-ready annotations).

### Impact Statement

Enabled rapid transformation of visual references into reusable AI prompts, reducing manual prompt authoring effort and creating a scalable foundation for future creator-focused AI tooling.
