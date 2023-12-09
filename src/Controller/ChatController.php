<?php

namespace App\Controller;

use AllowDynamicProperties;
use App\Service\FineTuningService;
use App\Service\FunctionCallService;
use App\Structure\FunctionCallStructure;
use Exception;
use OpenAI;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[AllowDynamicProperties] #[Route('/messagerie', name: 'messagerie_')]
class ChatController extends AbstractController
{
    private array $messages = [];

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
        $donnees = json_decode($request->getContent());
        $codeRetour = Response::HTTP_OK;
        $this->messages = [
            [
                'role' => 'system',
                'content' => 'Voici les informations de l\'eleve idCommande:' . $donnees->idCommande . 'Tu es un Chatbot pour le Centre Européen de Formation. Tu réponds aux élèves qui ont souscrit a notre ecole. Soit toujours cordial avec les formules de politesse et finit tes réponses par "Le service administratif."'
            ],
            ['role' => 'user', 'content' => $donnees->message]
        ];

        try {
            $retour = $this->recupererReponseIA();
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $retour = $e->getMessage();
            $codeRetour = Response::HTTP_INTERNAL_SERVER_ERROR;
        }

        return new JsonResponse($retour, $codeRetour);
    }


    /**
     * @return array
     */
    public function recupererReponseIA(): array
    {
        $retour = [];

        $reponseIA = $this->openAiClient->chat()->create([
            'model' => 'gpt-3.5-turbo-1106',
            'messages' => $this->messages,
            'functions' => FunctionCallStructure::FONCTION_LISTES,
        ]);
        foreach ($reponseIA->choices as $reponse) {
            if ($reponse->finishReason == 'function_call') {
                $retour = $this->gestionAppelFonction($reponse);
            } else {
                $retour['text'] = $reponse->message->content;
                $retour['type'] = FunctionCallStructure::TYPE_TEXT;
            }
        }

        return $retour;
    }

    /**
     * @param $reponse
     *
     * @return array
     */
    public function gestionAppelFonction($reponse): array
    {
        $nomFonction = $reponse->message->functionCall->name;
        $arguments = json_decode($reponse->message->functionCall->arguments, true);
        $retour = $this->functionCallService->appelerFonction($nomFonction, $arguments);

        $this->messages[] = ['role' => 'function', 'name' => $nomFonction, 'content' => $retour['infos']];

        return $this->recupererReponseIA();
    }
}