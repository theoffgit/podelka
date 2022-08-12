<?php

    /*
        Авторизация
        Проверяет введённые данные и авторизует пользователя в системе
        ?alef_action=signIn&email=info@alef.im&password=123456
    */

    class RequestSignIn extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_USER_MESSAGE = "user_message";


        public function executeRequest($email, $password)
        {
            $email = (string) $email; // E-mail ||| Пример значения: info@alef.im
            $password = (string) $password; // Пароль ||| Пример значения: 123456

            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge

            /** Удаление лишних символов из строки с E-mail */
            $email = trim($email);

            /** Проверка что нет незаполненных полей */
            Common::isEmpty($email, ERR_EMPTY_EMAIL);
            Common::isEmpty($password, ERR_EMPTY_PASSWORD);

            /** Валидация E-mail */
            Common::isEmailValid($email);

            /** Поиск пользователя в бд */
            $user_data = q1("SELECT * FROM `users` WHERE `email` = ?", [$email]);

            /** Проверка на существование юзера в бд и сходство паролей */
            if (!$user_data || !password_verify($password, $user_data['password_hash'])) {
                /** Если пользователь не найден или пароли не совпадают - в любом случае возвращается ошибка "Указан неверный E-mail или пароль" */
                /** Чтобы избежать возможности перебора зарегистрированных E-mail адресов */
                throw new AlefException(ERR_AUTH_FAILED);
            }
            
            $x = [

            /** Получение ID найденного пользователя */
            $user_id = $user_data["id"];
            /** Авторизация */
            $this->grantAccess($user_id);

            $res[self::KEY_STATUS] = 0;
            return $res;
        }
    }
