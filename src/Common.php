<?php
class Common
{
    public static function didReceiveRequest()
    {
        AlefCore::onCheckAuthorization(function ($user_id) {
            //$user = q1("select * from users where id=:user_id and is_active=1", ["user_id"=>$user_id]);
            //return !empty($user);
            return true;
        });
    }

    /**
     * Проверка наличия данных в полученом поле и возвращение ошибки с указанным кодом, если данных нет
     *
     * @param string $data Переменная указанного поля
     * @param integer $error_code Код ошибки
     * @return void
     */
    public static function isEmpty(string $data, int $error_code)
    {
        if (empty($data)) {
            throw new AlefException($error_code);
        }
    }

    /**
     * Проверяет валидность введённого E-mail
     *
     * @param string $email E-mail
     * @return void
     */
    public static function isEmailValid(string $email)
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new AlefException(ERR_INVALID_EMAIL);
        }
    }

    /**
     * Получение температуры в Москве через API Яндекс
     *
     * @return integer|null Температура в градусах Цельсия
     */
    public static function getMoscowTemperature(): int|null
    {
        $url = "https://api.weather.yandex.ru/v2/informers/?lat=55.755819&lon=37.617644";

        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $headers = ["X-Yandex-API-Key: ".API_YANDEX_KEY];
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);

        $resp = curl_exec($curl);
        curl_close($curl);


        $resp_json = json_decode($resp, true);

        $temperature = $resp_json["fact"]["temp"] ?? null;
        return $temperature;
    }
}
