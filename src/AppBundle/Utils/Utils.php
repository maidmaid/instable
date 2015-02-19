<?php

namespace AppBundle\Utils;

use GuzzleHttp\Client;
use Instaphp\Instagram\Response;

class Utils
{
    /**
     * @param Response $response
     *
     * @return bool|Response
     */
    public static function nextUrl(Response $response)
    {
        if (array_key_exists('next_url', $response->pagination)) {
            $client = new Client();
            $nextUrl = $response->pagination['next_url'];
            $response = $client->get($nextUrl);
            $response = new Response($response);

            return $response;
        }

        return false;
    }
}
