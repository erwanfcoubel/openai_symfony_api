<?php

namespace App\Exception\Api;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception renvoyée lors d'une validation de réponse suite à un appel d'un service client HTTP
 * si le code HTTP de la réponse n'est pas situé entre 200 inclus et 400 exclus
 */
class ErrorApiResponseException extends Exception
{
    const DEFAULT_MESSAGE = "Une erreur est survenue lors de la communication entre serveurs. La réponse est invalide.";

    /**
     * @param string $message
     * @param int    $code
     */
    public function __construct(
        string $message = self::DEFAULT_MESSAGE,
        int $code = Response::HTTP_INTERNAL_SERVER_ERROR
    ) {
        parent::__construct($message, $code);
    }
}