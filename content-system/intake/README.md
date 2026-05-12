# Intake

Raw operator/supplier inputs land here before being normalised into individual tour packages under `content-system/tours/<slug>/`.

## Files

- `2026-05-12-tour-batch-01.csv` — initial batch of 6 tours (Pisa, Cinque Terre, three Lake Como/Lugano variants, Milan cooking class). Source-facts and meta files for each tour are derived from this row.

## Why a separate folder

The dashboard's tour scanner only reads `content-system/tours/*` directories that contain a `meta.json`. Keeping raw intake outside `tours/` prevents partial CSV rows from being mistaken for content packages and preserves the exact supplier text for provenance.
