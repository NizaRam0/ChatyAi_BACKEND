# LinkedIn Post

Most AI demo apps stop at a screenshot.
I wanted to build one that behaves like a real product.

Over the last sprint, I built ChatyAi: a full-stack web app that turns an uploaded image into a detailed, reusable AI prompt.

## What I built

- A Laravel 13 backend API with token-based authentication (Sanctum)
- A Vue 3 SPA for upload, generation, and history management
- An OpenAI image-analysis integration that converts visual input into prompt text
- A full user flow: register/login -> upload image -> generate prompt -> search/filter history

## Technical highlights

- Versioned REST endpoints for domain resources (/api/v1)
- Form Request validation for upload rules (type, size, dimensions)
- Service-layer OpenAI integration (image encoded and sent through chat completion)
- Resource-based API responses for stable frontend contracts
- Reusable frontend service clients and auth-aware route guards
- Responsive, componentized UX with copy-to-clipboard and pagination/filter controls

## Engineering challenges solved

1. Auth contract mismatch between registration and token issuance:
   Implemented register-then-login flow to guarantee token availability for SPA API calls.

2. Keeping frontend and backend validation aligned:
   Mirrored backend constraints on the client to reduce failed requests and improve UX.

3. Managing API variability safely:
   Added response/error normalization so UI receives predictable messages across failure modes.

## What I learned

- AI features become truly useful only when wrapped in reliable product plumbing (auth, validation, persistence, observability).
- Strong boundaries (controllers vs services vs resources) make AI integrations easier to evolve.
- A polished frontend experience matters as much as model output quality for user trust.

## Why this matters

This project is now a solid foundation for scaling into creator tooling:

- quota/rate-limit layers
- async generation queues
- richer prompt management and collaboration workflows

If you are building AI-enabled products, I would love to compare architecture choices, especially around reliability and cost control in model-integrated APIs.
