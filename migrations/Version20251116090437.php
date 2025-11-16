<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20251116090437 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Refactor monthly limit entity';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('DROP INDEX idx_dee67429a76ed395');
        $this->addSql('ALTER TABLE user_monthly_limits DROP CONSTRAINT user_monthly_limits_pkey');
        $this->addSql('CREATE UNIQUE INDEX user_month_unique ON user_monthly_limits (user_id, usage_month)');
        $this->addSql('ALTER TABLE user_monthly_limits ADD PRIMARY KEY (user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP INDEX user_month_unique');
        $this->addSql('DROP INDEX user_monthly_limits_pkey');
        $this->addSql('CREATE INDEX idx_dee67429a76ed395 ON user_monthly_limits (user_id)');
        $this->addSql('ALTER TABLE user_monthly_limits ADD PRIMARY KEY (user_id, usage_month)');
    }
}
