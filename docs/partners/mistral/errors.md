# Mistral foutcodes

Bronnen, opgehaald 2026-10-04:

- Error glossary: https://docs.mistral.ai/resources/error-glossary.md
- OpenAPI-spec: https://docs.mistral.ai/openapi.yaml (`HTTPValidationError`, `ValidationError`)

## Statuscodes

| Status | Betekenis volgens Mistral | `ErrorKind` |
|---|---|---|
| 400 | "The request body is malformed or missing required fields." | `BadRequest` |
| 401 | "The API key is missing, invalid, or expired." | `Authentication` |
| 403 | "Your account does not have permission to access the requested resource." | `Forbidden` |
| 404 | "The requested resource does not exist." | `NotFound` |
| 422 | "The request is well-formed JSON but contains invalid parameter values." | `Validation` |
| 429 | "You have exceeded the rate limit for your subscription tier." Controleer de `Retry-After`-header. | `RateLimited` |
| 500, 502, 503, 504 | Serverfouten; Mistral adviseert retry met backoff | `ServerError` |

Elke andere status wordt `Unknown`.

## Foutbody

Volgens de glossary:

```json
{
  "object": "error",
  "message": "A human-readable description of the error.",
  "type": "invalid_request_error",
  "param": "model",
  "code": "unknown_model"
}
```

`type`: `invalid_request_error`, `authentication_error`, `rate_limit_error`, `server_error`.

De OpenAPI-spec beschrijft 422 als `HTTPValidationError`: `{"detail": [{"loc": [...], "msg": string, "type": string, "input"?: ..., "ctx"?: {...}}]}`. Welke van de twee vormen 422 werkelijk teruggeeft, is niet live gemeten. De SDK leest `message`, `type` en `code` als ze strings zijn en valt anders terug op "Mistral gaf HTTP <status>". `detail[].input` kan de request echoën en wordt bewust niet doorgegeven.
