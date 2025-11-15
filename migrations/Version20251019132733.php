<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251019132733 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Initial migration with RLS policies';
    }

    public function up(Schema $schema): void
    {
        $this->addSql("CREATE TYPE expense_category AS ENUM ('Transportation', 'Accomodation', 'Food & Drink', 'Activities', 'Uncategorized')");
        $this->addSql('CREATE TABLE expenses (id BIGSERIAL NOT NULL, trip_id BIGINT NOT NULL, amount NUMERIC(10, 2) NOT NULL, category expense_category NOT NULL DEFAULT \'Uncategorized\', created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX idx_expenses_trip_id ON expenses (trip_id)');
        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT chk_amount_positive CHECK (amount > 0)');
        $this->addSql('COMMENT ON COLUMN expenses.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN expenses.updated_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('CREATE TABLE trips (id BIGSERIAL NOT NULL, user_id BIGINT NOT NULL, trip_name VARCHAR(70) NOT NULL, trip_currency VARCHAR(3) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql("ALTER TABLE trips ADD CONSTRAINT chk_trip_name_not_empty CHECK (trim(trip_name) <> '')");
        $this->addSql('CREATE INDEX idx_trips_user_id ON trips (user_id)');
        $this->addSql('CREATE UNIQUE INDEX uq_user_trip_name ON trips (user_id, trip_name)');
        $this->addSql('COMMENT ON COLUMN trips.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN trips.updated_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('CREATE TABLE user_monthly_limits (usage_month DATE NOT NULL, user_id BIGINT NOT NULL, upload_count INT NOT NULL, PRIMARY KEY(user_id, usage_month))');
        $this->addSql('ALTER TABLE user_monthly_limits ALTER COLUMN upload_count SET DEFAULT 0');
        $this->addSql('ALTER TABLE user_monthly_limits ADD CONSTRAINT chk_upload_count_non_negative CHECK (upload_count >= 0)');
        $this->addSql('CREATE INDEX IDX_DEE67429A76ED395 ON user_monthly_limits (user_id)');
        $this->addSql('COMMENT ON COLUMN user_monthly_limits.usage_month IS \'(DC2Type:date_immutable)\'');

        $this->addSql('CREATE TABLE users (id BIGSERIAL NOT NULL, email VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, created_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, updated_at TIMESTAMP(0) WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1483A5E9E7927C74 ON users (email)');
        $this->addSql('COMMENT ON COLUMN users.created_at IS \'(DC2Type:datetimetz_immutable)\'');
        $this->addSql('COMMENT ON COLUMN users.updated_at IS \'(DC2Type:datetimetz_immutable)\'');

        $this->addSql('CREATE TABLE messenger_messages (id BIGSERIAL NOT NULL, body TEXT NOT NULL, headers TEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, available_at TIMESTAMP(0) WITHOUT TIME ZONE NOT NULL, delivered_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT NULL, PRIMARY KEY(id))');
        $this->addSql('CREATE INDEX IDX_75EA56E0FB7336F0 ON messenger_messages (queue_name)');
        $this->addSql('CREATE INDEX IDX_75EA56E0E3BD61CE ON messenger_messages (available_at)');
        $this->addSql('CREATE INDEX IDX_75EA56E016BA31DB ON messenger_messages (delivered_at)');
        $this->addSql('COMMENT ON COLUMN messenger_messages.created_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.available_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('COMMENT ON COLUMN messenger_messages.delivered_at IS \'(DC2Type:datetime_immutable)\'');
        $this->addSql('CREATE OR REPLACE FUNCTION notify_messenger_messages() RETURNS TRIGGER AS $$
            BEGIN
                PERFORM pg_notify(\'messenger_messages\', NEW.queue_name::text);
                RETURN NEW;
            END;
        $$ LANGUAGE plpgsql;');
        $this->addSql('DROP TRIGGER IF EXISTS notify_trigger ON messenger_messages;');
        $this->addSql('CREATE TRIGGER notify_trigger AFTER INSERT OR UPDATE ON messenger_messages FOR EACH ROW EXECUTE PROCEDURE notify_messenger_messages();');

        $this->addSql('ALTER TABLE expenses ADD CONSTRAINT FK_2496F35BA5BC2E0E FOREIGN KEY (trip_id) REFERENCES trips (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE trips ADD CONSTRAINT FK_AA7370DAA76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE user_monthly_limits ADD CONSTRAINT FK_DEE67429A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE NOT DEFERRABLE INITIALLY IMMEDIATE');

        // --- RLS POLICIES ---

        // 1. Enable RLS on 'trips' and add policy
        $this->addSql('ALTER TABLE trips ENABLE ROW LEVEL SECURITY');
        $this->addSql('CREATE POLICY user_access_policy_trips ON trips FOR ALL USING (user_id = current_setting(\'app.current_user_id\', true)::BIGINT) WITH CHECK (user_id = current_setting(\'app.current_user_id\', true)::BIGINT)');

        // 2. Enable RLS on 'expenses' and add policy
        $this->addSql('ALTER TABLE expenses ENABLE ROW LEVEL SECURITY');
        $this->addSql("CREATE POLICY user_access_policy_expenses ON expenses FOR ALL USING (EXISTS (SELECT 1 FROM trips WHERE trips.id = expenses.trip_id AND trips.user_id = current_setting('app.current_user_id', true)::BIGINT)) WITH CHECK (EXISTS (SELECT 1 FROM trips WHERE trips.id = expenses.trip_id AND trips.user_id = current_setting('app.current_user_id', true)::BIGINT))");
    }

    public function down(Schema $schema): void
    {
        $this->addSql('CREATE SCHEMA public');

        // --- REMOVE RLS POLICIES ---
        $this->addSql('ALTER TABLE expenses DISABLE ROW LEVEL SECURITY');
        $this->addSql('DROP POLICY IF EXISTS user_access_policy_expenses ON expenses');
        $this->addSql('ALTER TABLE trips DISABLE ROW LEVEL SECURITY');
        $this->addSql('DROP POLICY IF EXISTS user_access_policy_trips ON trips');

        $this->addSql('ALTER TABLE expenses DROP CONSTRAINT FK_2496F35BA5BC2E0E');
        $this->addSql('ALTER TABLE trips DROP CONSTRAINT FK_AA7370DAA76ED395');
        $this->addSql('ALTER TABLE user_monthly_limits DROP CONSTRAINT FK_DEE67429A76ED395');
        $this->addSql('DROP TABLE expenses');
        $this->addSql('DROP TYPE IF EXISTS expense_category');
        $this->addSql('DROP TABLE trips');
        $this->addSql('DROP TABLE user_monthly_limits');
        $this->addSql('DROP TABLE users');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
