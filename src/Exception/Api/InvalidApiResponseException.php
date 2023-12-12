<?php

namespace App\Exception\Api;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception pouvant être renvoyée lors d'un appel à une méthode d'un service client HTTP
 * si la réponse ne contient pas un élément nécessaire
 */
class InvalidApiResponseException extends Exception
{
    const DEFAULT_MESSAGE = "Une erreur est survenue lors de la communication entre serveurs. La réponse ne contient pas le ou les éléments attendus.";

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