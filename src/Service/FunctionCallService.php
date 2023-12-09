<?php

// Service permettant l'appel de fonction à l'interieur de cette classe (piste de refacto en interfaces pour ne pas avoir de
// logique métier à l'intérieur de celle-ci )

namespace App\Service;

use AllowDynamicProperties;
use App\Service\Api\ApiExampleService;
use App\Structure\FunctionCallStructure;
use Exception;
use Psr\Log\LoggerInterface;

#[AllowDynamicProperties] class FunctionCallService
{
    /**
     * @param ApiExampleService $apiExampleService
     * @param LoggerInterface   $logger
     */
    public function __construct(ApiExampleService $apiExampleService, LoggerInterface $logger)
    {
        $this->apiExampleService = $apiExampleService;
        $this->logger = $logger;
    }

    /**
     * @param $nomFonction
     * @param $arguments
     *
     * @return array
     */
    public function appelerFonction($nomFonction, $arguments): array
    {
        if (is_callable([$this, $nomFonction])) {
            return $this->$nomFonction(...$arguments);
        } else {
            $this->logger->error('Fonction innexistante: ' . $nomFonction);
            return [
                'type' => FunctionCallStructure::TYPE_ERREUR,
                'infos' => 'Pas de fonction correspondante trouvée.'
            ];
        }
    }

    /**
     * @param int $argumentExample
     *
     * @return array
     */
    public function functionExample(int $argumentExample): array
    {
        try {
            $test = $this->apiExampleService->functionExample($argumentExample);

            return [
                'type' => FunctionCallStructure::TYPE_ACTION,
                'fonction' => 'functionExample',
                'infos' => 'Infos example a OpenAI '.$test->infos
            ];
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return [
                'type' => FunctionCallStructure::TYPE_ERREUR,
                'infos' => $exception->getMessage()
            ];
        }
    }
}

