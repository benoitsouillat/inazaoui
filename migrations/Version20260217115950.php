<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260217115950 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" ADD username VARCHAR(180) DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD roles JSON DEFAULT NULL');
        $this->addSql('ALTER TABLE "user" ADD password VARCHAR(255) DEFAULT NULL');

        $this->addSql('UPDATE "user" SET username = email');
        $this->addSql('UPDATE "user" SET roles = \'["ROLE_ADMIN"]\' WHERE admin = true');
        $this->addSql('UPDATE "user" SET roles = \'["ROLE_USER"]\' WHERE admin = false');
        $this->addSql('UPDATE "user" SET password = \'LOCKED_ACCOUNT_CHANGE_REQUIRED\'');

        $this->addSql('ALTER TABLE "user" ALTER COLUMN username SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN roles SET NOT NULL');
        $this->addSql('ALTER TABLE "user" ALTER COLUMN password SET NOT NULL');

        $this->addSql('ALTER TABLE "user" DROP admin');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_IDENTIFIER_USERNAME ON "user" (username)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('DROP INDEX UNIQ_IDENTIFIER_USERNAME');
        $this->addSql('ALTER TABLE "user" ADD admin BOOLEAN NOT NULL');
        $this->addSql('ALTER TABLE "user" DROP username');
        $this->addSql('ALTER TABLE "user" DROP roles');
        $this->addSql('ALTER TABLE "user" DROP password');
    }
}
