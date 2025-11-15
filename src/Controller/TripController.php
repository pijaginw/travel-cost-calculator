<?php

namespace App\Controller;

use App\Entity\Trip;
use App\Entity\User;
use App\Form\TripType;
use App\Repository\TripRepository;
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
        /** @var User|null $user */
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
        /** @var User|null $user */
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

    /**
     * FR-009: Displays the detailed Trip Summary page.
     * FR-010: Displays the grand total cost and a list of all individual expenses.
     */
    #[Route('/{id}', name: 'app_trip_summary', methods: ['GET'])]
    public function summary(Trip $trip, TripRepository $tripRepository): Response
    {
        // Security check: Ensure the current user owns this trip
        if ($trip->getUser() !== $this->getUser()) {
            throw $this->createAccessDeniedException('You do not have permission to view this trip.');
        }

        // --- Calculation for FR-010 ---

        // This calculation should ideally be moved to the TripRepository
        // for better performance (a single SQL SUM query).
        $grandTotal = 0;
        foreach ($trip->getExpenses() as $expense) {
            // Note: Expense amount is a string (Type::DECIMAL) and must be converted for math.
            // Using bcmath or casting is necessary for accurate currency math in PHP.
            $grandTotal += (float) $expense->getAmount();
        }

        return $this->render('trip/summary.html.twig', [
            'trip' => $trip,
            'grand_total' => $grandTotal,
            'expense_count' => $trip->getExpenses()->count(),
        ]);
    }
}
