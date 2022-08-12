<?php

    /*
       Удалить новость
    */

    class RequestDeleteNews extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_USER_MESSAGE = "user_message";


        public function executeRequest($id)
        {
            $id = (int) $id;

            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge

            /** Удаление лишних символов из строки с E-mail */
            $id = intval($id);

            /** Проверка что нет незаполненных полей */
            Common::isEmpty($id, ERR_EMPTY_EMAIL);

            $user_data = q1("DELETE FROM `news` WHERE `id` = ?", [$id]);

            /** Проверка на существование юзера в бд и сходство паролей */
            if (!$user_data) {
                /** Если пользователь не найден или пароли не совпадают - в любом случае возвращается ошибка "Указан неверный E-mail или пароль" */
                /** Чтобы избежать возможности перебора зарегистрированных E-mail адресов */
                throw new AlefException();
            }
            

            $res[self::KEY_STATUS] = 0;
            return $user_data;
        }
    }
