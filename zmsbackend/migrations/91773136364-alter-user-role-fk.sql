-- Ensure user deletions also remove their role mappings
-- Adjust foreign key on user_role.user_id to use ON DELETE CASCADE

ALTER TABLE user_role
  DROP FOREIGN KEY fk_user_role_user;

ALTER TABLE user_role
  ADD CONSTRAINT fk_user_role_user
    FOREIGN KEY (user_id)
    REFERENCES nutzer (NutzerID)
    ON DELETE CASCADE;

