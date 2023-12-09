<?php

namespace App\Enumeration;

enum HttpHeaders: string
{
    case BASIC = 'Basic ';
    case BEARER = 'Bearer ';
    case AUTHORIZATION = 'Authorization';
    case CONTENT_TYPE = 'Content-Type';
    case API_KEY = 'Api-Key';
    case X_TOKEN = 'x-token';
    case ACCEPT = 'Accept';
    case BODY = 'body';
    case JSON = 'json';
    case QUERY = 'query';
    case TEXT_PLAIN = 'text/plain';
    case APPLICATION_JSON = 'application/json';
}