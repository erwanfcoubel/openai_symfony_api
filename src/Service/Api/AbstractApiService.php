<?php

//Fichier créer par mon collegue sur une autre API, que j'ai repris et modifier pour coller à ce projet.
//Je travaille actuellement pour le refactoriser et pourquoi pas trouver une nouvelle structure de fichier.

namespace App\Service\Api;

use AllowDynamicProperties;
use App\Entity\ApiReponse;
use App\Enumeration\HttpHeaders;
use App\Exception\Api\ExceptionReponseApiKo;
use App\Exception\Api\ExceptionReponseApiNull;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\DecodingExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\RedirectionExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

/**
 * Classe Abstraite permettant de construire un service ayant pour but de communiquer avec une API
 */
#[AllowDynamicProperties] abstract class AbstractApiService
{
    /**
     * Valeur à définir dans le constructeur des ApiService issus de cette classe.
     *
     * @var string
     */
    protected string $baseUrl = '';

    /**
     * @var array $headersParDefaut
     */
    protected array $headersParDefaut = [];

    /**
     * @param HttpClientInterface $httpClient
     * @param LoggerInterface     $graylogLogger
     */
    public function __construct(HttpClientInterface $httpClient, LoggerInterface $graylogLogger)
    {
        $this->httpClient = $httpClient;
        $this->logger = $graylogLogger;
    }


    /**
     * Retournes un objet ResponseInterface contenant les éléments de la réponse si l'appel est bien effectué.
     * null si une Exception est rencontrée.
     * Si vous souhaitez garantir le caractère non null, pensez à utiliser la méthode validerReponse()
     *
     * @param string      $route      le Endpoint ciblé
     * @param array       $parametres un array qui contient les paramètres (GET/POST) de la requête
     * @param string      $methode    GET par défaut
     * @param array       $headers    un array avec les headers de la requête
     * @param string|null $basicAuth  // couple 'login:mdp' (string) pour les endpoints configuré avec une basic-auth
     *                                (Si précisé et non-null, cette valeur vient écraser la valeur si déjà définie dans $headers)
     * @param string|null $token      // Bearer token pour les endpoints qui nécessitent une authentification
     *                                (Si précisé et non-null, cette valeur vient écraser la valeur si déjà définie dans $headers)
     * @param bool        $debug
     * @param bool        $contenuUniquement
     *
     * @return ApiReponse|null Retournes un objet ResponseInterface contenant les éléments de la réponse si l'appel est bien effectué.
     * null si une Exception est rencontrée.
     */
    public function call(
        string $route,
        array $parametres = [],
        string $methode = Request::METHOD_GET,
        array $headers = [],
        string $basicAuth = null,
        string $token = null,
        bool $debug = false,
        bool $contenuUniquement = false
    ): ?ApiReponse {
        $options = [];

        if (
            isset($headers[HttpHeaders::CONTENT_TYPE->value])
            && $headers[HttpHeaders::CONTENT_TYPE->value] === HttpHeaders::APPLICATION_JSON->value
            && !empty($parametres)
        ) {
            $options[HttpHeaders::JSON->value] = $parametres;
        } elseif (!empty($parametres)) {
            $options[$methode == Request::METHOD_GET ? HttpHeaders::QUERY->value : HttpHeaders::BODY->value] = $parametres;
        }

        if (!empty($basicAuth)) {
            $headers[HttpHeaders::AUTHORIZATION->value] = HttpHeaders::BASIC->value . base64_encode($basicAuth);
        } elseif (!empty($token)) {
            $headers[HttpHeaders::AUTHORIZATION->value] = HttpHeaders::BEARER->value . $token;
        }

        $headers = $this->setEntetesPourTrace($headers);

        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        $url = $this->baseUrl . $route;

        if ($debug) {
            dump([
                'methode' => $methode,
                'url' => $url,
                'options' => $options
            ]);
        }

        try {
            $traceApi = (bool)($_ENV['APP_TRACE_API'] ?? 0);
            $this->logger->info('Appel API : ' . $url, $traceApi ? [json_encode($parametres)] : []);
            $reponse = $this->httpClient->request($methode, $url, $options);
            $data = $contenuUniquement ? [] : $reponse->toArray();
            if ($debug) {
                dump([
                    'reponseComplete' => $reponse,
                    'statusCode' => $reponse->getStatusCode(),
                    'headers' => $reponse->getHeaders(),
                    'content' => $reponse->getContent(),
                    'data' => $data,
                ]);
            }
            return new ApiReponse(
                $reponse->getStatusCode(),
                $reponse->getHeaders(),
                $data,
                $reponse->getContent()
            );
        } catch (ClientExceptionInterface|DecodingExceptionInterface
        |RedirectionExceptionInterface|ServerExceptionInterface|TransportExceptionInterface $e) {
            // En cas d'exception :
            $this->logger->critical('Code : [' . $e->getCode() . '] ; Message http-client : ' . $e->getMessage());
            if ($debug) {
                dump($e);
            }
            return null;
        } catch (Exception $e) {
            // Gestion d'exceptions non typées
            $this->logger->critical('Code : [' . $e->getCode() . '] ; Message : ' . $e->getMessage());
            if ($debug) {
                dump($e);
            }
            return null;
        }
    }

    /**
     * Code retour d'un objet ApiReponse compris entre 200 et 400 exclus
     *
     * @param ApiReponse|null $reponse
     *
     * @return bool
     */
    public function estRetourOk(ApiReponse|null $reponse): bool
    {
        return !empty($reponse)
            && Response::HTTP_OK <= $reponse->getHttpCode()
            && $reponse->getHttpCode() < Response::HTTP_BAD_REQUEST;
    }

    /**
     * Permet de garantir que la réponse retournée par la méthode call() est valide et de type ApiReponse non null
     *
     * @param ApiReponse|null $reponse
     *
     * @return ApiReponse
     */
    public function validerReponse(ApiReponse|null $reponse): ApiReponse
    {
        if (empty($reponse)) {
            throw new ExceptionReponseApiNull();
        }

        if (!$this->estRetourOk($reponse)) {
            throw new ExceptionReponseApiKo();
        }

        return $reponse;
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    private function setEntetesPourTrace(array $headers): array
    {
        $headers['X-App'] = $_ENV['CURRENT_APP'];

        return $headers;
    }
}
