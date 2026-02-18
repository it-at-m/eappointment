CREATE TABLE IF NOT EXISTS user_role
(
    user_id INT(5) UNSIGNED NOT NULL,
    role_id INT UNSIGNED    NOT NULL,

    PRIMARY KEY (user_id, role_id),

    CONSTRAINT fk_user_role_user
        FOREIGN KEY (user_id) REFERENCES nutzer (NutzerID),

    CONSTRAINT fk_user_role_role
        FOREIGN KEY (role_id) REFERENCES role (id)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8mb4
  COLLATE = utf8mb4_unicode_ci;
