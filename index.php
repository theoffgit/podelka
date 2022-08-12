<?php
$alefAction = (string)$_REQUEST["alef_action"];
$pretty = (strpos($alefAction, "!") !== false);
$pretty = true;
$alefAction = str_replace("!", "", $alefAction);

if ($pretty) {
    ini_set("display_errors", "On");
    error_reporting(E_ALL);
} else {
    ini_set("display_errors", "Off");
    error_reporting(0);
}


if (empty($alefAction)) {
    // Если не указан никакой action – показываем информационную страницу с документацией
    header("Content-Type:text/html");
    require_once __DIR__ . "/engine/info.php";
    defaultProjectInfoPage();
} else {
    header("Content-Type:application/json");
    require_once __DIR__ . "/engine/classes/AlefCore.php";
    $language = $_REQUEST[KEY_LANG] ?? null;
    $aqTamperId = trim($_REQUEST[KEY_AQ_TAMPER_ID] ?? "");
    if(!empty($aqTamperId)) {
        $result = AlefCore::getTamperResponse($alefAction,$aqTamperId);
    }
    if (empty($result)) {
        $result = AlefCore::executeRequest($alefAction, $language);
    }
    }
if ($pretty) {
    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
} else {
    echo json_encode($result);
}
