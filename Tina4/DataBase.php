<?php
/**
 * Created by PhpStorm.
 * User: Andre van Zuydam
 * Date: 2016/02/29
 * Time: 07:50 PM
 */
namespace Tina4;

class DataBase
{
    public $dbh;           //database handle
    public $databaseName;
    public $port;
    public $hostName;
    public $username;
    public $password;
    public $dateFormat;
    public $fetchLimit=100; //limit an sql result set
    public $cache;
    public $transaction;


    public function native_open() {
        error_log("Implement the public method native_open for your database engine");
        return false;
    }

    public function native_close() {
        error_log("Implement the public method native_close for your database engine");
        return false;
    }

    public function native_exec() {
        error_log("Implement the public method native_exec for your database engine");
        return false;
    }

    public function native_error() {
        error_log("Implement the public method native_error for your database engine");
        return false;
    }

    public function native_getLastId() {
        error_log("Implement the public method native_getLastId for your database engine");
        return false;
    }

    public function native_fetch() {
        error_log("Implement the public method native_fetch for your database engine");
        return false;
    }

    /**
     * Override this method for your specific database engine, this should work for most database engines
     * See https://datatables.net/manual/index
     * @return array
     */
    public function native_dataTablesFilter () {
        $request = $_REQUEST;

        if (!empty($request["columns"])) {
            $columns = $request["columns"];
        }

        if (!empty($request["order"])) {
            $orderBy = $request["order"];
        }

        $filter = null;
        if (!empty($request["search"])) {
            $search = $request["search"];

            foreach ($columns as $id => $column) {
                if (($column["searchable"] == "true") && !empty($search["value"])) {
                    $filter[] = "upper(".$column["data"].") like '%" . strtoupper($search["value"]) . "%'";
                }
            }
        }

        $ordering = null;
        if (!empty($orderBy)) {
            foreach ($orderBy as $id => $orderEntry) {
                $ordering[] = $columns[$orderEntry["column"]]["data"] . " " . $orderEntry["dir"];
            }
        }

        $order = "";
        if (count($ordering) > 0) {
            $order = "order by " . join(",", $ordering);
        }

        $where = "";
        if (count($filter) > 0) {
            $where = "(". join (" or ", $filter).")";
        }

        if (!empty($request["start"])) {
            $start = $request["start"];
        } else {
            $start = 0;
        }

        if (!empty($request["length"])) {
            $length = $request["length"];
        } else {
            $length = 10;
        }

        return ["length" => $length, "start" => $start, "orderBy" => $order, "where" => $where];
    }

    /**
     * DataBase constructor.
     * @param $database - In the form [host/port:database]
     * @param string $username
     * @param string $password
     * @param string $dateFormat
     */
    public function __construct($database, $username="", $password="", $dateFormat="yyyy-mm-dd") {
        define ("DATA_ARRAY", 0);
        define ("DATA_OBJECT", 1);
        define ("DATA_NUMERIC", 2);

        define ("DATA_TYPE_TEXT", 0);
        define ("DATA_TYPE_NUMERIC", 1);
        define ("DATA_TYPE_BINARY", 2);

        define ("DATA_ALIGN_LEFT", 0);
        define ("DATA_ALIGN_RIGHT", 1);

        define ("DATA_CASE_UPPER", 1);

        define ("DATA_NO_SQL", "ERR001");

        global $cache;
        if (!empty($cache)) {
            $this->cache = $cache;
        }

        $this->username = $username;
        $this->password = $password;

        if (strpos($database, ":") !== false) {
            $database = explode(":", $database,2);
            $this->hostName = $database[0];
            $this->databaseName = $database[1];

            if (strpos($this->hostName, "/") !== false) {
                $port = explode ("/", $this->hostName);
                $this->hostName = $port[0];
                $this->port = $port[1];
            }
        } else {
            $this->hostName = "";
            $this->databaseName = $database;
        }
        $this->dateFormat = $dateFormat;
        $this->native_open();
    }


    public function close() {
        $this->native_close();
    }

    public function exec() {
        $params = func_get_args();
        if (count($params) > 0) {
            return call_user_func_array(array($this, "native_exec"), $params);
        } else  {
            return (new DataError(DATA_NO_SQL, "No sql statement found for exec"))->getError();
        }

    }

    public function getLastId() {
        return $this->native_getLastId();
    }

    /**
     * Fetch a result set of DataResult
     * @param string $sql
     * @param int $noOfRecords
     * @param int $offSet
     * @return DataResult
     */
    public function fetch($sql="", $noOfRecords=10, $offSet=0) {
        $params = func_get_args();
        return call_user_func_array(array($this, "native_fetch"), $params);
    }

    public function  commit() {
        return $this->native_commit();
    }


    public function error() {
        return $this->native_error();
    }


}
