<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Form\GuestType;
use App\Service\GuestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
final class GuestController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager
    )
    {}

    #[Route('/admin/guest', name: 'admin_guest_index', methods: Request::METHOD_GET)]
    public function index(): Response
    {
        $guests = $this->manager->getRepository(User::class)->findAll();

        return $this->render('admin/guest/index.html.twig', ['guests' => $guests]);
    }

    #[Route('/admin/guest/add', name: 'admin_guest_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function add(Request $request, GuestService $guestService): Response
    {
        $form = $this->createForm(GuestType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $guest = $guestService->addUser($form->getData());

            //Envoyer email de création de mot de passe

            $this->addFlash('success', 'L\'invité a été ajouté avec succès.');

            return $this->redirectToRoute('admin_guest_index');
        }

        return $this->render('admin/guest/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
