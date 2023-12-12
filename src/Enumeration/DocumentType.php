<?php

namespace App\Enumeration;

enum DocumentType: string
{
    case CV = 'Curicculum Vitae';
    case PP = 'Passport';
    CASE DL = 'Driver Licence';

    /**
     * @return string
     */
    public static function getExistingDocument(): string
    {
        $retour = '';
        foreach (DocumentType::cases() as $documentType) {
            $retour .= $documentType->name . ' pour ' . $documentType->value;
        }

        return $retour;
    }
}