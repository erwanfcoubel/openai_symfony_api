<?php

namespace App\Service\Api;

use App\Enumeration\HttpHeaders;
use Symfony\Component\HttpFoundation\Request;

class AuthentificationService extends AbstractApiService
{

    /**
     * @var string $defaultUrl
     */
    protected string $defaultUrl;

    /**
     * @var string
     */
    protected string $user;

    /**
     * @var string $password
     */
    protected string $password;

    /**
     * @var string $token
     */
    protected string $token;


    /**
     * @param $defaultUrl
     * @param $user
     * @param $password
     *
     * @return mixed|string
     */
    public function getToken($defaultUrl, $user, $password): mixed
    {
        $this->defaultUrl = $defaultUrl;
        $this->user = $user;
        $this->password = $password;

        if (empty($this->token)) {
            $this->token = $this->authentification();
        }

        return $this->token;
    }

    /**
     * @return mixed|string
     */
    public function authentification(): mixed
    {
        $headers[HttpHeaders::CONTENT_TYPE->value] = HttpHeaders::APPLICATION_JSON->value;
        $reponse = $this->call(
            route: '/login_check',
            parameters: [
                'username' => $this->user,
                'password' => $this->password,
            ],
            method: Request::METHOD_POST,
            headers: array_merge($this->defaultHeaders, $headers),
        );

        $this->token = $this->responseValidate($reponse)->get('token');
        return $this->token;
    }
}
