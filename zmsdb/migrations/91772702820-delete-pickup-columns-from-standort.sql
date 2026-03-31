ALTER TABLE standort
  DROP COLUMN IF EXISTS defaultabholerstandort,
  DROP COLUMN IF EXISTS ausgabeschaltername,
  DROP COLUMN IF EXISTS wartezeitveroeffentlichen,
  DROP COLUMN IF EXISTS qtv_url;