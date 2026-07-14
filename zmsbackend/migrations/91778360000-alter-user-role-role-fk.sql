-- Ensure role deletions also remove their user_role mappings
-- Adjust foreign key on user_role.role_id to use ON DELETE CASCADE

ALTER TABLE user_role
  DROP FOREIGN KEY fk_user_role_role;

ALTER TABLE user_role
  ADD CONSTRAINT fk_user_role_role
    FOREIGN KEY (role_id)
    REFERENCES role (id)
    ON DELETE CASCADE;
