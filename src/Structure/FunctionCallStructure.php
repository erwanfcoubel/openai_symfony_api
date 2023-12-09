<?php

// Structure permettant d'avoir les differents type de reponse de function call

namespace App\Structure;

class FunctionCallStructure
{

    // Liste des fonctions disponibles pour OpenAI (Possiblement a refacto ou a récupérer dynamiquement)
    const FONCTION_LISTES = [
        [
            'name' => 'avancerProchaineLivraisonPhysique',
            'description' => 'Permet d\'avancer la livraison du prochains colis de devoir',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'idCommande' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ],
        [
            'name' => 'infoProchaineLivraison',
            'description' => 'Permet d\'avoir diverses informations concernant sa prochaine livraison. La date d\'envoi, le contenu etc..',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'idCommande' => [
                        'type' => 'integer'
                    ]
                ]
            ]
        ],
        [
            'name' => 'envoiDocument',
            'description' => 'Permet d\'envoyer un document sous la demande d\'un eleve.',
            'parameters' => [
                'type' => 'object',
                'properties' => [
                    'idCommande' => [
                        'type' => 'integer',
                        'description' => 'Id commande fourni par le systeme'
                    ],
                    'nomDocument' => [
                        'type' => 'string',
                        'description' => 'Nom du document. Document possible: LR pour Lettre de recommandatation, CP: Certficat Professionnel, CA: Certficat d\'assiduite'
                    ]
                ]
            ]
        ],
    ];


    // Dans le cas ou la question demande une action ex: Avancer une livraison, Supprimer son compte etc..
    const TYPE_ACTION = 'action';

    // Dans le cas ou la question demande une simple information ex: Quel est mon numéro de colis
    const TYPE_TEXT = 'text';

    // Dans le cas ou il y aurait une erreur
    const TYPE_ERREUR = 'erreur';
}