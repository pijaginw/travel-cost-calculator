<?php

namespace App\Service;

use App\Entity\ExpenseCategory;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Exception;

/**
 * Service to handle interaction with the external AI service for receipt processing.
 */
class ExpenseProcessorService
{
    // A placeholder for the actual OpenAI client (e.g., $this->openAIClient)
    private $openAIClient;

    public function __construct(/* OpenAIClient $openAIClient, ... */)
    {
        // $this->openAIClient = $openAIClient;
    }

    /**
     * FR-012, FR-013: Processes the uploaded receipt image via AI.
     * * @throws Exception if the API call fails or the response is invalid (US-011)
     * @return array ['amount' => (string), 'category' => (ExpenseCategory)]
     */
    public function processReceipt(UploadedFile $file): array
    {
        // 1. Convert the file to a base64 string or upload it to a temporary bucket.
        // 2. Call the OpenAI API (FR-012).

        // --- SIMULATED LOGIC START ---

        // Simulate an API failure for testing FR-017 / US-009 / US-011
        // if (rand(1, 10) === 1) {
        //     throw new Exception("External AI API is currently unavailable (HTTP 503).");
        // }

        // Simulate successful extraction (FR-013)
        $simulatedAmount = (string) (rand(100, 5000) / 100);
        $categories = ExpenseCategory::cases();
        $simulatedCategory = $categories[array_rand($categories)];

        return [
            'amount' => $simulatedAmount,
            'category' => $simulatedCategory,
        ];

        // --- SIMULATED LOGIC END ---
    }

    /**
     * FR-022: Deletes the temporary image file from the server.
     */
    public function deleteFile(UploadedFile $file): void
    {
        // Logic to delete the temporary file after extraction
        // unlink($file->getPathname());
    }
}
