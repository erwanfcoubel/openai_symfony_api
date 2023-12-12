<?php

namespace App\Service\Api;

use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

class ApiExampleService extends AbstractApiService
{

    const URL_EXAMPLE = '/url-example/%s';

    /**
     * @var string
     */
    protected string $password;

    /**
     * @var string
     */
    protected string $user;

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $client;

    /**
     * @var mixed|string
     */
    protected mixed $token;

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
        $this->defaultUrl = $parametres->get('API_EXAMPLE_HOST');
        $this->user = $parametres->get('API_EXAMPLE_USER');
        $this->password = $parametres->get('API_EXAMPLE_PASS');
        $this->client = $client;
        $this->token = $authentificationService->getToken($this->defaultUrl, $this->user, $this->password);
    }

    /**
     * @param int $argumentsExample
     *
     * @return int|mixed|null
     */
    public function fonctionExample(int $argumentsExample): mixed
    {
        $result = $this->call(route: sprintf(self::URL_EXAMPLE, $argumentsExample), token: $this->token);

        return json_decode($result->getContent());
    }
}