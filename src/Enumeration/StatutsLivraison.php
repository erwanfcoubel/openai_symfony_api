<?php

namespace App\Enumeration;

enum StatutsLivraison: string
{
    case ACTIF = 'actif';
    case SUSPENDU = 'suspendu';
    case ANNULE = 'annule';
    case CLOTURE = 'cloture';
}