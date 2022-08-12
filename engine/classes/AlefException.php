<?php

class AlefException extends Exception
{
    private $customUserMessage;

    public function getCustomUserMessage()
    {
        return $this->customUserMessage;
    }

    /**
     * AlefException constructor.
     * @param $code
     * @param $developerMessage string|null Любая информация об ошибке, которая может быть полезна разработчкику, конечный пользователь не увидит сообщения
     * @param $previous Throwable|null
     * @param $customUserMessage string|null Используется в некоторых случаях, когда текст ошибки для пользователя должен быть взят из внешней системы
     */
    //public function __construct($code, $developerMessage=null, Throwable $previous =null, $customUserMessage=null)
    public function __construct($code, $developerMessage='', Throwable $previous =null, $customUserMessage='')
    {
        parent::__construct($developerMessage, $code, $previous);
        $this->customUserMessage = $customUserMessage;
    }
}
