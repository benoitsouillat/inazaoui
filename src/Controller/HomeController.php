<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use App\Service\GuestService;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class HomeController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly PaginationService $paginationService,
    )
    {}

    #[Route("/", name: "home", methods: Request::METHOD_GET)]
    public function home(): Response
    {
        return $this->render('front/home.html.twig');
    }

    #[Route("/guests", name: "guests", methods: Request::METHOD_GET)]
    public function guests(GuestService $guestService): Response
    {
        $guests = $guestService->getActiveGuests();

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
    public function portfolio(Request $request, ?Album $album = null): Response
    {
        $page = null;
        $totalPages = null;
        $albums = $this->manager->getRepository(Album::class)->findAll();
        if ($album)
        {
            $medias = $this->manager->getRepository(Media::class)->findActiveByAlbum($album);
        }
        else {
            $page = $request->query->getInt('page', 1);
            $limit = 24;
            $medias = $this->manager->getRepository(Media::class)->findAllActiveByPage($page, $limit);
            $totalMedias = $this->manager->getRepository(Media::class)->countAllActive();
            $totalPages = $this->paginationService->calculateTotalPages($totalMedias, $limit);
        }

        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias,
            'page' => $page,
            'totalPages' => $totalPages,
        ]);
    }

    #[Route("/about", name: "about", methods: Request::METHOD_GET)]
    public function about(): Response
    {
        return $this->render('front/about.html.twig');
    }
}
