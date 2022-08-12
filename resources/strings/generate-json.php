<?php
require_once __DIR__ . "/../../src/const/strings.php";
require_once __DIR__ . "/../../src/const/config.php";

$all_constants = get_defined_constants(true)["user"];
$filtered_keys = array_filter(array_keys($all_constants), function ($item){return strpos($item,"STR_")===0;});
$filtered_constants = array_intersect_key($all_constants, array_flip($filtered_keys));
foreach (CFG_ALLOWED_LANGUAGES as $language) {
    $filename = __DIR__."/strings-{$language}.json";

    if(file_exists($filename))
    {
        $strings = file_get_contents($filename);
        $strings_arr = json_decode($strings, true);
        if(!empty($strings_arr))
        {
            $filtered_constants = array_merge($filtered_constants,$strings_arr);
        }
        $filtered_constants["STR_LANG"] = $language;

    }

    file_put_contents($filename, json_encode($filtered_constants, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}
