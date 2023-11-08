<?php

return [
    "CREATE TABLE IF NOT EXISTS `topic_service` (
		`topic_id`	INTEGER NOT NULL,
		`service_id`	INTEGER NOT NULL,
		PRIMARY KEY(`topic_id`,`service_id`)
	);",
    "CREATE TABLE IF NOT EXISTS `topic_links` (
		`topic_id`	INTEGER NOT NULL,
		`name`	VARCHAR ( 255 ),
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`rank`	INTEGER NOT NULL,
		`url`	VARCHAR ( 255 ),
		`highlight`	TINYINT,
		`search`	TEXT,
		`meta_json`	TEXT,
		`data_json`	TEXT
	);",
    "CREATE TABLE IF NOT EXISTS `topic_cluster` (
		`topic_id`	INTEGER NOT NULL,
		`parent_id`	INTEGER NOT NULL,
		`rank`	INTEGER NOT NULL
	);",
    "CREATE TABLE IF NOT EXISTS `topic` (
		`id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`name`	VARCHAR ( 255 ),
		`path`	VARCHAR ( 255 ) NOT NULL,
		`navi`	INTEGER,
		`root`	INTEGER,
		`rank`	INTEGER NOT NULL,
		`data_json`	TEXT,
		PRIMARY KEY(`id`,`locale`)
	);",
    "CREATE TABLE IF NOT EXISTS `setting` (
		`name`	VARCHAR ( 255 ) NOT NULL,
		`value`	TEXT,
		PRIMARY KEY(`name`)
	);",
    "CREATE TABLE IF NOT EXISTS `service_information` (
		`service_id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`name`	TEXT,
		`description`	TEXT NOT NULL,
		`link`	TEXT,
		`type`	VARCHAR ( 255 ) NOT NULL,
		`sort`	TINYINT NOT NULL,
		`data_json`	TEXT,
		PRIMARY KEY(`service_id`,`locale`,`type`,`sort`)
	);",
    "CREATE TABLE IF NOT EXISTS `service` (
		`id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`leika`	VARCHAR ( 14 ) NOT NULL,
		`name`	VARCHAR ( 255 ) NOT NULL,
		`description`	TEXT NOT NULL,
		`hint`	TEXT,
		`fees`	TEXT,
		`residence`	VARCHAR ( 255 ),
		`representation`	VARCHAR ( 255 ),
		`responsibility`	TEXT,
		`responsibility_all`	TINYINT,
		`processing_time`	TEXT,
		`root_topic_id`	INTEGER NOT NULL,
		`appointment_all_link`	VARCHAR ( 255 ),
		`onlineprocessing_json`	TEXT,
		`relation_json`	TEXT,
		`authorities_json`	TEXT,
		`data_json`	TEXT,
		PRIMARY KEY(`id`,`locale`)
	);",
    "CREATE TABLE IF NOT EXISTS `search` (
		`object_id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`entity_type`	VARCHAR ( 255 ),
		`search_type`	TEXT NOT NULL,
		`search_value`	TEXT,
		PRIMARY KEY(`object_id`,`locale`,`search_type`)
	);",
    "CREATE TABLE IF NOT EXISTS `meta` (
		`object_id`	INTEGER NOT NULL,
		`hash`	VARCHAR ( 255 ) NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`lastupdate`	DATETIME NOT NULL,
		`keywords`	TEXT,
		`url`	VARCHAR ( 255 ),
		`type`	VARCHAR ( 25 ) NOT NULL,
		`titles_json`	TEXT,
		PRIMARY KEY(`object_id`,`locale`,`type`)
	);",
    "CREATE TABLE IF NOT EXISTS `location_service` (
		`location_id`	INTEGER NOT NULL,
		`service_id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`appointment_slots`	TINYINT,
		`appointment_bookable`	TINYINT,
		`appointment_external`	TINYINT,
		`appointment_multiple`	TINYINT,
		`appointment_link`	VARCHAR ( 255 ),
		`appointment_note`	TEXT,
		`contact_json`	TEXT,
		PRIMARY KEY(`location_id`,`service_id`,`locale`)
	);",
    "CREATE TABLE IF NOT EXISTS `location` (
		`id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`name`	VARCHAR ( 255 ) NOT NULL,
		`category_name`	VARCHAR ( 255 ) NOT NULL,
		`category_identifier`	VARCHAR ( 255 ) NOT NULL,
		`authority_id`	INTEGER,
		`authority_name`	VARCHAR ( 255 ) NOT NULL,
		`note`	TEXT,
		`category_json`	TEXT,
		`urgent_json`	TEXT,
		`opening_times_json`	TEXT,
		`transit_json`	TEXT,
		`deviating_postal_address_json`	TEXT,
		`payment_json`	TEXT,
		`accessibility_json`	TEXT,
		`appointment_json`	TEXT,
		`data_json`	TEXT,
		PRIMARY KEY(`id`,`locale`)
	);",
    "CREATE TABLE IF NOT EXISTS `contact` (
		`object_id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`name`	VARCHAR ( 255 ),
		`contact_json`	TEXT,
		`address_json`	TEXT,
		`deviating_postal_address_json`	TEXT,
		`geo_json`	TEXT,
		PRIMARY KEY(`object_id`,`locale`)
	);",
    "CREATE TABLE IF NOT EXISTS `authority_service` (
		`authority_id`	INTEGER NOT NULL,
		`service_id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		PRIMARY KEY(`authority_id`,`service_id`,`locale`)
	);",
    "CREATE TABLE IF NOT EXISTS `authority_location` (
		`authority_id`	INTEGER NOT NULL,
		`location_id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		PRIMARY KEY(`authority_id`,`location_id`,`locale`)
	);",
    "CREATE TABLE IF NOT EXISTS `authority` (
		`id`	INTEGER NOT NULL,
		`locale`	VARCHAR ( 4 ) NOT NULL,
		`name`	VARCHAR ( 255 ),
		`parent_id`	INTEGER NOT NULL,
		`locations_json`	TEXT,
		`relation_json`	TEXT,
		`contact_json`	TEXT,
		`data_json`	TEXT,
		PRIMARY KEY(`id`,`locale`)
	);",
    "CREATE INDEX IF NOT EXISTS `topic_root_index` ON `topic` (
		`root`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_rank_index` ON `topic` (
		`rank`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_path_index` ON `topic` (
		`path`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_navi_index` ON `topic` (
		`navi`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_links_topic_id_index` ON `topic_links` (
		`topic_id`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_links_rank_index` ON `topic_links` (
		`rank`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_links_locale_index` ON `topic_links` (
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_links_keywords_search_index` ON `topic_links` (
		`search`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_cluster_topic_id_index` ON `topic_cluster` (
		`topic_id`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_cluster_rank_index` ON `topic_cluster` (
		`rank`
	);",
    "CREATE INDEX IF NOT EXISTS `topic_cluster_parent_id_index` ON `topic_cluster` (
		`parent_id`
	);",
    "CREATE INDEX IF NOT EXISTS `service_search_index` ON `service` (
		`description`
	);",
    "CREATE INDEX IF NOT EXISTS `service_information_type_index` ON `service_information` (
		`type`
	);",
    "CREATE INDEX IF NOT EXISTS `service_information_service_id_index` ON `service_information` (
		`service_id`
	);",
    "CREATE INDEX IF NOT EXISTS `search_search_type_index` ON `search` (
		`search_type`
	);",
    "CREATE INDEX IF NOT EXISTS `search_search_index` ON `search` (
		`search_value`
	);",
    "CREATE INDEX IF NOT EXISTS `search_object_id_index` ON `search` (
		`object_id`
	);",
    "CREATE INDEX IF NOT EXISTS `search_locale_index` ON `search` (
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `search_entity_type_index` ON `search` (
		`entity_type`
	);",
    "CREATE INDEX IF NOT EXISTS `meta_type_index` ON `meta` (
		`type`
	);",
    "CREATE INDEX IF NOT EXISTS `meta_object_id_index` ON `meta` (
		`object_id`
	);",
    "CREATE INDEX IF NOT EXISTS `meta_hash_index` ON `meta` (
		`hash`
	);",
    "CREATE INDEX IF NOT EXISTS `location_service_service_id_locale_index` ON `location_service` (
		`service_id`,
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `location_service_service_id_index` ON `location_service` (
		`service_id`
	);",
    "CREATE INDEX IF NOT EXISTS `location_service_location_id_locale_index` ON `location_service` (
		`location_id`,
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `location_service_location_id_index` ON `location_service` (
		`location_id`
	);",
    "CREATE INDEX IF NOT EXISTS `location_service_locale_index` ON `location_service` (
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `location_name_index` ON `location` (
		`name`
	);",
    "CREATE INDEX IF NOT EXISTS `location_category_identifier_index` ON `location` (
		`category_identifier`
	);",
    "CREATE INDEX IF NOT EXISTS `location_authority_id_index` ON `location` (
		`authority_id`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_service_service_id_locale_index` ON `authority_service` (
		`service_id`,
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_service_service_id_index` ON `authority_service` (
		`service_id`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_service_locale_index` ON `authority_service` (
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_service_authority_id_locale_index` ON `authority_service` (
		`authority_id`,
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_service_authority_id_index` ON `authority_service` (
		`authority_id`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_location_location_id_locale_index` ON `authority_location` (
		`location_id`,
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_location_location_id_index` ON `authority_location` (
		`location_id`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_location_locale_index` ON `authority_location` (
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_location_authority_id_locale_index` ON `authority_location` (
		`authority_id`,
		`locale`
	);",
    "CREATE INDEX IF NOT EXISTS `authority_location_authority_id_index` ON `authority_location` (
		`authority_id`
	);"
];
