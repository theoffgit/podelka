<?php
class AlefSQL
{
    private static $dbConnection = null;

    private static function initDB()
    {
        if (empty(self::$dbConnection)) {
            self::$dbConnection = new PDO("mysql:host=localhost;dbname=aqbelogub; charset=utf8", "root", "root");
            self::$dbConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
            self::$dbConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            self::$dbConnection->exec("set names utf8");
        }
    }

    private static function parseBoundArrays($sql, $params)
    {
        foreach ($params as $key=>$value) {
            if (is_array($value)) {
                if (preg_match("/[^a-zA-Z0-9_]/", $key)) {
                    throw new AlefException(ERR_DB_EXCEPTION, "Invalid name for prepared statement parameter '{$key}'");
                }
                unset($params[$key]);
                $arr_params = [];
                for ($i=0;$i<count($value);$i++) {
                    $arr_params[$key."_arr_unqe08dfa_".$i] = $value[$i];
                }
                if (preg_match_all("/\:{$key}\b/", $sql)>1) {
                    throw new AlefException(ERR_DB_EXCEPTION, "Prepared statement parameter '{$key}' used several times in query. Choose different name");
                }
                $params = array_merge($params, $arr_params);
                $sql = preg_replace("/\:{$key}\b/", implode(", ", array_map(function ($item) {
                    return ":" . $item;
                }, array_keys($arr_params))), $sql);
            }
        }
        return [$sql, $params];
    }

    public static function dq($sql, $params)
    {
        list($sql, $params) = self::parseBoundArrays($sql, $params);
        $keys = array();
        foreach ($params as $key => $value) {
            if (is_string($key)) {
                $keys[] = '/:'.$key.'/';
            } else {
                $keys[] = '/[?]/';
            }
        }

        $sql = preg_replace($keys, $params, $sql, 1, $count);
        return $sql;
    }

    public static function q($sql, $params) // запрос к базе — короткое имя для удобства
    {
		try {
			self::initDB();
			list($sql, $params) = self::parseBoundArrays($sql, $params);
			$stmt = self::$dbConnection->prepare($sql);
			$stmt->execute($params);
			$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			if($e->getCode() == "HY000") {
				self::$dbConnection = NULL;
			} 
				
			throw $e;
			
		}
    }

    public static function q1($sql, $params) //запрос одной строчки
    {
		try {
			self::initDB();
			list($sql, $params) = self::parseBoundArrays($sql, $params);
			$stmt = self::$dbConnection->prepare($sql);
			$stmt->execute($params);
			$result = $stmt->fetch(PDO::FETCH_ASSOC);
			return $result;
		} catch(PDOException $e) {
			if($e->getCode() == "HY000") {
				self::$dbConnection = NULL;
			} 
				
			throw $e;
		}
    }

    public static function qi($sql, $params) // Используется для insert и update
    {
		try  {
			self::initDB();
			list($sql, $params) = self::parseBoundArrays($sql, $params);
			$stmt = self::$dbConnection->prepare($sql);
			if ($stmt->execute($params)) {
				return true;
			} else {
				return false;
			}
		} catch(PDOException $e) {
			if($e->getCode() == "HY000") {
				self::$dbConnection = NULL;
			} 
				
			throw $e;			
		}
    }

    public static function qInsertId() // Последнйи автоинкриментный ID
    {
        self::initDB();
		try {
        	return self::$dbConnection->lastInsertId();
		} catch(PDOException $e) {
			if($e->getCode() == "HY000") {
				self::$dbConnection = NULL;
			} 
			throw $e;			
		}
    }
}
