<?php
$db_cfg_path = AlefCore::get_db_cfg_php_path();
if (!empty($db_cfg_path)) {
    require_once $db_cfg_path; // реквизиты базы данных – генерируется автоматически во время установки с помощью diesel
}
require_once __DIR__ . "/../constants.php"; // системные константы

require_once __DIR__ . "/../../src/const/strings.php"; // строковые константы – пользовательские сообщения, на каждом проекте заполняются программистом
require_once __DIR__ . "/../../src/const/config.php"; // любые другие константы проекта, на каждом проекте заполняются программистом
require_once __DIR__ . "/../../src/const/errors.php"; // коды ошибок проекта, на каждом проекте заполняются программистом

require_once __DIR__ . "/../classes/AlefException.php";
require_once __DIR__ . "/../classes/AlefLog.php";
require_once __DIR__ . "/../classes/AlefLocalizer.php";
require_once __DIR__ . "/../classes/AlefSQL.php";
require_once __DIR__ . "/../classes/AlefRequest.php";
require_once __DIR__ . "/../classes/AlefInAppParser.php";
require_once __DIR__ . "/../classes/AlefPushManager.php";

require_once __DIR__ . "/../shortcuts.php";
require_once __DIR__ . "/../../src/Common.php";

class AlefCore
{
    private static $isAuthorizedHandlers = [];
    private static $requestId;

    const REQUESTS_REQUIRING_AUTH = ["getMoscowTemperature"];
    
    /*
     * Это корневой метод для запуска любого реквеста, он содержит в себе все необходимые обработки эксепшнов
     * и превращение их в валидный структурированный ответ.
     * Если последний параметр params не будет передан – реквест автоматически соберет необходимые параметры из http запроса
     * с помощью метода parseHttpParameterValues в AlefRequest.
     * Если его передать, то параметры http запроса будут проигнорированы и вместо них будут использованны переданные.
     * Это нужно для того, чтобы можно было сделать вызов функции из PHP версии API, а также для использования в автотестах
    */
    public static function executeRequest($alefAction, $language, $params = null)
    {
        $result = [];

        //уникальный идентификатор пришедшего запроса, используемый для трасировки записей в логах
        self::$requestId = md5(uniqid(rand(), true));
        $startTime = round(microtime(true) * 1000);
        try {
            if (AlefCore::isVerboseMode()) {
                $info = "-----------------------------------REQUEST----------------------------------->\n";
                $info .= "Request: \n" . print_r($_REQUEST ?? [], true) . "\n";
                $info .= "Files: \n" . print_r($_FILES ?? [], true) . "\n";
                $info .= "Cookies: \n" . print_r($_COOKIE ?? [], true). "\n";
                $info .= "params: \n" . print_r($params ?? [], true) . "\n";
                AlefLog::verbose($info);
            }
            Common::didReceiveRequest();
            self::checkAndCreateDefaultFolders();

            if (!empty($language)) {
                AlefLocalizer::initWithLanguage($language);
            }
            if (in_array($alefAction, self::REQUESTS_REQUIRING_AUTH) && !self::isAuthorized()) {
                throw new AlefException(ERR_AUTH_REQUIRED);
            } else {
                //вырезаем из строки все кроме букв латинской раскладки, цифр и знаков минус и подчеркивание
                $alefAction = preg_replace("/[^a-zA-Z0-9\-_]/", "", $alefAction);
                $className = "Request" . ucfirst($alefAction);

                $classPath = __DIR__ . "/../../src/" . $className . ".php";
                if (!file_exists($classPath)) {
                    throw new AlefException(ERR_UNKNOWN_ACTION);
                }

                require_once($classPath);

                $requestClass = new ReflectionClass($className);
                $requestInstance = $requestClass->newInstance();
                $result = $requestInstance->execute($params);
            }
        } catch (AlefException $exception) {
            $result = self::getAlefExceptionDetails($exception);
        } catch (PDOException $exception) {
            $alefException = new AlefException(ERR_DB_EXCEPTION, $exception->getMessage());
            $result = self::getAlefExceptionDetails($alefException);
        } catch (Throwable $exception) {
            $alefException = new AlefException(ERR_UNKNOWN, $exception->getMessage());
            $result = self::getAlefExceptionDetails($alefException);
        } finally {
            $endTime = round(microtime(true) * 1000);
            if (AlefCore::isVerboseMode()) {
                $info = "<-----------------------------------RESPONSE-----------------------------------\n";
                $info .= json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
                $info .= "Execution time: ".($endTime-$startTime)." msec.\n";
                $info .= "-- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- -- --\n";
                AlefLog::verbose($info);
            }
            self::$requestId = null;
            return $result;
        }
    }

    /*
     * Метод на основе кода ошибки эксепшна добавляет к нему текстовую информацию, информацию о реквесте и
     * локализует сообщение на язык установленный в рамках текущего запроса. В случае любых ошибок во время работы этого метода
     * предусмотрен fallback на стандартное сообщение об ошибке
     * */
    public static function getAlefExceptionDetails(AlefException $exception)
    {
        $response = [];
        $response[KEY_STATUS] = $exception->getCode();

        try {
            if (!empty($exception->getMessage()) && self::isDevServer()) {
                $response[KEY_DEVELOPER_MESSAGE] = $exception->getMessage() . "\n" . $exception->getTraceAsString();
            }

            if (!empty($_REQUEST)) {
                $response[KEY_REQUEST] = self::clearResponseSensitiveData($_REQUEST);
            }

            if (defined("ERRORS") && array_key_exists($exception->getCode(), ERRORS)) {
                $user_message = AlefLocalizer::_localize(ERRORS[$exception->getCode()][KEY_USER_MESSAGE]);
                $is_recoverable = ERRORS[$exception->getCode()][KEY_IS_RECOVERABLE] ?? null;
            }
            
            //Если было задано принудительно кастомное сообщение для пользователя, сообщение из справочника будет проигнорировано
            if (!empty($exception->getCustomUserMessage())) {
                $user_message = $exception->getCustomUserMessage();
            }

            if (empty($user_message)) {
                if (defined("ERRORS")) {
                    $user_message = AlefLocalizer::_localize(ERRORS[ERR_UNKNOWN][KEY_USER_MESSAGE]);
                }
            }

            if (empty($user_message)) {
                if (defined("STR_ERR_UNKNOWN")) {
                    $user_message = STR_ERR_UNKNOWN;
                } else {
                    // Если не удалось получить никакое текстовое сообщение для ошибки ставим общее сообщение
                    $user_message = "Unknown error";
                }
            }

            if (isset($is_recoverable)) {
                $response[KEY_IS_RECOVERABLE] = $is_recoverable;
            }

            $response[KEY_USER_MESSAGE] = $user_message;
        } catch (Throwable $e) {
            $response[KEY_USER_MESSAGE] = ERRORS[ERR_UNKNOWN][KEY_USER_MESSAGE];
            $response[KEY_DEVELOPER_MESSAGE] = $e->getMessage();
            // Если при попытке заполнения деталей эксепшна в респонсе произошла ошибка – игнорируем ee и устанавливаем сообщение о неизвестной ошибке
        } finally {
            if ($response[KEY_STATUS] !== 0) {
                self::logErrorDetails($response);
            }
            return $response;
        }
    }
    
    private static function logErrorDetails($response)
    {
        $message = "\n---------------------------------- \n";
        $message .= "Error while executing request: \n";
        $message .= print_r($_REQUEST ?? null, true) . "\n";
        $message .= "Session:\n";
        $message .= print_r($_SESSION ?? null, true) . "\n";
        $message .= "Response:\n";
        $message .= json_encode($response, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n";
        $message .= "---------------------------------- \n";
        AlefLog::error($message);
    }

    public static function onCheckAuthorization($isAuthorizedHandler)
    {
        self::$isAuthorizedHandlers[] = $isAuthorizedHandler;
    }

    public static function isDevServer()
    {
        return (defined("DIESEL_SERVER") && (DIESEL_SERVER == "dev" || DIESEL_SERVER == "monster"));
    }
    
    public static function isVerboseMode()
    {
        return (defined("CFG_VERBOSE_MODE") && CFG_VERBOSE_MODE == 1);
    }
    
    public static function isAuthorized()
    {
        if (isset($_REQUEST["token"])) {
            $session_id = $_REQUEST["token"];
        } else {
            if (isset($_COOKIE["token"])) {
                $session_id = $_COOKIE["token"];
            } else {
                return false;
            }
        }

        if (!isset($_SESSION) || session_status()!=PHP_SESSION_ACTIVE) {
            session_id($session_id);
            session_start();
        }
        if (isset($_SESSION[ALEF_SESSION_MARKER]) && $_SESSION[ALEF_SESSION_MARKER] == ALEF_SESSION_MARKER) {
            if (isset(self::$isAuthorizedHandlers)) {
                foreach (self::$isAuthorizedHandlers as $handler) {
                    $authorized = ($handler($_SESSION[USER_ID]));
                    if (!$authorized) {
                        return false;
                    }
                }
            }
            return true;
        }
        session_destroy();

        return false;
    }

    public static function clearResponseSensitiveData($request)
    {
        //при возврате реквеста в ответе об ошибке принудительно вычищать из него поля с паролями из черного списка на боевом сервере
        $black_list = ["pass", "password"];
        if (!self::isDevServer()) {
            foreach ($black_list as $word) {
                if (key_exists($word, $request)) {
                    unset($request[$word]);
                }
            }
        }
        return $request;
    }

    public static function get_db_cfg_php_path()
    {
        $search_path = [__DIR__ . "/../", __DIR__ . "/../../", __DIR__ . "/../../../", $_SERVER["DOCUMENT_ROOT"] . "/"];

        foreach ($search_path as $path) {
            $full_path = $path . "db.cfg.php";
            if (file_exists($full_path)) {
                return $full_path;
            }
        }
        return null;
    }

    public static function getRequestId()
    {
        return self::$requestId;
    }
    
    public static function getCurrentUserId()
    {
        /* Проверяем если уже есть активная сессия, возвращаем user_id из нее сразу. Если сесси активной нет, пытаемся ее восстановить
        * с помощью общего механизма isAuthorized.
        */
        if (isset($_SESSION[USER_ID])) {
            return $_SESSION[USER_ID];
        } elseif (AlefCore::isAuthorized()) {
            return $_SESSION[USER_ID] ?? null;
        }

        return null;
    }

    // Функция проверяет наличие и создает при отсуствии папки, необходимые для работы: uploads, logs
    public static function checkAndCreateDefaultFolders()
    {
        $foldersToCheck = [CFG_LOGS_FOLDER, UPLOADS_FOLDER, TAMPER_RESPONSES_FOLDER];

        foreach ($foldersToCheck as $folder) {
            if (!file_exists($folder)) {
                if (mkdir($folder) === false) {
                    throw new AlefException(ERR_CREATE_FOLDER);
                }

                if (!file_exists($folder . "/index.html")) {
                    touch($folder . "/index.html"); //создаем пустой index.html как дополнительную меру защиты от просмотра содержимого папки на случай, если web-сервер не был корректно сконфигурирован
                }
            }
        }
    }

    public static function getTamperFileName($alefAction, $aqTamperId)
    {
        $alefAction = preg_replace("/[^a-zA-Z0-9\-_]/", "", $alefAction);
        $res = TAMPER_RESPONSES_FOLDER."/" . md5($aqTamperId) . "-" . $alefAction . ".json";
        return $res;
    }

    public static function getTamperResponse($alefAction, $aqTamperId)
    {
        $aqTamperFilename = empty($aqTamperId)?null:self::getTamperFileName($alefAction, $aqTamperId);
        if (!empty($aqTamperFilename) && !file_exists($aqTamperFilename)) {
            return null;
        } else {
            $result = json_decode(file_get_contents($aqTamperFilename), true);
            $result["tamper_warning"] = "You are using hardcoded response based on aq_tamper_id parameter";
            return $result;
        }
    }
}
