<?php

namespace App\Controller\Admin;

use App\Entity\User;
use App\Event\GuestEvent;
use App\Form\GuestType;
use App\Service\GuestService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_ADMIN")]
final class GuestController extends AbstractController
{
    public function __construct(
        private readonly EntityManagerInterface $manager,
        private readonly GuestService $service
    )
    {}

    #[Route('/admin/guest', name: 'admin_guest_index', methods: Request::METHOD_GET)]
    public function index(): Response
    {
        $guests = $this->service->getGuests('ROLE_USER');

        return $this->render('admin/guest/index.html.twig', ['guests' => $guests]);
    }

    #[Route('/admin/guest/add', name: 'admin_guest_add', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function add(Request $request): Response
    {
        $form = $this->createForm(GuestType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->service->addUser($form->getData());
                $this->addFlash('success', 'L\'invité a été ajouté avec succès.');

                return $this->redirectToRoute('admin_guest_index');
            }
            catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('admin/guest/add.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/guest/{id}/edit', name: 'admin_guest_update', methods: [Request::METHOD_GET, Request::METHOD_POST])]
    public function edit(Request $request, User $guest): Response
    {
        $form = $this->createForm(GuestType::class, $guest);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->service->editUser($form->getData());
                $this->addFlash('success', 'L\'invité a été modifié avec succès.');

                return $this->redirectToRoute('admin_guest_index');
            }
            catch (\Exception $e) {
                $this->addFlash('danger', $e->getMessage());
            }
        }

        return $this->render('admin/guest/update.html.twig', [
            'guest' => $guest,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/admin/guest/{id}/delete', name: 'admin_guest_delete', methods: Request::METHOD_GET)]
    public function delete(User $guest, EventDispatcherInterface $dispatcher): Response
    {
        $dispatcher->dispatch(new GuestEvent($guest), GuestEvent::GUEST_DELETED);

        return $this->redirectToRoute('admin_guest_index');
    }

    #[Route('/admin/guest/{id}/toggle', name: 'admin_guest_toggle', methods: Request::METHOD_GET)]
    public function toggle(User $guest, EventDispatcherInterface $dispatcher): Response
    {
        $guest->setActive(!$guest->isActive());
        $dispatcher->dispatch(new GuestEvent($guest), GuestEvent::GUEST_EDITED);
        $this->manager->persist($guest);
        $this->manager->flush();
        $this->addFlash('success', sprintf('Le compte de l\'invité a bien été %s.', $guest->isActive() ? 'activé' : 'désactivé'));

        return $this->redirectToRoute('admin_guest_index');
    }
}
