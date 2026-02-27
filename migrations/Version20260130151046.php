<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260130151046 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Chargement des fichiers SQL d'initialisation
        $sqlDirectory = __DIR__ . '/sql/';

        $this->addSql('CREATE TABLE album (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))');
        $this->loadSqlFile($sqlDirectory . 'album.sql');

        $this->addSql('CREATE TABLE "user" (id SERIAL NOT NULL, admin BOOLEAN NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, email VARCHAR(180) NOT NULL, PRIMARY KEY(id))');
        $this->loadSqlFile($sqlDirectory . 'user.sql');

        $this->addSql('CREATE TABLE media (id SERIAL NOT NULL, user_id INT DEFAULT NULL, album_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL,  PRIMARY KEY(id))');
        $this->loadSqlFile($sqlDirectory . 'media.sql');

        $this->addSql('ALTER TABLE media ADD created_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE media ADD updated_at TIMESTAMP(0) WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL');

        $this->addSql('CREATE INDEX IDX_6A2CA10CA76ED395 ON media (user_id)');
        $this->addSql('CREATE INDEX IDX_6A2CA10C1137ABCF ON media (album_id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
        $this->addSql('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C1137ABCF FOREIGN KEY (album_id) REFERENCES album (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE SCHEMA public');
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10CA76ED395');
        $this->addSql('ALTER TABLE media DROP CONSTRAINT FK_6A2CA10C1137ABCF');
        $this->addSql('DROP TABLE album');
        $this->addSql('DROP TABLE media');
        $this->addSql('DROP TABLE "user"');
    }


    /**
     * Lit un fichier SQL et l'exécute requête par requête pour éviter l'erreur de "multiple commands" de Postgres.
     */
    private function loadSqlFile(string $filePath): void
    {
        if (!file_exists($filePath)) {
            throw new \RuntimeException(sprintf('Le fichier SQL "%s" est introuvable.', $filePath));
        }

        $sqlContent = file_get_contents($filePath);

        // On sépare le fichier en plusieurs requêtes en utilisant le point-virgule comme délimiteur
        $queries = explode(';', $sqlContent);

        foreach ($queries as $query) {
            $query = trim($query);

            // On s'assure de ne pas envoyer de requêtes vides (souvent le cas après le dernier point-virgule du fichier)
            if ($query !== '') {
                $this->addSql($query);
            }
        }
    }
}
