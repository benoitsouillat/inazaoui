<?php

namespace App\Entity;

use App\Repository\MediaRepository;
use Doctrine\ORM\Mapping as ORM;
use Gedmo\Timestampable\Traits\TimestampableEntity;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Attribute as Vich;
use Symfony\Component\Validator\Constraints as Assert;

#[Vich\Uploadable]
#[ORM\Entity(repositoryClass: MediaRepository::class)]
class Media
{
    use TimestampableEntity;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: User::class, fetch: "EAGER", inversedBy: "medias")]
    private ?User $user = null;

    #[ORM\ManyToOne(targetEntity: Album::class, fetch: "EAGER")]
    private ?Album $album = null;

    #[Assert\File(
        maxSize: '2M',
        mimeTypes: ['image/jpeg', 'image/png', 'image/gif'],
        mimeTypesMessage: 'Merci d\'utiliser une image (JPEG, PNG, GIF) n\'excédant pas 2MB.'
    )]
    #[Vich\UploadableField(mapping: 'medias', fileNameProperty: 'path')]
    private ?File $file = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $path = null;

    #[ORM\Column]
    private string $title;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?OldUser
    {
        return $this->user;
    }

    public function setUser(?OldUser $user): static
    {
        $this->user = $user;
        return $this;
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path = null): static
    {
        $this->path = $path;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;
        return $this;
    }

    public function getFile(): ?File
    {
        return $this->file;
    }

    public function setFile(?File $file = null): static
    {
        $this->file = $file;

        if (null !== $file) {
            $this->updatedAt = new \DateTimeImmutable();
        }

        return $this;
    }

    public function getAlbum(): ?Album
    {
        return $this->album;
    }

    public function setAlbum(?Album $album): static
    {
        $this->album = $album;
        return $this;
    }
}
