<?php

namespace App\Controller;

use App\Entity\Album;
use App\Entity\Media;
use App\Entity\User;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="home")
     */
    public function home()
    {
        return $this->render('front/home.html.twig');
    }

    /**
     * @Route("/guests", name="guests")
     */
    public function guests(ManagerRegistry $registry)
    {
        $guests = $registry->getRepository(User::class)->findBy(['admin' => false]);
        return $this->render('front/guests.html.twig', [
            'guests' => $guests
        ]);
    }

    /**
     * @Route("/guest/{id}", name="guest")
     */
    public function guest(ManagerRegistry $registry, int $id)
    {
        $guest = $registry->getRepository(User::class)->find($id);
        return $this->render('front/guest.html.twig', [
            'guest' => $guest
        ]);
    }

    /**
     * @Route("/portfolio/{id}", name="portfolio")
     */
    public function portfolio(ManagerRegistry $registry, ?int $id = null)
    {
        $albums = $registry->getRepository(Album::class)->findAll();
        $album = $id ? $registry->getRepository(Album::class)->find($id) : null;
        $user = $registry->getRepository(User::class)->findOneByAdmin(true);

        $medias = $album
            ? $registry->getRepository(Media::class)->findByAlbum($album)
            : $registry->getRepository(Media::class)->findByUser($user);
        return $this->render('front/portfolio.html.twig', [
            'albums' => $albums,
            'album' => $album,
            'medias' => $medias
        ]);
    }

    /**
     * @Route("/about", name="about")
     */
    public function about()
    {
        return $this->render('front/about.html.twig');
    }
}