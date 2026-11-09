-- Generate a real hash first:  php -r "echo password_hash('your_admin_password', PASSWORD_DEFAULT);"
INSERT INTO users (email, password_hash, role, full_name, is_verified, password_reset_required, created_at) VALUES (
  'admin@ctrlflow.local',
  '$2y$10$REPLACE_WITH_YOUR_OWN_GENERATED_HASH_____________',
  'super_admin',
  'Ctrl+Flow Admin',
  1, 0, NOW()
);
