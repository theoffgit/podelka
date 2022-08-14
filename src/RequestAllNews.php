<?php

    /*
       Удалить новость
    */

    class RequestAllNews extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_USER_MESSAGE = "user_message";


        public function executeRequest()
        {
            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge

            /** Удаление лишних символов из строки с E-mail */

            /** Проверка что нет незаполненных полей */

            /** Поиск пользователя в бд */
            $user_data['news'] = q("SELECT * FROM `news`", []);

            /** Проверка на существование юзера в бд и сходство паролей */
            //var_dump($user_data);
            //if (!$user_data) {
            //    /** Если пользователь не найден или пароли не совпадают - в любом случае возвращается ошибка "Указан неверный E-mail или пароль" */
            //    /** Чтобы избежать возможности перебора зарегистрированных E-mail адресов */
            //    throw new AlefException();
            //}
            

            $user_data[self::KEY_STATUS] = 0;
            return $user_data;
        }
    }
