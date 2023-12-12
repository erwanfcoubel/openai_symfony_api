<?php

namespace App\Service\Api;

use App\Entity\ApiReponse;
use App\Enumeration\HttpHeaders;
use App\Exception\Api\ErrorApiResponseException;
use App\Exception\Api\NullApiResponseException;
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

abstract class AbstractApiService
{
    /**
     *
     * @var string
     */
    protected string $defaultUrl = '';

    /**
     * @var array $defaultHeaders
     */
    protected array $defaultHeaders = [];

    /**
     * @var HttpClientInterface
     */
    protected HttpClientInterface $httpClient;

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

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
     * @param string      $route
     * @param array       $parameters
     * @param string      $method
     * @param array       $headers
     * @param string|null $basicAuth
     * @param string|null $token
     * @param bool        $debug
     * @param bool        $contentOnly
     *
     * @return ApiReponse|null
     */
    public function call(
        string $route,
        array $parameters = [],
        string $method = Request::METHOD_GET,
        array $headers = [],
        string $basicAuth = null,
        string $token = null,
        bool $debug = false,
        bool $contentOnly = false
    ): ?ApiReponse {
        $options = [];

        if (
            isset($headers[HttpHeaders::CONTENT_TYPE->value])
            && $headers[HttpHeaders::CONTENT_TYPE->value] === HttpHeaders::APPLICATION_JSON->value
            && !empty($parameters)
        ) {
            $options[HttpHeaders::JSON->value] = $parameters;
        } elseif (!empty($parameters)) {
            $options[$method == Request::METHOD_GET ? HttpHeaders::QUERY->value : HttpHeaders::BODY->value] = $parameters;
        }

        if (!empty($basicAuth)) {
            $headers[HttpHeaders::AUTHORIZATION->value] = HttpHeaders::BASIC->value . base64_encode($basicAuth);
        } elseif (!empty($token)) {
            $headers[HttpHeaders::AUTHORIZATION->value] = HttpHeaders::BEARER->value . $token;
        }

        $headers = $this->setHeadersForTrace($headers);

        if (!empty($headers)) {
            $options['headers'] = $headers;
        }

        $url = $this->defaultUrl . $route;

        if ($debug) {
            dump([
                'methode' => $method,
                'url' => $url,
                'options' => $options
            ]);
        }

        try {
            $traceApi = (bool)($_ENV['APP_TRACE_API'] ?? 0);
            $this->logger->info('Appel API : ' . $url, $traceApi ? [json_encode($parameters)] : []);
            $reponse = $this->httpClient->request($method, $url, $options);
            $data = $contentOnly ? [] : $reponse->toArray();
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
     * @param ApiReponse|null $reponse
     *
     * @return bool
     */
    public function isKoResult(ApiReponse|null $reponse): bool
    {
        return !empty($reponse)
            && Response::HTTP_OK <= $reponse->getHttpCode()
            && $reponse->getHttpCode() < Response::HTTP_BAD_REQUEST;
    }

    /**
     * @param ApiReponse|null $reponse
     *
     * @return ApiReponse
     */
    public function responseValidate(ApiReponse|null $reponse): ApiReponse
    {
        if (empty($reponse)) {
            throw new NullApiResponseException();
        }

        if (!$this->isKoResult($reponse)) {
            throw new ErrorApiResponseException();
        }

        return $reponse;
    }

    /**
     * @param array $headers
     *
     * @return array
     */
    private function setHeadersForTrace(array $headers): array
    {
        $headers['X-App'] = $_ENV['CURRENT_APP'];

        return $headers;
    }
}
