<?php

    /*
        Регистрация
        Проверяет введённые данные и регистрирует пользователя в системе или выдаёт ошибку, если пользователь с таким E-mail уже зарегистрирован
        ?alef_action=signUp&name=Иван&email=info@alef.im&password=123456
    */

    class RequestSignUp extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_USER_MESSAGE = "user_message";


        public function executeRequest($name, $email, $password)
        {
            $name = (string) $name; // Имя ||| Пример значения: Иван Иванов
            $email = (string) $email; // E-mail ||| Пример значения: info@alef.im
            $password = (string) $password; // Пароль ||| Пример значения: 123456

            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge
            
            /** Удаление лишних символов из строк */
            $name = trim($name);
            $email = trim($email);

            /** Проверка что нет незаполненных полей */
            Common::isEmpty($name, ERR_EMPTY_NAME);
            Common::isEmpty($email, ERR_EMPTY_EMAIL);
            Common::isEmpty($password, ERR_EMPTY_PASSWORD);

            /** Валидация E-mail */
            Common::isEmailValid($email);

            /** Превращение пароля в зашифрованный хэш */
            $password_hash = password_hash($password, PASSWORD_DEFAULT);

            /** Попытка сохранить юзера в БД */
            try {
                qi("INSERT INTO `users` (`name`, `email`, `password_hash`) VALUES (?, ?, ?)", [$name, $email, $password_hash]);
            } catch (Exception $e) {
                /** Ошибка, если указаный E-mail уже зарегистрирован */
                throw new AlefException(ERR_EXISTING_EMAIL);
            }

            /** Получение ID зарегистрированного пользователя */
            $user_id = qInsertId();
            /** Авторизация */
            $this->grantAccess($user_id);

            $res[self::KEY_STATUS] = 0;
            return $res;
        }
    }
