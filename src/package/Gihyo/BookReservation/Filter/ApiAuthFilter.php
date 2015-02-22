<?php
namespace Gihyo\BookReservation\Filter;

use Gihyo\BookReservation\Model\User;
use Request;
use Response;

/**
 * Class ApiAuthFilter
 * @package Gihyo\BookReservaion\Filter
 */
class ApiAuthFilter
{
    /**
     * @var string
     */
    const APPLICATION_TOKEN = 'x-application-token';
    /**
     * @var string
     */
    const AUTHORIZED_USER = 'authorized_user';

    /**
     * filter
     */
    public function filter()
    {
        $user = User::where('api_token', Request::header(static::APPLICATION_TOKEN))->first();
        if (is_null($user)) {
            return Response::json(['message' => '401 Unauthorized'], 401);
        }

        app()[static::AUTHORIZED_USER] = $user;

        return null;
    }
}