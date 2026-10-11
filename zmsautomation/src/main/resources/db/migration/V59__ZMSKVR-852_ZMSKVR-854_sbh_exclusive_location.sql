-- ZMSKVR-852 / ZMSKVR-854: Sozialbürgerhaus peers for exclusiveLocation jump-in ATAF.
-- Scopes already exist in V5; seed missing dldb provider/request rows.
LOCK TABLES `provider` WRITE;
UNLOCK TABLES;

LOCK TABLES `request` WRITE;
UNLOCK TABLES;

LOCK TABLES `request_provider` WRITE;
UNLOCK TABLES;

