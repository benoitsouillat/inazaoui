<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
    )
    {}

    #[Route("/", name: "home", methods: Request::METHOD_GET)]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route("/guests", name: "guests", methods: Request::METHOD_GET)]
    public function guests(): Response
    {
        $guests = $this->manager->getRepository(User::class)->findBy(['admin' => false]);

        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    #[Route("/guest/{id}", name: "guest", requirements: ['id' => '\d+'], methods: Request::METHOD_GET)]
    public function guest(User $guest): Response
    {
        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    #[Route("/portfolio/{id?}", name: "portfolio", methods: Request::METHOD_GET)]
    public function portfolio(?Album $album = null): Response
    {
        $albums = $this->manager->getRepository(Album::class)->findAll();
        $user = $this->manager->getRepository(User::class)->findOneByAdmin(true);

        $medias = $album
            ? $this->manager->getRepository(Media::class)->findByAlbum($album)
            : $this->manager->getRepository(Media::class)->findByUser($user);
        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    #[Route("/about", name: "about", methods: Request::METHOD_GET)]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
