# Intake

Raw operator/supplier inputs land here before being normalised into individual tour packages under `content-system/tours/<slug>/`.

## Files

- `2026-05-12-tour-batch-01.csv` — initial batch of 5 in-scope tours (Cinque Terre, three Lake Como/Lugano variants, Milan cooking class). Source-facts and meta files for each tour are derived from this row.

## Rejected from this batch

- **Pisa Afternoon Tour from Florence + Skip-the-Line Access** — out of scope for the Milano Adventures brand (Florence-departing, Tuscan operator). Removed from the intake CSV, the cluster registry, and the tours folder. If a Florence-aligned brand is added later, re-import from the original supplier source.

## Why a separate folder

The dashboard's tour scanner only reads `content-system/tours/*` directories that contain a `meta.json`. Keeping raw intake outside `tours/` prevents partial CSV rows from being mistaken for content packages and preserves the exact supplier text for provenance.
