-- ============================================================
-- FMLider - Auth Security Configuration
-- Execute this SQL in Supabase SQL Editor
-- ============================================================

-- 1. Create a stronger JWT secret (if needed)
-- NOTE: This only works if you haven't rotated the JWT secret before
-- If you get an error here, skip it and rotate via Dashboard > Settings > API

-- 2. Enable email confirmation via SQL (Supabase v2+)
-- This requires the auth.config table which may not be accessible via SQL
-- If this fails, configure via Dashboard > Authentication > Settings

-- 3. Create a function to validate password strength
CREATE OR REPLACE FUNCTION public.validate_password_strength(password text)
RETURNS boolean AS $$
DECLARE
    has_upper boolean;
    has_lower boolean;
    has_digit boolean;
    has_special boolean;
BEGIN
    if length(password) < 8 then
        return false;
    end if;
    
    has_upper := password ~ '[A-Z]';
    has_lower := password ~ '[a-z]';
    has_digit := password ~ '[0-9]';
    has_special := password ~ '[!@#$%^&*(),.?":{}|<>]';
    
    -- Require at least 3 of 4 character types
    return (has_upper::int + has_lower::int + has_digit::int + has_special::int) >= 3;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 4. Create audit log table for auth events
CREATE TABLE IF NOT EXISTS auth_audit_log (
    id bigint GENERATED ALWAYS AS IDENTITY PRIMARY KEY,
    event_type varchar(50) NOT NULL,
    user_id uuid,
    email varchar(255),
    ip_address inet,
    user_agent text,
    metadata jsonb,
    created_at timestamptz DEFAULT now()
);

CREATE INDEX IF NOT EXISTS idx_auth_audit_user ON auth_audit_log(user_id);
CREATE INDEX IF NOT EXISTS idx_auth_audit_event ON auth_audit_log(event_type);
CREATE INDEX IF NOT EXISTS idx_auth_audit_created ON auth_audit_log(created_at);

-- 5. Create trigger to log auth events (signups, logins)
CREATE OR REPLACE FUNCTION public.log_auth_event()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO auth_audit_log (event_type, user_id, email, metadata)
    VALUES (
        TG_ARGV[0],
        NEW.id,
        NEW.email,
        jsonb_build_object(
            'role', NEW.raw_user_meta_data->>'role',
            'approval_status', NEW.raw_user_meta_data->>'approval_status'
        )
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- Trigger for new user signups
DROP TRIGGER IF EXISTS on_auth_user_signup_log ON auth.users;
CREATE TRIGGER on_auth_user_signup_log
    AFTER INSERT ON auth.users
    FOR EACH ROW
    EXECUTE FUNCTION public.log_auth_event('signup');

-- 6. Create view for admin to see auth audit log
CREATE OR REPLACE VIEW public.auth_audit_view AS
SELECT
    a.id,
    a.event_type,
    a.email,
    a.created_at,
    u.name as user_name,
    u.role as user_role
FROM auth_audit_log a
LEFT JOIN users u ON u.auth_id = a.user_id
ORDER BY a.created_at DESC;

-- 7. Create function to check if account is locked
CREATE OR REPLACE FUNCTION public.is_account_locked(user_auth_id uuid)
RETURNS boolean AS $$
DECLARE
    lock_record record;
BEGIN
    SELECT locked_at, locked_reason INTO lock_record
    FROM users
    WHERE auth_id = user_auth_id;
    
    IF lock_record.locked_at IS NULL THEN
        RETURN false;
    END IF;
    
    -- Lock expires after 24 hours
    IF (now() - lock_record.locked_at) > interval '24 hours' THEN
        UPDATE users SET locked_at = NULL, locked_reason = NULL WHERE auth_id = user_auth_id;
        RETURN false;
    END IF;
    
    RETURN true;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- 8. Create function to get user security status
CREATE OR REPLACE FUNCTION public.get_user_security_status(user_auth_id uuid)
RETURNS jsonb AS $$
DECLARE
    result jsonb;
    user_record record;
BEGIN
    SELECT * INTO user_record FROM users WHERE auth_id = user_auth_id;
    
    IF user_record IS NULL THEN
        RETURN jsonb_build_object('error', 'User not found');
    END IF;
    
    result := jsonb_build_object(
        'user_id', user_record.id,
        'auth_id', user_record.auth_id,
        'role', user_record.role,
        'approval_status', user_record.approval_status,
        'is_locked', user_record.locked_at IS NOT NULL,
        'locked_at', user_record.locked_at,
        'locked_reason', user_record.locked_reason,
        'password_must_change', user_record.password_must_change,
        'last_login', user_record.last_login,
        'status', user_record.status,
        'created_at', user_record.created_at
    );
    
    RETURN result;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

-- ============================================================
-- END
-- ============================================================
