<?php declare(strict_types=1);

namespace Acme;


final class Log
{
    protected static $logfile;


    public static function setLogFile(string $logfile)
    {
        self::$logfile = $logfile;
        file_put_contents(self::$logfile, 'new' . time() . PHP_EOL);
    }

    public static function log(string $text)
    {
        file_put_contents(self::$logfile, $text . PHP_EOL, FILE_APPEND);
    }
    
}
