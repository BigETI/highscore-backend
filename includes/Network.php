<?php

/**
 * Network class
 * @author Ethem Kurt
 *
 */
class Network
{

    /**
     * Get client IP address
     *
     * @return string IP address
     */
    public static function GetClientIPAddress()
    {
        $ret = '';
        if (! (empty($_SERVER['HTTP_CLIENT_IP'])))
        {
            $ret = $_SERVER['HTTP_CLIENT_IP'];
        }
        elseif (! (empty($_SERVER['HTTP_X_FORWARDED_FOR'])))
        {
            $ret = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        else
        {
            $ret = $_SERVER['REMOTE_ADDR'];
        }
        return $ret;
    }

    /**
     * Get my IP address
     *
     * @return string My IP address
     */
    public static function GetMyIPAddress()
    {
        $ret = '';
        if (! empty($_SERVER['SERVER_ADDR']))
        {
            $ret = $_SERVER['SERVER_ADDR'];
        }
        else
        {
            $ret = $_SERVER['LOCAL_ADDR'];
        }
        return $ret;
    }
}
?>