-- Create a restricted user for the application
-- Run this as a superuser (postgres)

-- 1. Create the user
CREATE USER hq_app_user WITH PASSWORD 'your_secure_password';

-- 2. Grant connection permission
GRANT CONNECT ON DATABASE hq_app TO hq_app_user;

-- 3. Grant schema usage
GRANT USAGE ON SCHEMA public TO hq_app_user;

-- 4. Grant specific privileges (Principle of Least Privilege)
-- Allow reading/writing data
GRANT SELECT, INSERT, UPDATE, DELETE ON ALL TABLES IN SCHEMA public TO hq_app_user;
GRANT USAGE, SELECT ON ALL SEQUENCES IN SCHEMA public TO hq_app_user;

-- 5. Revoke dangerous permissions (just in case)
REVOKE CREATE ON SCHEMA public FROM hq_app_user; -- Cannot create new tables
REVOKE DROP ON ALL TABLES IN SCHEMA public FROM hq_app_user; -- Cannot drop tables
