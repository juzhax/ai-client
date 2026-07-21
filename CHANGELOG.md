# Changelog

## 1.1.1 - 2026-07-21

- Point `HealthResource::ping()` to the AI Platform's Laravel health endpoint at `/up`.
- Update the default SDK user agent to `juzhax-ai-client/1.1.1`.

## 1.1.0 - 2026-07-21

- Add direct saved-prompt execution through `PromptsResource::run()`.
- Add `PromptRunRequest` with provider, model, and prompt variables.
- Add `PromptRunResponse` with output, structured data, usage, cost, and execution metadata.
- Update the default SDK user agent to `juzhax-ai-client/1.1`.

## 1.0.0 - 2026-07-14

- Initial framework-independent PHP SDK release.
