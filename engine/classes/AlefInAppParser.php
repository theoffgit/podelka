<?php


class AlefInAppParser
{

    const SANDBOX_URL = 'https://sandbox.itunes.apple.com/verifyReceipt';
    const PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

    /**
     * Функция возвращает на основе base64 ресипта от Apple и пароля его содержимое
     * @param $receipt
     * @param $password
     * @return mixed|null
     */
    public static function validate_receipt_ios($receipt, $password)
    {
        $data = json_encode(["receipt-data" => $receipt, "password" => $password]);

        $options = [
            "http" => [
                "header"  => "Content-type: application/x-www-form-urlencoded",
                "method"  => "POST",
                "content" => $data
            ],
        ];
        $context = stream_context_create($options);
        $result = file_get_contents(self::PRODUCTION_URL, false, $context);
        $test = json_decode($result, true);
        if ($test["status"] == 21007) {
            $result = file_get_contents(self::SANDBOX_URL, false, $context);
        }

        if ($result === false) {
            return null;
        } else {
            return json_decode($result, true);
        }
    }

    public static function validate_receipt_android($receipt)
    {
        $res = [];
        if (!empty($receipt)) {
            foreach ($receipt as $item) {
                $validated_item = self::validate_purchase_android($item["id"], $item["token"]);
                if (!empty($validated_item)) {
                    $res[] = $validated_item;
                }
            }
        }
        return $res;
    }


    private static function get_google_access_token()
    {
        $url = "https://accounts.google.com/o/oauth2/token";
        $params = [
            "client_id"     => CFG_ANDROID_CLIENT_ID,
            "client_secret" => CFG_ANDROID_CLIENT_SECRET,
            "refresh_token" => CFG_ANDROID_REFRESH_TOKEN,
            "grant_type"    => "refresh_token"
        ];
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($curl);
        curl_close($curl);
        $res_arr = json_decode($res, true);
        return $res_arr["access_token"] ?? null;
    }

    private static function google_get_request($url, $access_token)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $access_token]);
        $json_response = curl_exec($curl);
        curl_close($curl);
        return $json_response;
    }

    public static function google_get_product_info($product_id, $access_token)
    {
        $url = "https://www.googleapis.com/androidpublisher/v3/applications/" . CFG_ANDROID_APPLICATION_ID . "/inappproducts/{$product_id}";
        return self::google_get_request($url, $access_token);
    }


    public static function validate_purchase_android($product_id, $purchase_token)
    {
        if (empty($product_id) || empty(trim($product_id))) {
            return null;
        }
        $access_token = self::get_google_access_token();
        $product = self::google_get_product_info($product_id, $access_token);

        $product = json_decode($product, true);
        if (empty($product)) {
            return null;
        }
        if ($product["purchaseType"] == "subscription") {
            $type = "subscriptions";
        } else {
            $type = "products";
        }

        $url = "https://www.googleapis.com/androidpublisher/v3/applications/" . CFG_ANDROID_APPLICATION_ID . "/purchases/{$type}/{$product_id}/tokens/{$purchase_token}";
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HTTPAUTH, CURLAUTH_ANY);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Bearer " . $access_token]);
        $json_response = curl_exec($curl);
        curl_close($curl);
        $res = json_decode($json_response, true);
        $res["product_id"] = $product_id;
        return $res;
    }


    /**
     * @param $receipt_data
     * @return array
     */
    public static function parse_receipt_data_ios($receipt_data)
    {
        $receipt_info = $receipt_data["receipt"] ?? null;
        $res = [];

        if (!empty($receipt_info)) {

            if (array_key_exists("latest_receipt_info", $receipt_data)) {
                $in_apps = $receipt_data["latest_receipt_info"];
            } else {
                $in_apps = $receipt_data["receipt"]["in_app"];
            }

            if (!empty($in_apps)) {
                foreach ($in_apps as $in_app) {
                    $item = [
                        "product_id"      => $in_app["product_id"],
                        "is_subscription" => empty($in_app["web_order_line_item_id"]) ? 0 : 1,
                        "purchase_ts"     => $in_app["purchase_date_ms"] / 1000,
                        "expires_ts"      => $in_app["expires_date_ms"] / 1000,
                        "is_trial_period" => $in_app["is_trial_period"] == "true" ? 1 : 0
                    ];

                    if (empty($item["expires_ts"]) || $item["expires_ts"] > time()) {
                        $res[$in_app["product_id"]] = $item;
                    }

                }
            }
        } else {
            AlefLog::error("Error while parsing receipt data: " . json_encode($receipt_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
        }

        return $res;
    }

    /**
     * @param $receipt_data
     * @return array
     */
    public static function parse_receipt_data_android($receipt_data)
    {
        $res = [];
        foreach ($receipt_data as $in_app) {
            $item = [
                "product_id"      => $in_app["product_id"],
                "is_subscription" => 1,
                "purchase_ts"     => ceil(($in_app["startTimeMillis"] ?? 0) / 1000),
                "expires_ts"      => ceil(($in_app["expiryTimeMillis"] ?? 0) / 1000),
                "is_trial_period" => 0
            ];

            if (empty($item["expires_ts"]) || $item["expires_ts"] > time()) {
                $res[$in_app["product_id"]] = $item;
            }

        }
        return $res;
    }


    public static function parseInApps($inApps)
    {
        if (empty($inApps)) {
            return [];
        }
        $inAppsAndroid = json_decode($inApps, 1);

        if (empty($inAppsAndroid)) //iOS
        {
            AlefLog::debug("Checking receipt with Apple:\n" . $inApps);
            $receiptData = self::validate_receipt_ios($inApps, CFG_IOS_IN_APP_PASSWORD);
            AlefLog::debug("Response from Apple:\n" . json_encode($receiptData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $res = self::parse_receipt_data_ios($receiptData);
        } else { //Android
            AlefLog::debug("Checking in-apps with Google:\n" . $inApps);
            $receiptData = self::validate_receipt_android($inAppsAndroid);
            AlefLog::debug("Response from Google:\n" . json_encode($receiptData, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
            $res = self::parse_receipt_data_android($receiptData);
        }

        return $res;

    }
}