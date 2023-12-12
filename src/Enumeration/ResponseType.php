<?php

namespace App\Enumeration;

enum ResponseType: string
{
    case ACTION = 'action';
    case TEXT = 'text';
    case ERREUR = 'erreur';
}