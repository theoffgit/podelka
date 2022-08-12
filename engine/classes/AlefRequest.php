<?php

class AlefRequest
{

    /*
     * Функция вызывается после того, как логин и пароль пользователя проверены и мы хотим его авторизовать
     *
     * */
    public function grantAccess($user_id=null)
    {
        if (AlefCore::isAuthorized()) {
            // уже залогинен - разлогиниваем
            $this->revokeAccess();
        }
        
        if (session_status()!=PHP_SESSION_ACTIVE || !isset($_SESSION)) {
            session_start();
        }

        $_SESSION[ALEF_SESSION_MARKER] = ALEF_SESSION_MARKER;
        $_SESSION[USER_ID] = $user_id;
        setcookie("token", session_id(), 10 * 365 * 24 * 3600 + time(), "/");
    }

    public function revokeAccess() // надо вызывать, когда пользователь разлогинился
    {
        if (session_status()!=PHP_SESSION_ACTIVE || !isset($_SESSION)) {
            session_start();
        }

        $_SESSION[ALEF_SESSION_MARKER] = "";
        $_SESSION[USER_ID] = 0;
        $_SESSION = [];
        unset($_SESSION);

        if(session_status()==PHP_SESSION_ACTIVE) {
            session_destroy();
        }
        unset($_COOKIE["token"]);
        setcookie("token", "", 0, "/");
        session_commit();
    }

    public function getCurrentUserId()
    {
        return AlefCore::getCurrentUserId();
    }

    public function parseHttpParameterValues($reflection_params)
    {
        // Подпись запроса проверяем только в случае, если обрабатывается http реквест, если параметры прислали принудительно, в этот метод не попадем
        if (!$this->hasValidSecuritySignature($_REQUEST[KEY_TIMESTAMP] ?? null, $_REQUEST[KEY_SECURITY_HASH] ?? null)) {
            throw new AlefException(ERR_SECURITY_FAILED);
        }
        $params = [];
        foreach ($reflection_params as $param) {
            $params[] = isset($_FILES[$param->name]) ? $_FILES[$param->name] : ($_REQUEST[$param->name] ?? null);
        }

        return $params;
    }

    final public function execute($params=null)
    {
        if (method_exists($this, "executeRequest")) {
            $reflectionMethod = new ReflectionMethod(get_class($this), "executeRequest");
            $reflectionMethod->setAccessible(true);
            $params = ($params==null?$this->parseHttpParameterValues($reflectionMethod->getParameters()):$params);
            $result = $reflectionMethod->invokeArgs($this, $params);
            return $result;
        } else {
            throw new AlefException(ERR_BROKEN_REQUEST_CLASS, "Method executeRequest is missing in ".get_class($this)." or request file is corrupted");
        }
    }

    private function hasValidSecuritySignature($req_timestamp, $req_hash)
    {
        if (!defined("CFG_ENABLE_REQUEST_SECURITY_SIGNATURE") || CFG_ENABLE_REQUEST_SECURITY_SIGNATURE==0) {
            return true;
        }
        if (!empty($req_timestamp) && !empty($req_hash)) {
            if (abs($req_timestamp - time()) <= CFG_BASE_SECURITY_TIME_FRAME) {
                $sec_hash_md5 = md5(BASE_SECURITY_SALT . $req_timestamp);
                $sec_hash_sha256 = hash("sha256", BASE_SECURITY_SALT . $req_timestamp);

                if ($req_hash == $sec_hash_md5 || $req_hash == $sec_hash_sha256) {
                    return true;
                } else {
                    return false;
                }
            } else {
                return false;
            }
        }
        return false;
    }
}
