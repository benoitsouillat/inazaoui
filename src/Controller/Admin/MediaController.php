<?php

namespace App\Controller\Admin;

use App\Entity\Media;
use App\Entity\User;
use App\Form\MediaType;
use App\Service\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class MediaController extends AbstractController
{

    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly PaginationService $paginationService
    )
    {}

    #[Route('/admin/media', name: 'admin_media_index', methods: Request::METHOD_GET)]
    public function index(Request $request): Response
    {
        $page = $request->query->getInt('page', 1);
        $limit = 25;
        $criteria = [];

        if (!$this->isGranted('ROLE_ADMIN')) {
            /** @var User $user */
            $user = $this->getUser();
            $criteria['user'] = $user;
        }

        $medias = $this->manager->getRepository(Media::class)->findBy(
            $criteria,
            ['id' => 'ASC'],
            $limit,
            $limit * ($page - 1)
        );
        $totalMedias = $this->manager->getRepository(Media::class)->countAllByCriteria($criteria);
        $totalPages = $this->paginationService->calculateTotalPages($totalMedias, $limit);

        return $this->render('admin/media/index.html.twig', [
            'medias' => $medias,
            'totalPages' => $totalPages,
            'page' => $page
        ]);
    }

    #[Route('/admin/media/add', name: 'admin_media_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function add(Request $request): Response
    {
        $media = new Media();
        $form = $this->createForm(MediaType::class, $media, ['is_admin' => $this->isGranted('ROLE_ADMIN')]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if (!$this->isGranted('ROLE_ADMIN')) {
                /** @var User $user */
                $user = $this->getUser();
                $media->setUser($user);
            }
            $this->manager->persist($media);
            $this->manager->flush();

            return $this->redirectToRoute('admin_media_index');
        }

        return $this->render('admin/media/add.html.twig', ['form' => $form->createView()]);
    }

    #[Route('/admin/media/delete/{id}', name: 'admin_media_delete', methods: Request::METHOD_GET)]
    public function delete(Media $media): Response
    {
        $this->manager->remove($media);
        $this->manager->flush();

        return $this->redirectToRoute('admin_media_index');
    }
}
