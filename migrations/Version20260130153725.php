<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130153725 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE media ALTER path DROP NOT NULL');

        $sqlDirectory = __DIR__ . '/sql/';

        // Lecture et exécution des fichiers SQL
        // Tu peux rajouter une vérification file_exists() si tu veux être plus prudent
        $this->addSql(file_get_contents($sqlDirectory . 'album.sql'));
        $this->addSql(file_get_contents($sqlDirectory . 'user.sql'));
        $this->addSql(file_get_contents($sqlDirectory . 'media.sql'));
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE media ALTER path SET NOT NULL');
    }
}
