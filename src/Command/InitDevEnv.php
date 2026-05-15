<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(name: "initDevEnv", description: "Initialise la base de données à l'identique de la prod 5.4")]
class InitDevEnv extends Command
{
    public function __construct(private readonly EntityManagerInterface $em)
    {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $conn = $this->em->getConnection();
        $sqlDirectory = __DIR__ . '/../../migrations/sql/';

        try {
            // Table Album
            $conn->executeQuery("CREATE TABLE album (id SERIAL NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id))");
            $this->loadSqlFile($conn, $sqlDirectory . 'album.sql');
            $conn->executeQuery("SELECT setval(pg_get_serial_sequence('album', 'id'), coalesce(max(id),0) + 1, false) FROM album");

            // Table User
            $conn->executeQuery('CREATE TABLE "user" (id SERIAL NOT NULL, admin BOOLEAN NOT NULL, name VARCHAR(255) NOT NULL, description TEXT DEFAULT NULL, email VARCHAR(180) NOT NULL, PRIMARY KEY(id))');
            $this->loadSqlFile($conn, $sqlDirectory . 'user.sql');
            $conn->executeQuery("SELECT setval(pg_get_serial_sequence('\"user\"', 'id'), coalesce(max(id),0) + 1, false) FROM \"user\"");

            // Table Media
            $conn->executeQuery("CREATE TABLE media (id SERIAL NOT NULL, user_id INT DEFAULT NULL, album_id INT DEFAULT NULL, path VARCHAR(255) NOT NULL, title VARCHAR(255) NOT NULL,  PRIMARY KEY(id))");
            $this->loadSqlFile($conn, $sqlDirectory . 'media.sql');
            // Recalibrage de l'incrémentation de postgresSQL MEDIA
            $conn->executeQuery("SELECT setval(pg_get_serial_sequence('media', 'id'), coalesce(max(id),0) + 1, false) FROM media");

            // Ajout des index et contraintes de l'ancienne version
            $conn->executeQuery('CREATE INDEX IDX_6A2CA10CA76ED395 ON media (user_id)');
            $conn->executeQuery('CREATE INDEX IDX_6A2CA10C1137ABCF ON media (album_id)');
            $conn->executeQuery('CREATE UNIQUE INDEX UNIQ_8D93D649E7927C74 ON "user" (email)');
            $conn->executeQuery('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10CA76ED395 FOREIGN KEY (user_id) REFERENCES "user" (id) NOT DEFERRABLE INITIALLY IMMEDIATE');
            $conn->executeQuery('ALTER TABLE media ADD CONSTRAINT FK_6A2CA10C1137ABCF FOREIGN KEY (album_id) REFERENCES album (id) NOT DEFERRABLE INITIALLY IMMEDIATE');

            $io->success('Environnement de dev initialisé avec succès (Données + Séquences) !');
            return Command::SUCCESS;

        } catch (\Exception $e) {
            $io->error('Erreur lors de l\'initialisation : ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    private function loadSqlFile($conn, string $filePath): void
    {
        if (!file_exists($filePath)) return;
        $sqlContent = file_get_contents($filePath);
        $queries = explode(';', $sqlContent);
        foreach ($queries as $query) {
            if (trim($query) !== '') {
                $conn->executeQuery($query);
            }
        }
    }
}
