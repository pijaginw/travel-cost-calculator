```markdown
1. List of tables with their columns, data types, and constraints
2. Relationships between tables
3. Indexes
4. PostgreSQL policies (if applicable)
5. Any additional notes or explanations about design decisions
```

-----

### 1\. Tables and Data Definition

This section defines the tables, columns, data types, and constraints required for the application.

#### **ENUM Type Definition**

First, the custom `ENUM` type for expense categories must be created as required by FR-014.

```sql
CREATE TYPE expense_category AS ENUM (
    'Transportation',
    'Accomodation',
    'Food & Drink',
    'Activities',
    'Uncategorized'
);
```

-----

#### **Table: `users`**

Stores user account and authentication information.

```sql
CREATE TABLE users (
    id BIGSERIAL PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
);
```

-----

#### **Table: `trips`**

Stores trip-level information, linked to a user.

```sql
CREATE TABLE trips (
    id BIGSERIAL PRIMARY KEY,
    user_id BIGINT NOT NULL,
    trip_name VARCHAR(70) NOT NULL,
    trip_currency VARCHAR(3) NOT NULL,
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    -- Enforce unique trip names per user (FR-006)
    CONSTRAINT uq_user_trip_name UNIQUE (user_id, trip_name),

    -- Prevent empty or whitespace-only names
    CONSTRAINT chk_trip_name_not_empty CHECK (trim(trip_name) <> '')
);
```

-----

#### **Table: `expenses`**

Stores individual expense records, linked to a trip.

```sql
CREATE TABLE expenses (
    id BIGSERIAL PRIMARY KEY,
    trip_id BIGINT NOT NULL,
    amount NUMERIC(10, 2) NOT NULL,
    category expense_category NOT NULL DEFAULT 'Uncategorized',
    created_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),
    updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW(),

    -- Ensure expense amounts are positive values
    CONSTRAINT chk_amount_positive CHECK (amount > 0)
);
```

*Note: Per FR-022 and planning decisions, this table intentionally does not store any file paths, original filenames, or initial AI suggestions.*

-----

#### **Table: `user_monthly_limits`**

Tracks the number of receipt uploads per user per month to enforce FR-023.

```sql
CREATE TABLE user_monthly_limits (
    user_id BIGINT NOT NULL,
    usage_month DATE NOT NULL,
    upload_count INT NOT NULL DEFAULT 0,

    -- Composite primary key to track count per user/month
    PRIMARY KEY (user_id, usage_month),

    -- Ensure count is never negative
    CONSTRAINT chk_upload_count_non_negative CHECK (upload_count >= 0)
);
```

-----

### 2\. Relationships (Foreign Keys)

This section defines the relationships between the tables.

```sql
-- Relationship: users (1) -> (N) trips
ALTER TABLE trips
    ADD CONSTRAINT fk_trips_users
    FOREIGN KEY (user_id)
    REFERENCES users (id)
    ON DELETE CASCADE;

-- Relationship: trips (1) -> (N) expenses
ALTER TABLE expenses
    ADD CONSTRAINT fk_expenses_trips
    FOREIGN KEY (trip_id)
    REFERENCES trips (id)
    ON DELETE CASCADE;

-- Relationship: users (1) -> (N) user_monthly_limits
ALTER TABLE user_monthly_limits
    ADD CONSTRAINT fk_limits_users
    FOREIGN KEY (user_id)
    REFERENCES users (id)
    ON DELETE CASCADE;
```

*Note: `ON DELETE CASCADE` is used as per planning decisions to ensure that deleting a user also removes all their associated trips, expenses, and usage limits.*

-----

### 3\. Indexes

Indexes are created to optimize query performance for common operations like logins and dashboard loading.

```sql
-- 1. For users table:
--    - `idx_users_email`: Automatically created by the UNIQUE constraint.
--    - Speeds up login by allowing fast lookup of users by email.

-- 2. For trips table:
--    - `idx_trips_user_id`: To quickly retrieve all trips for a user's dashboard (FR-007).
CREATE INDEX idx_trips_user_id ON trips (user_id);
--    - `uq_user_trip_name`: Automatically created by the UNIQUE constraint.

-- 3. For expenses table:
--    - `idx_expenses_trip_id`: To quickly aggregate SUM(amount) and COUNT(*)
--      for a specific trip (FR-008, FR-010).
CREATE INDEX idx_expenses_trip_id ON expenses (trip_id);
```

-----

### 4\. PostgreSQL Policies (Row-Level Security)

As specified in the planning session, RLS will be enabled on the `trips` and `expenses` tables to ensure users can only access their own data.

**Assumption:** The application (Symfony) will set a session variable `app.current_user_id` upon user authentication.

```sql
-- Enable RLS on the 'trips' table
ALTER TABLE trips ENABLE ROW LEVEL SECURITY;

-- Policy: Allow users to SELECT, UPDATE, DELETE only their own trips
CREATE POLICY user_access_policy_trips
    ON trips
    FOR ALL
    USING (user_id = current_setting('app.current_user_id', true)::BIGINT)
    WITH CHECK (user_id = current_setting('app.current_user_id', true)::BIGINT);

-- Enable RLS on the 'expenses' table
ALTER TABLE expenses ENABLE ROW LEVEL SECURITY;

-- Policy: Allow users to access expenses that belong to trips they own
CREATE POLICY user_access_policy_expenses
    ON expenses
    FOR ALL
    USING (
        EXISTS (
            SELECT 1 FROM trips
            WHERE trips.id = expenses.trip_id
            AND trips.user_id = current_setting('app.current_user_id', true)::BIGINT
        )
    )
    WITH CHECK (
        EXISTS (
            SELECT 1 FROM trips
            WHERE trips.id = expenses.trip_id
            AND trips.user_id = current_setting('app.current_user_id', true)::BIGINT
        )
    );
```

-----

### 5\. Additional Notes

1.  **Timestamp Management:** The `created_at` and `updated_at` columns use `DEFAULT NOW()`. The application logic (via Doctrine ORM) should be configured to automatically update the `updated_at` timestamp on any row modification.
2.  **Password Hashing:** The `users.password` column is `VARCHAR(255)` to be compatible with Symfony's default `bcrypt` hashing, as noted in the tech stack. All hashing is handled by the application, not the database.
3.  **Data Integrity:** Data integrity (e.g., `amount > 0`, `trip_name` not empty, `trip_name` length) is enforced at the database level using `CHECK` and `VARCHAR(50)` constraints, providing a strong secondary layer of validation to application-level checks.
4.  **Limits Logic:** The `user_monthly_limits` table only *tracks* usage. The application logic is responsible for:
    * Querying this table before an upload.
    * Blocking the upload if `upload_count >= 100` (per FR-023).
    * Incrementing the count using an `INSERT ... ON CONFLICT ... UPDATE` (UPSERT) command upon a successful upload.

<!-- end list -->

```
```
