<?php

namespace App\Service;

use App\Entity\ExpenseCategory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpClient\Exception\TransportException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Throwable;

/**
 * Service to process uploaded receipts using the OpenAI Vision API.
 */
class ExpenseProcessorService
{
    private string $openaiApiKey;
    private HttpClientInterface $httpClient;

    public function __construct(
        HttpClientInterface $httpClient,
        #[Autowire('%env(OPENAI_API_KEY)%')]
        string $openaiApiKey,
    ) {
        $this->httpClient = $httpClient;
        $this->openaiApiKey = $openaiApiKey;
    }

    /**
     * Sends the uploaded receipt image to OpenAI for structured parsing.
     *
     * @param UploadedFile $file the uploaded receipt file
     *
     * @return ParsedExpenseData the extracted expense data
     *
     * @throws OpenAIException if the API call fails or returns an invalid structure
     */
    public function processReceipt(UploadedFile $file): ParsedExpenseData
    {
        try {
            $base64Image = base64_encode(file_get_contents($file->getRealPath()));
            $mimeType = $file->getMimeType();
        } catch (Throwable $e) {
            throw new OpenAIException('Failed to read file contents: '.$e->getMessage());
        }

        $jsonStructure = json_encode([
            'amount' => 0.0,
            'category' => ExpenseCategory::Uncategorized->value,
        ]);
        $categoryList = array_map(fn (ExpenseCategory $e) => $e->value, ExpenseCategory::cases());
        $categoryList = implode(', ', $categoryList);

        $payload = [
            'model' => 'gpt-4o-mini', // Cost-effective model for Vision and Structured Output
            'messages' => [
                [
                    'role' => 'system',
                    'content' => "You are an expert financial receipt parser. Your sole purpose is to analyze the provided receipt image, extract the total monetary amount, and assign the most appropriate category. Always respond ONLY with a JSON object. The object MUST adhere to this structure: {$jsonStructure}. The 'amount' must be a float, and 'category' must be one of these: {$categoryList}.",
                ],
                [
                    'role' => 'user',
                    'content' => [
                        ['type' => 'text', 'text' => 'Analyze this receipt image and extract the total amount and the corresponding expense category.'],
                        [
                            'type' => 'image_url',
                            'image_url' => [
                                'url' => "data:{$mimeType};base64,{$base64Image}",
                            ],
                        ],
                    ],
                ],
            ],
            'response_format' => [
                'type' => 'json_object',
            ],
            'max_tokens' => 500,
        ];

        try {
            $response = $this->httpClient->request('POST', 'https://api.openai.com/v1/chat/completions', [
                'headers' => [
                    'Authorization' => 'Bearer '.$this->openaiApiKey,
                    'Content-Type' => 'application/json',
                ],
                'json' => $payload,
                'timeout' => 15, // Set a timeout to meet FR-020 target (under 10s, allowing buffer)
            ]);

            $statusCode = $response->getStatusCode();
            $content = $response->getContent(false); // Don't throw for 4xx/5xx errors yet

            if ($statusCode !== 200) {
                // Handle API error response (FR-021)
                $errorDetails = json_decode($content, true)['error']['message'] ?? 'Unknown OpenAI API Error.';
                throw new OpenAIException("OpenAI API call failed with status {$statusCode}: {$errorDetails}");
            }

            $data = json_decode($content, true);

            $jsonResponseText = $data['choices'][0]['message']['content'] ?? null;

            if (empty($jsonResponseText)) {
                throw new OpenAIException('OpenAI returned an empty or unreadable response.');
            }

            $parsedResult = json_decode($jsonResponseText, true);

            if (!isset($parsedResult['amount']) || !isset($parsedResult['category'])) {
                throw new OpenAIException('OpenAI failed to return the required structured data (amount and category missing).');
            }

            return new ParsedExpenseData(
                (float) $parsedResult['amount'],
                $parsedResult['category']
            );
        } catch (TransportException $e) {
            // Handle network/timeout errors (FR-021)
            throw new OpenAIException('Network error during OpenAI communication. Service may be unavailable: '.$e->getMessage(), 0, $e);
        } catch (Throwable $e) {
            // Catch any other errors (JSON decoding, DTO validation, etc.)
            throw new OpenAIException('Failed to process API response: '.$e->getMessage(), 0, $e);
        }
        //        finally {
        //            // NOTE: Per FR-022, the file is deleted immediately after extraction *and confirmation*.
        //            // Since this service only performs extraction, the deletion should ideally happen in the Controller
        //            // or a listener *after* the user confirms/saves the expense.
        //            // However, if the intent is to delete the temp file immediately after reading it, you would do it here,
        //            // but for safety (as UploadedFile handles the move/temp file lifetime), we leave it to Symfony's request cycle
        //            // or a dedicated file service to manage the original persistence/cleanup.
        //        }
    }
}
