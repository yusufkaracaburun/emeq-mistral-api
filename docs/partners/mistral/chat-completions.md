# Mistral chat completions: `POST /v1/chat/completions`

Bronnen, opgehaald 2026-10-04:

- OpenAPI-spec: https://docs.mistral.ai/openapi.yaml (operation `chat_completion_v1_chat_completions_post`, schemas `ChatCompletionRequest`, `UserMessage`, `ContentChunk`, `TextChunk`, `ImageURLChunk`, `ResponseFormat`, `JsonSchema`, `ChatCompletionResponse`, `ChatCompletionChoice`, `UsageInfo`)
- Vision: https://docs.mistral.ai/studio/conversations/vision.md
- Structured output: https://docs.mistral.ai/studio/conversations/structured-output/custom.md

## Request

Verplicht: `model` (string), `messages` (lijst).

| Veld | Type | Gebruikt |
|---|---|---|
| `model` | string | ja |
| `messages` | lijst van System-/User-/Assistant-/ToolMessage | ja, alleen `user` |
| `temperature` | number 0 tot 1.5 \| null. "The default value varies depending on the model" | optioneel |
| `response_format` | `ResponseFormat` | ja, altijd `json_schema` |

`UserMessage`: `{"role": "user", "content": string | null | ContentChunk[]}`.

`ContentChunk` die de SDK stuurt:

- `TextChunk`: `{"type": "text", "text": string}`
- `ImageURLChunk`: `{"type": "image_url", "image_url": string}`. De vision-gids stuurt base64 als `"image_url": "data:image/jpeg;base64,<base64>"`.

`ResponseFormat`: `{"type": "text" | "json_object" | "json_schema", "json_schema"?: JsonSchema}`.

`JsonSchema`: verplicht `name` (string) en `schema` (object); optioneel `description`, `strict` (boolean, default `false`). Voorbeeld uit de structured-output-gids:

```json
{"type": "json_schema", "json_schema": {"schema": {...}, "name": "book", "strict": true}}
```

## Response (`ChatCompletionResponse`)

Gebruikte velden: `model`, `choices[0].message.content`, `usage`.

- `ChatCompletionChoice`: `index`, `finish_reason` (`stop`, `length`, `model_length`, `error`, `tool_calls`), `message` (`AssistantMessage`).
- `AssistantMessage.content`: string \| null \| ContentChunk[]. De SDK accepteert alleen een string.
- `UsageInfo`: verplicht `prompt_tokens`, `completion_tokens`, `total_tokens` (integer); verder `prompt_audio_seconds`, `service_tier`.

## Live bevestigd

Een gemeten EU-run (2026-10-04) met `temperature: 0` en `response_format` als `json_schema` met `strict: true` gaf `object: "chat.completion"`, `choices[].message.content` als string en `usage` met de drie tokenvelden terug.
