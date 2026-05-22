<?php

namespace App\Factory;

use App\Entity\Media;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Zenstruck\Foundry\Persistence\PersistentObjectFactory;

/**
 * @extends PersistentObjectFactory<Media>
 */
final class MediaFactory extends PersistentObjectFactory
{
    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#factories-as-services
     *
     * @todo inject services if required
     */
    public function __construct()
    {
    }

    #[\Override]
    public static function class(): string
    {
        return Media::class;
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#model-factories
     *
     */
    #[\Override]
    protected function defaults(): array|callable
    {

        return [
            'album' => AlbumFactory::random(),
            'user' => UserFactory::random(),
            //'file' => $this->getRandomFixturesImages(), // On appele pas la propriété file pour court-circuiter Vich et forcer le nom original pour les fixtures
            'path' => $this->copyRandomFixtureImage(),
            'createdAt' => self::faker()->dateTime(),
            'title' => self::faker()->text(),
            'updatedAt' => self::faker()->dateTime(),
        ];
    }

    /**
     * @see https://symfony.com/bundles/ZenstruckFoundryBundle/current/index.html#initialization
     */
    #[\Override]
    protected function initialize(): static
    {
        return $this
            // ->afterInstantiate(function(Media $media): void {})
        ;
    }

    private function copyRandomFixtureImage(): string
    {
        $sourceDirectory = __DIR__ . '/../../assets/images/fixtures';
        $images = glob($sourceDirectory . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);

        if (empty($images)) {
            throw new \RuntimeException(sprintf('Aucune image de test trouvée dans "%s".', $sourceDirectory));
        }

        $randomImage = $images[array_rand($images)];
        $fileName = basename($randomImage);

        $uploadDir = __DIR__ . '/../../public/uploads';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0775, true);
        }

        copy($randomImage, $uploadDir . '/' . $fileName);

        return $fileName;
    }

    private function getRandomFixturesImages(): UploadedFile
    {
        $sourceDirectory = __DIR__ . '/../../assets/images/fixtures';
        $images = glob($sourceDirectory . '/*.{jpg,jpeg,png,gif}', GLOB_BRACE);


        if (empty($images)) {
            throw new \RuntimeException(sprintf('Aucune image de test trouvée dans le dossier "%s".', $sourceDirectory));
        }

        $randomImage = $images[array_rand($images)];
        $tempFilename = sys_get_temp_dir() . '/' . uniqid('fixture_') . '_' . basename($randomImage);

        copy($randomImage, $tempFilename);

        return new UploadedFile(
            $tempFilename,
            basename($randomImage),
            mime_content_type($tempFilename) ?: 'image/jpeg',
            null,
            true // <-- LE MODE TEST : Demande à Symfony d'ignorer "is_uploaded_file()"
        );
    }
}
