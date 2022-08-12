<?php

require_once __DIR__ . "/AlefCore.php";

// Класс php-интерфейс, позволяющий обращаться к API. Например, может быть использован для подключения и использования на сайте, дублирующем функции приложения

class AlefQuery
{
    public static function requestGetMoscowTemperature($lang = null)
    {
        return AlefCore::executeRequest("getMoscowTemperature", $lang, []);
    }

    public static function requestSignOut($lang = null)
    {
        return AlefCore::executeRequest("signOut", $lang, []);
    }

    public static function requestSignIn($email, $password, $lang = null)
    {
        return AlefCore::executeRequest("signIn", $lang, ["email" => $email, "password" => $password]);
    }

    public static function requestSignUp($name, $email, $password, $lang = null)
    {
        return AlefCore::executeRequest("signUp", $lang, ["name" => $name, "email" => $email, "password" => $password]);
    }
}
