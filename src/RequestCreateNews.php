<?php

    /*
        Регистрация
        Проверяет введённые данные и регистрирует пользователя в системе или выдаёт ошибку, если пользователь с таким E-mail уже зарегистрирован
        ?alef_action=signUp&name=Иван&email=info@alef.im&password=123456
    */

    class RequestCreateNews extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_USER_MESSAGE = "user_message";


        public function executeRequest($title, $text)
        {
            $title = (string) $title; //
            $text = (string) $text; //

            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge
            
            /** Удаление лишних символов из строк */
            $title = trim($title);
            $text = trim($text);

            /** Проверка что нет незаполненных полей */
            Common::isEmpty($title, ERR_EMPTY_TITLE);
            Common::isEmpty($text, ERR_EMPTY_TEXT);

            /** Попытка сохранить новость в БД */
            try {
                qi("INSERT INTO `news` (`title`, `text`) VALUES (?, ?)", [$title, $text]);
            } catch (Exception $e) {
                /** Ошибка, если указаный E-mail уже зарегистрирован */
                throw new AlefException($e);
            }

            /** Получение ID зарегистрированного пользователя */
            $news_id = qInsertId();

            $res[self::KEY_STATUS] = 0;
            $res['id'] = $news_id;
            return $res;
        }
    }
