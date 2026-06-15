-- ============================================================
-- FMLider - Supabase Auth Sync Migration (v2 - safe to re-run)
-- ============================================================

-- 1. Add auth_id column (safe: won't error if already exists)
ALTER TABLE users ADD COLUMN IF NOT EXISTS auth_id uuid UNIQUE;
CREATE INDEX IF NOT EXISTS idx_users_auth_id ON users(auth_id);

-- 2. Drop and recreate trigger + function for auth sync
DROP TRIGGER IF EXISTS on_auth_user_created ON auth.users;
DROP FUNCTION IF EXISTS handle_new_auth_user();

CREATE OR REPLACE FUNCTION handle_new_auth_user()
RETURNS TRIGGER AS $$
BEGIN
    INSERT INTO public.users (
        auth_id, username, name, email, phone, role, approval_status, password, created_at
    ) VALUES (
        NEW.id,
        COALESCE(NEW.raw_user_meta_data->>'username', split_part(NEW.email, '@', 1)),
        COALESCE(NEW.raw_user_meta_data->>'name', NEW.email),
        NEW.email,
        COALESCE(NEW.raw_user_meta_data->>'phone', ''),
        COALESCE(NEW.raw_user_meta_data->>'role', 'cliente'),
        COALESCE(NEW.raw_user_meta_data->>'approval_status', 'pending'),
        'supabase_auth_managed',
        NEW.created_at
    );
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE TRIGGER on_auth_user_created
    AFTER INSERT ON auth.users
    FOR EACH ROW
    EXECUTE FUNCTION handle_new_auth_user();

-- 3. Drop and recreate approval sync trigger + function
DROP TRIGGER IF EXISTS on_users_approval_changed ON users;
DROP FUNCTION IF EXISTS sync_approval_to_auth_metadata();

CREATE OR REPLACE FUNCTION sync_approval_to_auth_metadata()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE auth.users
    SET raw_user_meta_data = raw_user_meta_data || jsonb_build_object(
        'approval_status', NEW.approval_status,
        'approved_at', NEW.approved_at::text,
        'rejection_reason', NEW.rejection_reason
    )
    WHERE id = NEW.auth_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE TRIGGER on_users_approval_changed
    AFTER UPDATE OF approval_status ON users
    FOR EACH ROW
    WHEN (OLD.approval_status IS DISTINCT FROM NEW.approval_status)
    EXECUTE FUNCTION sync_approval_to_auth_metadata();

-- 4. Drop and recreate profile sync trigger + function
DROP TRIGGER IF EXISTS on_users_profile_changed ON users;
DROP FUNCTION IF EXISTS sync_profile_to_auth_metadata();

CREATE OR REPLACE FUNCTION sync_profile_to_auth_metadata()
RETURNS TRIGGER AS $$
BEGIN
    UPDATE auth.users
    SET raw_user_meta_data = raw_user_meta_data || jsonb_build_object(
        'name', NEW.name,
        'phone', NEW.phone,
        'role', NEW.role,
        'photo', NEW.photo,
        'position', NEW.position,
        'company_completed', COALESCE((SELECT is_completed FROM companies WHERE user_id = NEW.id), false),
        'must_change_password', NEW.password_must_change,
        'password_changed_at', NEW.password_changed_at::text,
        'locked_at', NEW.locked_at::text,
        'locked_reason', NEW.locked_reason
    )
    WHERE id = NEW.auth_id;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql SECURITY DEFINER;

CREATE TRIGGER on_users_profile_changed
    AFTER UPDATE ON users
    FOR EACH ROW
    EXECUTE FUNCTION sync_profile_to_auth_metadata();

-- ============================================================
-- END
-- ============================================================
