<?php
/**
 * Database class.
 *
 * @package     GenericClasses
 * @subpackage  Classes
 * @version     1.1
 * @author      Kruzya <admin@crazyhackgut.ru>
 */

namespace Kruzya\Generic;

class Database { 
    /**
     * Count executed queries.
     *
     * @var int
     */
    private $iQCount;

    /**
     * Connection to DB.
     *
     * @class \PDO
     */
    private $db;

    /**
     * Tables prefix.
     *
     * @var string
     */
    private $prefix;

    /**
     * Transaction stat.
     *
     * @var bool
     */
    private $bTransaction;

    /**
     * Constructor.
     *
     * @param $address  Address with Server Database.
     *                  Default: '127.0.0.1'
     * @param $port     Port with Server Database.
     *                  Default: 3306
     * @param $login    Username
     *                  Default: 'root'
     * @param $password Password
     *                  Default: ''
     * @param $dbName   Database name.
     *                  Default: ''
     * @param $prefix   Database Prefix
     *                  Default: ''
     * @param $pdoset   Custom PDO settings.
     *                  Default: NULL
     *
     * @return void
     * @throws \PDOException
     */
    public function __construct($address = '127.0.0.1', $port = 3306, $login = 'root', $password = '', $dbName = '', $prefix = '', $pdoset = NULL) {
        // Assign global vars
        $this->prefix   = $prefix;
        $this->iQCount  = 0;

        // Build settings array
        $pdo = [
            \PDO::ATTR_ERRMODE               => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE    => \PDO::FETCH_ASSOC
        ];

        if ($pdoset !== NULL) {
            foreach ($pdoset as $k => $v) {
                $pdo[$k] = $v;
            }
        }

        // Build DSN.
        $dsn = sprintf("mysql:host=%s;dbname=%s;charset=%s;port=%d", $address, $dbName, 'utf8', $port);

        // Connecting...
        $this->db = new \PDO($dsn, $login, $password, $pdo);
    }

    /**
     * Prepare statement.
     *
     * @param $query    Query string.
     * @param $values   Values.
     *                  Default: array()
     * @return \PDOStatement
     */
    private function getStatement($query, $values = array()) {
        $hStatement = $this->db->prepare($query);
        $hStatement->execute($values);
        $this->iQCount++;

        return $hStatement;
    }

    /**
     * Insert prefix for query.
     *
     * @param $query    Query with Placeholders ":prefix:"
     * @return string   Prepared query string.
     */
    public function prepareQuery($query) {
        return str_replace(':prefix:', $this->prefix, $query);
    }

    /**
     * Runs a query.
     *
     * @param $query    Query string.
     * @param $values   Values.
     *                  Default: array()
     * @param $preQeury Prepare query (replace prefixes).
     *                  Default: TRUE
     * @return /PDOStatement
     */
    public function run($query, $values = array(), $preQuery = TRUE) {
        if ($preQuery) {
            $query = $this->prepareQuery($query);
        }

        return $this->getStatement($query, $values, true);
    }

    /**
     * Retrieve one row from result.
     *
     * @param $query    Query string.
     * @param $values   Values.
     *                  Default: array()
     * @param $type     Array type.
     *                  Default: \PDO::FETCH_ASSOC
     * @return array
     */
    public function getRow($query, $values = array(), $type = \PDO::FETCH_ASSOC) {
        $hStatement = $this->run($query, $values);
        return $hStatement->fetch($type);
    }

    /**
     * Retrieve first column from first row from result.
     *
     * @param   $query      Query string.
     * @param   $values     Values.
     *                      Default: array()
     * @param   $defValue   Default value if not found row in database.
     *                      Default: ""
     * @return  mixed
     */
    public function getOne($query, $values = array(), $defValue = "") {
        $row = $this->getRow($query, $values, \PDO::FETCH_NUM);
        if ($row == null || $row[0] == null)
            return $defValue;
        return $row[0];
    }

    /**
     * Retrieve all data from result.
     *
     * @param   $query      Query string.
     * @param   $values     Values.
     *                      Default: array()
     * @param   $type       Array type.
     *                      Default: \PDO::FETCH_ASSOC
     * @return array
     */
    public function getAll($query, $values = array(), $type = \PDO::FETCH_ASSOC) {
        $data = array();
        $hStatement = $this->run($query, $values);
        while ($row = $hStatement->fetch($type)) {
            $data[] = $row;
        }

        return $data;
    }

    /**
     * Getter.
     *
     * @param $key  Key Name
     * @return      mixed
     */
    public function __get($key) {
        if ($key == "count")
            return $this->iQCount;
        else if ($value == "transaction")
            return $this->bTransaction;
        else
            return NULL;
    }

    /**
     * PDO attributes mgr.
     *
     * @param $attr     Attribute Name
     * @param $value    Value to setting. Leave empty, if you need request.
     * @return          mixed
     */
    public function pdoAttribute($attr, $value = NULL) {
        if ($value === NULL) {
            return $this->db->getAttribute($attr);
        } else {
            return $this->db->setAttribute($attr, $value);
        }
    }

    /**
     * Enables transaction mode.
     *
     * @return          bool
     */
    public function startTransaction() {
        if ($this->bTransaction)
            return false;

        $this->bTransaction = $this->db->beginTransaction();
        return $this->bTransaction;
    }

    /**
     * Disables transaction mode.
     *
     * @return          bool
     */
    public function endTransaction() {
        if (!$this->bTransaction)
            return false;

        $result = $this->db->commit();
        $this->bTransaction = false;
        return $result;
    }

    /**
     * Rolls back the current transaction
     *
     * @return          bool
     */
    public function cancelTransaction() {
        if (!$this->bTransaction)
            return false;

        $result = $this->db->rollBack();
        $this->bTransaction = false;
        return $result;
    }

    /**
     * Return last insert ID.
     *
     * @return string
     */
    public function getLastInsertID() {
        return $this->db->lastInsertId();
    }
}
