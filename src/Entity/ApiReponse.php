<?php

namespace App\Entity;

use Symfony\Component\HttpFoundation\Response;

/**
 * Cet objet est créé lors d'un appel API avec un service issu de la classe Abstraite définie dans AbstractApiService.php
 * L'objet généré est ainsi manipulable est exempt d'exceptions car déjà gérées (logs)
 */
final class ApiReponse
{
    /**
     * @var int|null
     */
    protected ?int $httpCode = null;

    /**
     * @var array
     */
    protected array $headers = [];

    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @var string|null
     */
    protected ?string $contenu = null;

    /**
     * @param int|null $httpCode
     * @param array|null $headers
     * @param array|null $data
     * @param string|null $contenu
     */
    public function __construct(
        ?int    $httpCode = Response::HTTP_OK,
        ?array  $headers = [],
        ?array  $data = [],
        ?string $contenu = ''
    )
    {
        $this->httpCode = $httpCode;
        $this->headers = $headers;
        $this->data = $data;
        $this->contenu = $contenu;
    }

    /**
     * Le code HTTP de la réponse retournée lors de l'appel avec HttpClient
     * @return int|null
     */
    public function getHttpCode(): ?int
    {
        return $this->httpCode;
    }

    /**
     * @param int|null $httpCode
     * @return ApiReponse
     */
    public function setHttpCode(?int $httpCode): ApiReponse
    {
        $this->httpCode = $httpCode;
        return $this;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        return $this->headers;
    }

    /**
     * @param array|null $headers
     * @return ApiReponse
     */
    public function setHeaders(?array $headers): ApiReponse
    {
        $this->headers = $headers ?? [];
        return $this;
    }

    /**
     * Le contenu de la réponse retournée lors de l'appel avec HttpClient sous forme d'un array
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array|null $data
     * @return ApiReponse
     */
    public function setData(?array $data): ApiReponse
    {
        $this->data = $data ?? [];
        return $this;
    }

    /**
     * Le contenu de la réponse retournée lors de l'appel avec HttpClient
     * @return string|null
     */
    public function getContenu(): ?string
    {
        return $this->contenu;
    }

    /**
     * @param string|null $contenu
     * @return ApiReponse
     */
    public function setContenu(?string $contenu): ApiReponse
    {
        $this->contenu = $contenu;
        return $this;
    }

    /**
     * Permet de récupérer une valeur depuis le tableau contenant la réponse de l'API
     * @param string $cle
     * @param mixed $valeurParDefaut
     * @return mixed
     */
    public function get(string $cle, mixed $valeurParDefaut = null): mixed
    {
        return $this->data[$cle] ?? $valeurParDefaut;
    }

    /**
     * Permet de tester l'existence d'une clé dans le tableau de données de la réponse
     * @param string $cle
     * @return bool
     */
    public function isset(string $cle): bool
    {
        return isset($this->data[$cle]);
    }
}
