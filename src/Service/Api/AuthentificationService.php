<?php

// Classe permettant de récuperer les jetons JWT pour connexion sécuriser via un service d'API qui nous fournis simplement
// l'URI de base, l'utilisateur et mdp fourni dans security.yaml de l'API source

namespace App\Service\Api;

use App\Enumeration\HttpHeaders;
use Symfony\Component\HttpFoundation\Request;

class AuthentificationService extends AbstractApiService
{

    protected string $baseUrl;

    protected string $utilisateur;

    protected string $motDePasse;

    protected string $jeton;


    public function recupererJeton($baseUrl, $utilisateur, $motDePasse)
    {
        $this->baseUrl=  $baseUrl;
        $this->utilisateur = $utilisateur;
        $this->motDePasse = $motDePasse;

        if(empty($this->jeton)){
            $this->jeton = $this->authentification();
        }

        return $this->jeton;
    }

    public function authentification()
    {
        $headers[HttpHeaders::CONTENT_TYPE->value] = HttpHeaders::APPLICATION_JSON->value;
        $reponse = $this->call(
            route: '/login_check',
            parametres: [
                'username' => $this->utilisateur,
                'password' => $this->motDePasse,
            ],
            methode: Request::METHOD_POST,
            headers: array_merge($this->headersParDefaut, $headers),
            debug: $this->debug
        );

        $this->jeton = $this->validerReponse($reponse)->get('token');
        return $this->jeton;
    }
}
