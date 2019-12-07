<?php
/**
 * (file name)
 *
 * @package App\Handlers
 * @copyright (c) 2019, Fairbanks Publishing
 * @license Proprietary
 */

namespace App\Handlers;

/**
 * Class Handler
 *
 * @author David Fairbanks <david@makerdave.com>
 * @package App\Handlers
 * @version 1.0
 */
class Handler
{
    protected static $messages = [];

    /**
     * Get the last message
     *
     * @param string $default
     * @return string
     */
    public static function getMessage($default='')
    {
        if(empty(self::$messages))
            return $default;

        return array_pop(self::$messages);
    }

    /**
     * Get all messages
     *
     * @return array
     */
    public static function getMessages()
    {
        return self::$messages;
    }

    /**
     * Clear all messages
     */
    public static function resetMessages()
    {
        self::$messages = [];
    }
}
