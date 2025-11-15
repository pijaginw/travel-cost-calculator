<?php

namespace App\Controller;

use App\Entity\Expense;
use App\Entity\ExpenseCategory;
use App\Entity\Trip;
use App\Entity\User;
use App\Form\ExpenseType;
use App\Service\ExpenseProcessorService;
use App\Service\UploadLimitService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/trips/{id}/expenses')]
#[IsGranted('IS_AUTHENTICATED_FULLY')]
class ExpenseController extends AbstractController
{
    /**
     * Handles both the expense file upload/AI processing and the final saving of the expense.
     * This method fulfills FR-011, FR-012, FR-013, FR-015, FR-016, FR-017, FR-019, US-007, US-008, US-009, US-011, US-012.
     */
    #[Route('/add', name: 'app_expense_add', methods: ['GET', 'POST'])]
    public function add(
        Trip $trip,
        Request $request,
        EntityManagerInterface $entityManager,
        ExpenseProcessorService $processor,
        UploadLimitService $limitService
    ): Response {
        /** @var null|User $user */
        $user = $this->getUser();
        $expense = new Expense();
        $expense->setTrip($trip);
        // This form is used for manual entry (PATH C) and the final review/save (PATH A)
        $form = $this->createForm(ExpenseType::class, $expense);

        // --- 1. Detect File Upload Action (From the manually built form in Twig) ---
        $uploadedFile = $request->files->get('receipt_image');
        $isUploadAttempt = $request->request->has('upload_submit'); // Check for the submit button name

        // --- PATH B: FILE UPLOAD (AI Processing) ---
        if ($isUploadAttempt && $uploadedFile) {

            // FIX 1: Ensure CSRF token for the manually created upload form is valid
            if (!$this->isCsrfTokenValid('upload_receipt', $request->request->get('_csrf_token'))) {
                $this->addFlash('danger', 'Invalid security token for upload.');
                return $this->redirectToRoute('app_expense_add', ['id' => $trip->getId()]);
            }

            // FR-023 / US-012: Enforce Monthly Upload Limit
            if (!$limitService->canUserUploadReceipt($user)) {
                $this->addFlash('danger', 'You have reached your monthly limit of 100 receipt uploads.');
                return $this->redirectToRoute('app_trip_summary', ['id' => $trip->getId()]);
            }

            // Basic FR-011 validation: JPG or PNG
            $mimeType = $uploadedFile->getMimeType();
            if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
                $this->addFlash('danger', 'Invalid file type. Only JPG and PNG are supported.');
                return $this->redirectToRoute('app_expense_add', ['id' => $trip->getId()]);
            }

            try {
                // FR-012, FR-013: AI Service processes the image
                $aiResult = $processor->processReceipt($uploadedFile);

                // FR-022: File cleanup logic would happen after the user confirms/saves.

                // FR-015: Create a *new* expense instance pre-filled with AI data for user review
                $reviewExpense = new Expense();
                $reviewExpense->setTrip($trip);
                $reviewExpense->setAmount($aiResult->getAmount());
                $reviewExpense->setCategory(ExpenseCategory::tryFrom($aiResult->getCategory()));

                $entityManager->persist($reviewExpense);
                $entityManager->flush();

                return $this->redirectToRoute('app_expense_add', ['id' => $trip->getId()]);

            } catch (Exception $e) {
                // FR-017, US-009, US-011: Handle AI failure or API unavailability
                $this->addFlash('warning', 'AI service failed to extract data: ' . $e->getMessage() . '. Please enter the expense details manually.');

                $form = $this->createForm(ExpenseType::class, $expense);

                return $this->render('expense/add.html.twig', [
                    'trip' => $trip,
                    'form' => $form->createView(),
                    'show_manual_entry' => true, // <-- Add this new variable
                ]);
            }
        }

        // --- 2. Handle Manual Expense Save (From the Symfony Form) ---
        // This block handles the submission of the second form in add.html.twig OR the submission from the review form.
        $form->handleRequest($request);

        // --- PATH A: FINAL EXPENSE SAVE (Manual or Review Correction) ---
        if ($form->isSubmitted() && $form->isValid()) {
            // FR-016, US-008, US-009: Save the corrected/manually entered expense
            $entityManager->persist($expense);
            $entityManager->flush();

            $this->addFlash('success', 'Expense successfully added to ' . $trip->getTripName() . '!');

            // FR-019: User remains on the "Add Expense" page
            return $this->redirectToRoute('app_expense_add', ['id' => $trip->getId()]);
        }

        // --- PATH C: INITIAL LOAD (GET) OR FALLBACK TO MANUAL ENTRY ---
        // This is the fallback for GET requests. Note: Manual entry fallback is now handled in the catch block.
        return $this->render('expense/add.html.twig', [
            'trip' => $trip,
            'form' => $form->createView(),
            'show_manual_entry' => false,
        ]);
    }

    /**
     * Deletes an individual expense.
     * This method fulfills FR-018 and US-010.
     */
    #[Route('/{expenseId}/delete', name: 'app_expense_delete', methods: ['POST'])]
    public function delete(
        Trip $trip,
        int $expenseId,
        Request $request,
        EntityManagerInterface $entityManager
    ): Response {
        // Use $expenseId to fetch the specific Expense
        $expense = $entityManager->getRepository(Expense::class)->find($expenseId);

        if (!$expense || $expense->getTrip() !== $trip) {
            throw $this->createNotFoundException('The expense does not exist or does not belong to this trip.');
        }

        // CSRF Token check is required for secure deletion (similar to US-010 confirmation)
        if ($this->isCsrfTokenValid('delete' . $expense->getId(), $request->request->get('_token'))) {
            // US-010: Confirming the action permanently removes the expense
            $entityManager->remove($expense);
            $entityManager->flush();

            // US-010: Trip's total cost is immediately recalculated and updated (handled by ORM/app logic)
            $this->addFlash('success', 'Expense successfully deleted.');
        } else {
            // The CSRF token check serves as the confirmation step (FR-018 / US-010)
            $this->addFlash('danger', 'Invalid security token for deletion.');
        }

        return $this->redirectToRoute('app_trip_summary', ['id' => $trip->getId()]);
    }
}
