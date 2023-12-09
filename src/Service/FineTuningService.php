<?php

namespace App\Service;

use AllowDynamicProperties;
use Exception;
use LLPhant\Chat\Enums\OpenAIChatModel;
use OpenAI;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

/**
 * Class FineTuningService
 */
#[AllowDynamicProperties]
class FineTuningService
{
    /**
     * @param LoggerInterface       $logger
     * @param ParameterBagInterface $parameterBag
     *
     * @throws Exception
     */
    public function __construct(
        LoggerInterface $logger,
        ParameterBagInterface $parameterBag
    ) {
        $this->logger = $logger;
        $this->openAiClient = OpenAI::client($parameterBag->get('app.open_ai_token'));
    }

    /**
     * Creates a job that fine-tunes a specified model from a given dataset.
     *
     * @param string $trainingFile
     *
     * @return array|\array{id: string, object: string, model: string, created_at: int, finished_at: ?int, fine_tuned_model: ?string, hyperparameters: array{n_epochs: int}, organization_id: string, result_files: array, status: string, validation_file: ?string, training_file: string, trained_tokens: ?int}
     */
    public function createJob(string $trainingFile): array
    {
        try {
            $response = $this->openAiClient->fineTuning()->createJob([
                'training_file' => $trainingFile,
                'validation_file' => null,
                'model' => OpenAIChatModel::Gpt35Turbo->getModelName(),
                'hyperparameters' => [
                    'n_epochs' => 4,
                ],
                'suffix' => null,
            ]);
            return $response->toArray();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return ['error' => $exception->getMessage()];
        }
    }

    /**
     * List your organization's fine-tuning jobs.
     *
     * @param int $limit
     *
     * @return array|\array{object: string, data: array, has_more: bool}
     */
    public function listJobs(int $limit = 20): array
    {
        try {
            $response = $this->openAiClient->fineTuning()->listJobs([
                'limit' => $limit
            ]);
            return $response->toArray();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return ['error' => $exception->getMessage()];
        }
    }

    /**
     * Get info about a fine-tuning job.
     *
     * @param string $id
     *
     * @return array|\array{id: string, object: string, model: string, created_at: int, finished_at: ?int, fine_tuned_model: ?string, hyperparameters: array{n_epochs: int}, organization_id: string, result_files: array, status: string, validation_file: ?string, training_file: string, trained_tokens: ?int}
     */
    public function retrieveJob(string $id): array
    {
        try {
            $response = $this->openAiClient->fineTuning()->retrieveJob($id);
            return $response->toArray();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return ['error' => $exception->getMessage()];
        }
    }

    /**
     * Immediately cancel a fine-tune job.
     *
     * @param string $id
     *
     * @return array|\array{id: string, object: string, model: string, created_at: int, finished_at: ?int, fine_tuned_model: ?string, hyperparameters: array{n_epochs: int}, organization_id: string, result_files: array, status: string, validation_file: ?string, training_file: string, trained_tokens: ?int}
     */
    public function cancelJob(string $id): array
    {
        try {
            $response = $this->openAiClient->fineTuning()->cancelJob($id);
            return $response->toArray();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return ['error' => $exception->getMessage()];
        }
    }

    /**
     * Get status updates for a fine-tuning job.
     *
     * @param string $id
     * @param int    $limit
     *
     * @return array|\array{object: string, data: array<int, array{object: string, id: string, created_at: int, level: string, message: string, data: array{step: int, train_loss: float, train_mean_token_accuracy: float}
     */
    public function listJobEvents(string $id, int $limit = 20): array
    {
        try {
            $response = $this->openAiClient->fineTuning()->listJobEvents($id, ['limit' => $limit]);
            return $response->toArray();
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return ['error' => $exception->getMessage()];
        }
    }
}
