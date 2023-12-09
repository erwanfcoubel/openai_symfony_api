<?php

// Fichier Service d'une api fictive. Cela permet le call vers n'importe qu'elle API utilisant les token JWT

namespace App\Service\Api;

use AllowDynamicProperties;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;


#[AllowDynamicProperties] class ApiExampleService extends AbstractApiService
{
    const URL_EXAMPLE = '/route-example/%s';

    /**
     * @param ParameterBagInterface   $parametres
     * @param HttpClientInterface     $client
     * @param AuthentificationService $authentificationService
     * @param LoggerInterface         $logger
     */
    public function __construct(
        ParameterBagInterface $parametres,
        HttpClientInterface $client,
        AuthentificationService $authentificationService,
        LoggerInterface $logger
    ) {
        parent::__construct($client, $logger);
        $this->baseUrl = $parametres->get('API_EXAMPLE_HOST');
        $this->utilisateur = $parametres->get('API_EXAMPLE_USER');
        $this->motDePasse = $parametres->get('API_EXAMPLE_PASS');
        $this->client = $client;
        $this->jeton = $authentificationService->recupererJeton($this->baseUrl, $this->utilisateur, $this->motDePasse);
    }

    /**
     * @param int $argumentExample
     *
     * @return int|mixed|null
     */
    public function functionExample(int $argumentExample): mixed
    {
        $retour = $this->call(route: sprintf(self::URL_EXAMPLE, $argumentExample), token: $this->jeton);

        return json_decode($retour->getContenu());
    }
}