# Mistral OCR: `POST /v1/ocr`

Bronnen, opgehaald 2026-10-04:

- OpenAPI-spec: https://docs.mistral.ai/openapi.yaml (operation `ocr_v1_ocr_post`, schemas `OCRRequest`, `OCRResponse`, `OCRPageObject`, `OCRPageConfidenceScores`, `OCRConfidenceScore`, `OCRUsageInfo`, `DocumentURLChunk`, `ImageURLChunk`)
- Gids: https://docs.mistral.ai/studio/document-processing/basic_ocr.md
- Regionale endpoints: https://docs.mistral.ai/inference/regional-inference.md

## Endpoint

`api.eu.mistral.ai` is het EU-endpoint: "Use the EU regional endpoint to support inference processing within the European Union." De spec noemt alleen `https://api.mistral.ai` als server; het pad is op beide gelijk.

> Stateful features, including Agents, Batch, and the Files API, are not available on regional endpoints.

Daarom stuurt de SDK documenten altijd inline als data-URL, nooit via `file_id`.

## Request (`OCRRequest`)

Verplicht: `document`, `model`.

| Veld | Type | Gebruikt |
|---|---|---|
| `model` | string \| null | ja |
| `document` | `FileChunk` \| `DocumentURLChunk` \| `ImageURLChunk` | ja, zonder `FileChunk` |
| `confidence_scores_granularity` | `"word"` \| `"page"` \| `"block"` \| null | optioneel. "Defaults to None (no confidence scores)" |

`DocumentURLChunk`: `{"type": "document_url", "document_url": string, "document_name"?: string}`. Base64-PDF volgens de gids:

```json
{"type": "document_url", "document_url": "data:application/pdf;base64,<base64>"}
```

`ImageURLChunk`: `{"type": "image_url", "image_url": string | {"url": string, "detail"?: ...}}`. Voorbeeld uit de spec: `{"type":"image_url","image_url":"data:image/png;base64,iVBORw0"}`.

De gids: `image_url` voor "PNG, JPEG/JPG, AVIF, and other image formats", `document_url` voor "PDF, PPTX, DOCX, and other document formats".

Niet gebruikt door de SDK: `pages`, `include_image_base64`, `image_limit`, `image_min_size`, `bbox_annotation_format`, `document_annotation_format`, `document_annotation_prompt`, `table_format`, `extract_header`, `extract_footer`, `include_blocks`.

## Response (`OCRResponse`)

Verplicht: `pages`, `model`, `usage_info`. Daarnaast `document_annotation` (string \| null).

`OCRPageObject` (gebruikte velden):

| Veld | Type |
|---|---|
| `index` | integer, "starting from 0" |
| `markdown` | string |
| `confidence_scores` | `OCRPageConfidenceScores` \| null, "populated when confidence_scores_granularity is set" |

Overige paginavelden die de SDK negeert: `images`, `tables`, `hyperlinks`, `header`, `footer`, `dimensions`, `blocks`.

`OCRPageConfidenceScores`: verplicht `average_page_confidence_score` en `minimum_page_confidence_score` (number, 0 tot 1); `word_confidence_scores` (lijst van `OCRConfidenceScore`) "populated only for 'word' granularity".

`OCRConfidenceScore`: `text` (string), `confidence` (number 0 tot 1), `start_index` (integer, "Start index of the text in the page markdown string").

`OCRUsageInfo`: `pages_processed` (integer, verplicht), `doc_size_bytes` (integer \| null).

## Live bevestigd

Een gemeten EU-run (2026-10-04, `mistral-ocr-latest`, `confidence_scores_granularity: "word"`) gaf exact deze veldnamen terug, voor zowel een `image_url`- als een `document_url`-PDF-request.
