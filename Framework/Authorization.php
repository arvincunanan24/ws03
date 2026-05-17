<?php


namespace Framework;

use Framework\Session;

class Authorization
{
    /**
     * Check if logged in user owns a listing
     * 
     * @params int $resourceId
     * @return bool
     */

    public static function isOwner($resourceId)
    {
        $sessionUser = Session::get('user');

        if ($sessionUser !== null && isset($sessionUser['id'])) {
            $sessionUserId = (int) $sessionUser['id'];
            return $sessionUserId === $resourceId;
        }

        return false;
    }
}
