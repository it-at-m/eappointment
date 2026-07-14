ALTER TABLE standort
  DROP COLUMN IF EXISTS smswarteschlange,
  DROP COLUMN IF EXISTS smswmsbestaetigung,
  DROP COLUMN IF EXISTS smsbenachrichtigungsfrist,
  DROP COLUMN IF EXISTS smsbenachrichtigungstext,
  DROP COLUMN IF EXISTS smsbestaetigungstext;