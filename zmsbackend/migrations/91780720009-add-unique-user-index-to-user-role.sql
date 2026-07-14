ALTER TABLE user_role
    ADD UNIQUE KEY uniq_user_role_user (user_id);