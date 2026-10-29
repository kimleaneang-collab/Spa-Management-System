ALTER TABLE therapists
    ADD COLUMN user_id BIGINT UNSIGNED NULL UNIQUE AFTER id,
    ADD CONSTRAINT fk_therapist_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL;
