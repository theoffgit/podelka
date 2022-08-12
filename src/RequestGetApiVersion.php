<?php

    /*
        Получение версии API
        ?alef_action=getApiVersion
    */

    class RequestGetApiVersion extends AlefRequest
    {
        const KEY_STATUS = "status";
        const KEY_API_VERSION = "api_version";

        public function executeRequest()
        {

            // Пишите код только ниже этой строки, чтобы избежать конфликтов при git merge
            
            $res = $this->getStub();
            $res[self::KEY_STATUS] = 0;
            return $res;
        }

        public function getStub()
        {
            $res = json_decode('{
    "status": 0,
    "api_version": "1.3"
}', true);
            return $res;
        }
    }
