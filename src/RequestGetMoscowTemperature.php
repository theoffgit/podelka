<?php

    /*
        Получение температуры в Москве
        Возвращает текущую температуру в Москве в градусах Цельсия
        ?alef_action=getMoscowTemperature
    */

    class RequestGetMoscowTemperature extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_TEMPERATURE = "temperature";
        const KEY_USER_MESSAGE = "user_message";


        public function executeRequest()
        {

            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge
            
            /** Запрос к API сервиса погоды для получения температуры в Москве */
            $temperature = Common::getMoscowTemperature();
            
            /** Если при получении температуры произошла ошибка - возвращаем ошибку */
            if (empty($temperature)) {
                throw new AlefException(ERR_INVALID_YANDEX_RESPONSE);
            }

            $res[self::KEY_STATUS] = 0;
            $res[self::KEY_TEMPERATURE] = $temperature;
            return $res;
        }
    }
