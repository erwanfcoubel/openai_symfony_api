<?php

namespace App\Exception\Api;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\HttpFoundation\Response;

/**
 * Exception renvoyée lors d'une validation de réponse suite à un appel d'un service client HTTP.
 * Si la réponse retournée par la méthode call() est null (c.a.d une exception a été rencontrée lors de l'appel)
 */
class ExceptionReponseApiNull extends Exception
{
    const MESSAGE_PAR_DEFAUT = "Une erreur est survenue lors de la communication entre serveurs. La réponse est vide.";

    /**
     * @param string $message
     * @param int $code
     */
    public function __construct(
        string $message = self::MESSAGE_PAR_DEFAUT,
        int    $code = Response::HTTP_INTERNAL_SERVER_ERROR
    )
    {
        parent::__construct($message, $code);
    }
}
