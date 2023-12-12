<?php

namespace App\Controller;

use App\Enumeration\DocumentType;
use App\Enumeration\ResponseType;
use App\Service\FineTuningService;
use App\Service\FunctionCallService;
use Exception;
use OpenAI;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/chat', name: 'chat_')]
class ChatController extends AbstractController
{

    /**
     * @var array
     */
    protected array $messages = [];

    /**
     * @var FineTuningService
     */
    protected FineTuningService $fineTuningService;

    /**
     * @var OpenAI\Client
     */
    protected OpenAI\Client $openAiClient;

    /**
     * @var FunctionCallService
     */
    protected FunctionCallService $functionCallService;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;


    /**
     * @param FineTuningService     $fineTuningService
     * @param ParameterBagInterface $parameterBag
     * @param FunctionCallService   $functionCallService
     * @param LoggerInterface       $logger
     */
    public function __construct(
        FineTuningService $fineTuningService,
        ParameterBagInterface $parameterBag,
        FunctionCallService $functionCallService,
        LoggerInterface $logger
    ) {
        $this->fineTuningService = $fineTuningService;
        $this->openAiClient = OpenAI::client($parameterBag->get('app.open_ai_token'));
        $this->functionCallService = $functionCallService;
        $this->logger = $logger;
    }


    /**
     * @param Request $request
     *
     * @return JsonResponse
     */
    #[Route('/prompt', name: 'prompt', methods: 'POST')]
    public function prompt(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent());
        $responseCode = Response::HTTP_OK;
        $this->messages = [
            [
                'role' => 'system',
                'content' => 'Instruction for the chatbot'
            ],
            ['role' => 'user', 'content' => $data->message]
        ];

        try {
            $result = $this->getAiResponse();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $result = $e->getMessage();
            $responseCode = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse($result, $responseCode);
    }


    /**
     * @return array
     */
    public function getAiResponse(): array
    {
        $result = [];

        $AiResponse = $this->openAiClient->chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
            'messages' => $this->messages,
            'functions' => [
                [
                    'name' => 'sendDocument',
                    'description' => 'Send document with the name of it.',
                    'parameters' => [
                        'type' => 'object',
                        'properties' => [
                            'idClient' => [
                                'type' => 'integer',
                                'description' => 'IdClient given by the system'
                            ],
                            'documentName' => [
                                'type' => 'string',
                                'description' => 'Name of the document. Existing document: ' . DocumentType::getExistingDocument()
                            ]
                        ]
                    ]
                ],
            ],
        ]);

        foreach ($AiResponse->choices as $reponse) {
            if ($reponse->finishReason == 'function_call') {
                $result = $this->functionCall($reponse);
            } else {
                $result['text'] = $reponse->message->content;
                $result['type'] = ResponseType::TEXT->value;
            }
        }

        return $result;
    }


    /**
     * @param $reponse
     *
     * @return array
     */
    public function functionCall($reponse): array
    {
        $functionName = $reponse->message->functionCall->name;
        $arguments = json_decode($reponse->message->functionCall->arguments, true);
        $result = $this->functionCallService->appelerFonction($functionName, $arguments);

        $this->messages[] = ['role' => 'function', 'name' => $functionName, 'content' => $result['infos']];

        $result['text'] = $this->getAiResponse()['text'];

        return $result;
    }
}