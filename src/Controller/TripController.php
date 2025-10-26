<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\User;
use App\Form\TripType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/trip')]
class TripController extends AbstractController
{
    #[Route('/new', name: 'app_trip_new', methods: ['GET', 'POST'])]
    #[IsGranted('ROLE_USER')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $trip = new Trip();

        // FR-004: Associate the trip with the currently logged-in user.
        // We fetch the user object and set it on the trip.
        // This ensures the trip is owned by the user creating it.
        /** @var null|User $user */
        $user = $this->getUser();
        if (!$user) {
            // This should not happen due to #[IsGranted], but it's good practice.
            $this->addFlash('error', 'You must be logged in to create a trip.');
            return $this->redirectToRoute('app_login');
        }
        $trip->setUser($user);

        $form = $this->createForm(TripType::class, $trip);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($trip);

            $entityManager->flush();

            $this->addFlash('success', 'Trip created successfully!');

            // US-005 Acceptance Criteria 6: Redirect to the trip dashboard upon successful creation.
            // @todo create dashboard route and redirect to it
            return $this->redirectToRoute('app_dashboard');
        }

        return $this->render('trip/new.html.twig', [
            'trip' => $trip,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/list', name: 'app_dashboard', methods: ['GET'])]
    #[IsGranted('ROLE_USER')]
    public function list(): Response
    {
        /** @var null|User $user */
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'You must be logged in to view your trips.');
            return $this->redirectToRoute('app_login');
        }

        $trips = $user->getTrips();

        return $this->render('trip/list.html.twig', [
            'trips' => $trips,
        ]);
    }
}
