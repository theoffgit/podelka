<?php

    /*
        Регистрация
        Проверяет введённые данные и регистрирует пользователя в системе или выдаёт ошибку, если пользователь с таким E-mail уже зарегистрирован
        ?alef_action=signUp&name=Иван&email=info@alef.im&password=123456
    */

    class RequestUpdateNews extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_USER_MESSAGE = "user_message";


        public function executeRequest($id, $title, $text)
        {
            $id = (int) $id;
            $title = (string) $title; //
            $text = (string) $text; //

            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge
            
            /** Удаление лишних символов из строк */
            $id = intval($id);
            $title = trim($title);
            $text = trim($text);

            /** Проверка что нет незаполненных полей */
            Common::isEmpty($id, ERR_EMPTY_TITLE);

            /** Попытка сохранить новость в БД */
            try {
                qi("UPDATE `news` SET `title`=?, `text`=? WHERE `id`=?", [$title, $text, $id]);
            } catch (Exception $e) {
                /** Ошибка, если указаный E-mail уже зарегистрирован */
                throw new AlefException($e);
            }

            $res[self::KEY_STATUS] = 0;
            $res['id'] = $id;
            return $res;
        }
    }
