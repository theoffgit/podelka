<?php
class AlefPushManager
{
    const API_URL = "https://fcm.googleapis.com/fcm/send";

    public static function sendPush($tokens, $title, $message, $badge = 0, $sound="default")
    {
        $tokens = implode(",",$tokens);
        $notification = [
            "title"    => $title,
            "subtitle" => "",
            "body"     => $message,
            "sound"    => $sound,
            "badge"    => $badge
        ];

        $params = [
            "registration_ids" => $tokens,
            "notification"     => $notification,
            "data"             => $notification,
            "time_to_live"     => 60 * 60 * 6,
            "priority"         => "high"
        ];

        $json = json_encode($params);
        $headers = [
            "Content-Type: application/json",
            "Authorization: key= ". CFG_FCM_SERVER_KEY
        ];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::API_URL);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
        curl_setopt($ch, CURLOPT_POSTFIELDS, $json);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);
        curl_close($ch);
        $res = json_decode($response, true);
        if ($res===null)
        {
            throw new AlefException(ERR_UNKNOWN, $response);
        }
        else {
            return $res;
        }
    }
}