<?php
require_once __DIR__ . "/../../src/const/config.php";

class AlefLog
{
    const LEVEL_INFO = 0;
    const LEVEL_WARNING = 1;
    const LEVEL_DEBUG = 2;
    const LEVEL_ERROR = 3;
    const LEVEL_VERBOSE = 4;

    const MAX_LOG_SIZE = 10; //MB
    const LOG_ROTATION_MAX_FILES = 10;

    const LEVEL_TITLES = [
        self::LEVEL_INFO    => "info",
        self::LEVEL_WARNING => "warning",
        self::LEVEL_DEBUG   => "debug",
        self::LEVEL_ERROR   => "error",
        self::LEVEL_VERBOSE   => "verbose"
    ];

    private static function get_calling_class()
    {
        $trace = debug_backtrace();

        $currentClass = $trace[1]["class"];

        for ($i = 1; $i < count($trace); $i++) {
            if (isset($trace[$i])) {
                $stepClass = $trace[$i]["class"] ?? null;
                if ($currentClass != $stepClass) {
                    return $stepClass;
                }
            }
        }
    }

    public static function debug($str)
    {
        self::log($str, self::LEVEL_DEBUG);
    }

    public static function info($str)
    {
        self::log($str, self::LEVEL_INFO);
    }

    public static function warning($str)
    {
        self::log($str, self::LEVEL_WARNING);
    }

    public static function error($str)
    {
        self::log($str, self::LEVEL_ERROR);
    }

    public static function verbose($str)
    {
        self::log($str, self::LEVEL_VERBOSE);
    }

    private static function log($str, $level = self::LEVEL_INFO)
    {
        try {
            $file_name = CFG_GENERAL_LOG . "-" . self::LEVEL_TITLES[$level] . ".log";
            $full_name = CFG_LOGS_FOLDER . $file_name;

            /* не оптимальный механизм ротации логов, будет заменен в ближайших версиях */
            clearstatcache();
            if (file_exists($full_name) && (filesize($full_name) / 1024 / 1024) > self::MAX_LOG_SIZE) {
                self::shiftLogFiles($full_name);
            }
            $data = date("Y-m-d H:i:s") .  " [user_id:" . AlefCore::getCurrentUserId() . "]" . " [req_id:" . AlefCore::getRequestId() . "]". " [" . self::get_calling_class() . "] " . str_replace("\n",'\n',$str) . "\n";

            @file_put_contents($full_name, $data, FILE_APPEND | LOCK_EX);

        } catch (Throwable $e) {
            // Если логирование падает сошибкой это не должно никак повлиять на работу приложения
        }
    }

    private static function shiftLogFiles($fileName)
    {
        $nextFileName = self::nextFileName($fileName);
        if (is_null($nextFileName)) {
            unlink($fileName);
        } else {
            if (file_exists($nextFileName)) {
                self::shiftLogFiles($nextFileName);
            }
            rename($fileName, $nextFileName);
        }
    }

    private static function nextFileName($fileName)
    {
        preg_match_all('/[\.]?(\d{0,})\.log$/', $fileName, $matches);
        $fileNumber = $matches[1][0];
        if (empty($fileNumber)) {
            $fileNumber = 0;
        }
        $fileNumber++;
        if ($fileNumber > self::LOG_ROTATION_MAX_FILES) {
            $nextFileName = null;
        } else {
            $nextFileName = preg_replace('/[\.]?\d{0,}\.log$/', ".{$fileNumber}.log", $fileName);
        }
        return $nextFileName;
    }
}
