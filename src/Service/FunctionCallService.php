<?php

namespace App\Service;

use App\Enumeration\ResponseType;
use App\Enumeration\StatutsLivraison;
use App\Service\Api\ApiCefService;
use Exception;
use Psr\Log\LoggerInterface;

class FunctionCallService
{
    /**
     * @var ApiCefService $apiCefService
     */
    private ApiCefService $apiCefService;

    /**
     * @var LoggerInterface $logger
     */
    private LoggerInterface $logger;

    /**
     * @param ApiCefService   $apiCefService
     * @param LoggerInterface $logger
     */
    public function __construct(ApiCefService $apiCefService, LoggerInterface $logger)
    {
        $this->apiCefService = $apiCefService;
        $this->logger = $logger;
    }

    /**
     * @param $nomFonction
     * @param $arguments
     *
     * @return array
     */
    public function appelerFonction($nomFonction, $arguments): array
    {
        if (is_callable([$this, $nomFonction])) {
            return $this->$nomFonction(...$arguments);
        } else {
            $this->logger->error('Fonction inexistante: ' . $nomFonction);
            return [
                'type' => ResponseType::TEXT->value,
                'infos' => 'Pas de fonction correspondante trouvée.'
            ];
        }
    }

    /**
     * @param int $idCommande
     *
     * @return array
     */
    public function avancerProchaineLivraisonPhysique(int $idCommande): array
    {
        try {
            $statutsCommande = $this->apiCefService->recupererStatutsCommande($idCommande);
            $prochaineLivraisonPhysique = $this->apiCefService->recupererProchaineLivraisonPhysique($idCommande);

            if ($statutsCommande->statutLivraison != 'actif' || empty($prochaineLivraisonPhysique)) {
                return [
                    'type' => ResponseType::TEXT->value,
                    'infos' => $statutsCommande->statutLivraison != StatutsLivraison::ACTIF->value ? 'Les livraisons ne sont pas actives sur ce dossier' : 'Il n\'y a pas de prochaine livraison',
                ];
            }

            return [
                'type' => ResponseType::ACTION->value,
                'fonction' => 'avancerProchaineLivraisonPhysique',
                'btn' => 'avancer la prochaine livraison',
                'infos' => 'Avancement de la livraison OK'
            ];
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return [
                'type' => ResponseType::ERREUR->value,
                'infos' => $exception->getMessage()
            ];
        }
    }

    /**
     * @param int $idCommande
     *
     * @return array
     */
    public function infoProchaineLivraison(int $idCommande): array
    {
        try {
            $prochaineLivraison = $this->apiCefService->recupererProchaineLivraisonPhysique($idCommande);

            if (!empty($prochaineLivraison)) {
                return [
                    'type' => ResponseType::TEXT->value,
                    'infos' => 'date:' . $prochaineLivraison->dateExpe . ' expedition numéro: ' . $prochaineLivraison->numExpe
                ];
            }
            return ['type' => ResponseType::TEXT->value, 'infos' => 'Pas de future livraison.'];
        } catch (Exception $exception) {
            $this->logger->error($exception->getMessage());
            return [
                'type' => ResponseType::ERREUR->value,
                'infos' => $exception->getMessage()
            ];
        }
    }
}

