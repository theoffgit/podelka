<?php
    
	class RequestGetAlefRadio extends AlefRequest
	{
		const KEY_RADIO = "radio";
		const KEY_STATUS = "status";

		public function executeRequest()
		{
			require_once __DIR__."/const/radio.php";
			$res[self::KEY_RADIO] = json_decode(RADIO_CONTENT, true);
			$res[self::KEY_STATUS] = 0;
			return $res;
		}
	}
