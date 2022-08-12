<?php

require_once __DIR__ . "/../../src/const/strings.php";
require_once __DIR__ . "/../../src/const/config.php";
require_once __DIR__ . "/AlefLog.php";

class AlefLocalizer
{
    private static $language;

    public static function initWithLanguage($language)
    {
        if (self::isValidLanguage($language)) {
            self::$language = strtolower($language);
        } else {
            AlefLog::warning("Trying to init AlefLocalizer with invalid language. Skipping. Check it is included in CFG_ALLOWED_LANGUAGES and is valid 2-letter language code");
        }
    }

    public static function getLanguage()
    {
        $language = self::$language;
        if (empty(self::$language)) {
            if (!defined("CFG_DEFAULT_LANG")) {
                AlefLog::warning("CFG_DEFAULT_LANG was not set. Localizer will return all strings back unchanged");
            } else {
                AlefLog::info("Using default language from CFG_DEFAULT_LANG=".CFG_DEFAULT_LANG);
                $language = CFG_DEFAULT_LANG;
            }
        }

        if (empty($language)) {
            return null;
        }

        if (self::isValidLanguage($language)) {
            return strtolower($language);
        } else {
            AlefLog::warning("Trying to use invalid language {$language}");
            return null;
        }
    }


    /*
     * https://en.wikipedia.org/wiki/List_of_ISO_639-1_codes
     * Поддерживаются только 2х буквенные коды стандарта ISO 639-1
     *
     * */
    private static function isValidLanguage($language)
    {
        if (empty($language)) {
            return false;
        }
        if (strlen($language)!=2) {
            return false;
        }

        //Проверяем, что в переменной языка не содержится лишних символов
        if (preg_match("/[^a-z]/", $language)) {
            return false;
        }

        //Проверяем, что в конфиге определен список разрешенных языков и что язык входит в этот список
        if (!defined("CFG_ALLOWED_LANGUAGES") || !in_array($language, CFG_ALLOWED_LANGUAGES)) {
            return false;
        }
        return true;
    }

    public static function _localize($string)
    {
        try {
            if (empty($string)) {
                AlefLog::warning("Trying to localize empty string");
                return null;
            }

            $language = self::getLanguage();
            if (empty($language)) {
                AlefLog::warning("Language is not set. Returning the original string value: {$string}");
                return $string;
            }
            $file_name = STRINGS_FOLDER ."strings-". $language . ".json";
            if (!file_exists($file_name)) {
                AlefLog::warning("Localization file {$file_name} not found");
                return $string;
            }

            $localized_strings = file_get_contents($file_name);
            if (empty($localized_strings)) {
                AlefLog::warning("Localization file {$file_name} is empty");
                return $string;
            }

            $localized_strings_arr = json_decode($localized_strings, true);
            if (empty($localized_strings_arr)) {
                AlefLog::warning("Localization file {$file_name} is not valid json");
                return $string;
            }
            $localized_string_keys = array_keys($localized_strings_arr);
            $localization_key = self::get_localization_key($string, $localized_string_keys);
            $string = $localized_strings_arr[$localization_key] ?? $string;
        } catch (Throwable $e) {
            AlefLog::error("Error while trying localize '{$string}' to language: '{$language}' \n[" . $e->getMessage() ."]");
        } finally {
            return $string;
        }
    }

    public static function get_localization_key($string, $key_bound_list)
    {
        $all_constants=get_defined_constants(true)["user"];
        $filtered_constants = array_intersect_key($all_constants, array_flip($key_bound_list));
        $flipped_constants = array_flip($filtered_constants);
        return $flipped_constants[$string] ?? null;
    }
}
