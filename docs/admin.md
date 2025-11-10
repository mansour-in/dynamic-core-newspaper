# Admin User Provisioning & Password Reset

## Create Admin User
1. Generate bcrypt hash (cost 12 recommended):
   ```bash
   php -r "echo password_hash('StrongPasswordHere', PASSWORD_BCRYPT, ['cost' => 12]), PHP_EOL;"
   ```
2. Insert into `admin_users` table:
   ```sql
   INSERT INTO admin_users (email, password_hash, role, created_at)
   VALUES ('admin@core.fit', '$2y$12$hash...', 'admin', NOW());
   ```
3. Confirm login at `/admin/login`.

## Password Policy
- Minimum length 12 characters.
- Encourage passphrases with mixed case and symbols.
- Store hashes only; never plain text.

## Reset Password
1. Generate new hash as above.
2. Update record:
   ```sql
   UPDATE admin_users SET password_hash = '$2y$12$newhash...' WHERE email = 'admin@core.fit';
   ```
3. Log audit trail manually if required by compliance (update `provider_changes` is unrelated).
4. Notify the user securely and advise immediate login and verification.

## Viewer Accounts
- Create with role `viewer` to provide read-only access to admin pages.
- Viewer accounts cannot modify provider settings.

## Session Hardening
- Sessions are invalidated on logout.
- Encourage administrators to log out after use and enable MFA on email accounts.
