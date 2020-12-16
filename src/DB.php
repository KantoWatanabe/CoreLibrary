<?php
/**
 * Kore : Simple And Minimal Framework
 *
 */

namespace Kore;

use Kore\Config;
use Kore\Log;

/**
 * DB class
 *
 * Managing database operations
 */
class DB
{
    /**
     * \PDO instance
     *
     * @var \PDO
     */
    protected $pdo;

    /**
     * __construct method
     *
     * @param string $dbconfig DB configuration key
     * @return void
     */
    final private function __construct($dbconfig)
    {
        $this->connect($dbconfig);
    }

    /**
     * __clone method
     *
     * @return void
     * @throws \Exception Thrown in when __clone is called
     */
    final public function __clone()
    {
        throw new \Exception('__clone is not allowed!');
    }

    /**
     * Get DB instance corresponding to the DB configuration key
     *
     * @param string $dbconfig DB configuration key
     * @return self DB instance
     */
    public static function connection($dbconfig='database')
    {
        static $instances = array();
        if (empty($instances[$dbconfig])) {
            $instances[$dbconfig] = new static($dbconfig);
        }
        return $instances[$dbconfig];
    }

    /**
     * Access DB
     *
     * @param string $dbconfig DB configuration key
     * @return void
     * @throws \Exception Thrown in when the DB configuration does not exist
     * @throws \PDOException Thrown when DB access fails
     */
    protected function connect($dbconfig)
    {
        $config = Config::get($dbconfig);
        if ($config === null) {
            throw new \Exception("Databse config $dbconfig is not found!");
        }
        $host = $config['host'];
        $db = $config['db'];
        $port = $config['port'];
        $user = $config['user'];
        $pass = $config['pass'];

        $dsn = sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8', $host, $port, $db);
        $this->pdo = new \PDO($dsn, $user, $pass);
        $this->pdo->setAttribute(\PDO::ATTR_EMULATE_PREPARES, false);
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Get \PDO instance
     *
     * @return \PDO \PDO instance
     */
    public function getPdo()
    {
        return $this->pdo;
    }

    /**
     * Run the select statement
     *
     * @param string $query SQL query
     * @param array<mixed> $params SQL query parameters
     * @return array<mixed> query results
     */
    public function select($query, $params = array())
    {
        $stm = $this->execute($query, $params);

        $result = $stm->fetchAll(\PDO::FETCH_ASSOC);
        return $result !== false ? $result : array();
    }

    /**
     * Run the select statement, get only one record
     *
     * @param string $query SQL query
     * @param array<mixed> $params SQL query parameters
     * @return array<mixed> query results
     */
    public function selectFirst($query, $params = array())
    {
        $stm = $this->execute($query, $params);
        
        $result = $stm->fetch(\PDO::FETCH_ASSOC);
        return $result !== false ? $result: array();
    }

    /**
     * Run the select count statement
     *
     * @param string $query SQL query
     * @param array<mixed> $params SQL query parameters
     * @return mixed query results
     */
    public function count($query, $params = array())
    {
        $stm = $this->execute($query, $params);

        $result = $stm->fetchColumn();
        return $result !== false ? $result : 0;
    }

    /**
     * Run the insert statement
     *
     * @param string $query SQL query
     * @param array<mixed> $params SQL query parameters
     * @return string last insert ID
     */
    public function insert($query, $params = array())
    {
        $this->execute($query, $params);

        return $this->pdo->lastInsertId();
    }

    /**
     * Run the update statement
     *
     * @param string $query SQL query
     * @param array<mixed> $params SQL query parameters
     * @return int number of updated records
     */
    public function update($query, $params = array())
    {
        $stm = $this->execute($query, $params);

        return $stm->rowCount();
    }

    /**
     * Run the delete statement
     *
     * @param string $query SQL query
     * @param array<mixed> $params SQL query parameters
     * @return int number of deleted records
     */
    public function delete($query, $params = array())
    {
        $stm = $this->execute($query, $params);

        return $stm->rowCount();
    }

    /**
     * Run in a transaction
     *
     * Rollback if an exception is raised in the callback.
     * @param callable $callback callback processing
     * @return void
     */
    public function transaction($callback)
    {
        try {
            $this->beginTransaction();
            $callback();
            $this->commit();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->rollback();
        }
    }

    /**
     * Begin transaction
     *
     * @return void
     */
    public function beginTransaction()
    {
        $this->pdo->beginTransaction();
    }

    /**
     * Commit
     *
     * @return void
     */
    public function commit()
    {
        $this->pdo->commit();
    }

    /**
     * Rollback
     *
     * @return void
     */
    public function rollback()
    {
        $this->pdo->rollback();
    }

    /**
     * Get the IN clause
     *
     * @param string $marker parameter marker
     * @param array<mixed> $values value specified in the IN clause
     * @return array<mixed> IN clause information
     *                      array(
     *                          'IN (:MARKER_0, :MARKER_1, :MARKER_2)',
     *                          array(
     *                              'MARKER_0' => 'value',
     *                              'MARKER_1' => 'value',
     *                              'MARKER_2' => 'value'
     *                          )
     *                      )
     */
    public static function getInClause($marker, $values)
    {
        $inClause = 'IN (';
        $params = array();
        foreach ($values as $i => $value) {
            if ($i !== 0) {
                $inClause .= ', ';
            }
            $key = $marker.'_'.$i;
            $inClause .= ':'.$key;
            $params[$key] = $value;
        }
        $inClause .= ')';
        return [$inClause, $params];
    }

    /**
     * Run the query statement
     *
     * @param string $query SQL query
     * @param array<mixed> $params SQL query parameters
     * @return \PDOStatement<mixed> \PDOStatement object
     */
    private function execute($query, $params)
    {
        $stm = $this->pdo->prepare($query);

        $keys = array();
        $values = array();
        foreach ($params as $key => $value) {
            $parameter = ":{$key}";
            $keys[] = $parameter;
            $values[] = $this->debugValue($value);
            $stm->bindValue($parameter, $value, $this->dataType($value));
        }

        $start = microtime(true);
        $stm->execute();
        $end = microtime(true);
        /** @phpstan-ignore-next-line */
        Log::debug(sprintf('%f - %s', $end-$start, str_replace($keys, $values, preg_replace('/[\n\t\s]+/', ' ', $query))));
        return $stm;
    }

    /**
     * Get the debug value from the variable
     *
     * @param mixed $value variable
     * @return mixed debug value
     */
    private function debugValue($value)
    {
        switch (gettype($value)) {
            case 'string':
                $debugValue = "'$value'";
                break;
            case 'boolean':
                $debugValue = $value ? 'true' : 'false';
                break;
            case 'NULL':
                $debugValue = 'NULL';
                break;
            default:
                $debugValue = $value;
        
        }
        return $debugValue;
    }

    /**
     * Get the data_type from the variable
     *
     * @param mixed $value variable
     * @return int data_type
     */
    private function dataType($value)
    {
        switch (gettype($value)) {
            case 'boolean':
                $datatype = \PDO::PARAM_BOOL;
                break;
            case 'integer':
                $datatype = \PDO::PARAM_INT;
                break;
            case 'double':
                // doubleに対応するdatatypeがないのでSTR
                $datatype = \PDO::PARAM_STR;
                break;
            case 'string':
                $datatype = \PDO::PARAM_STR;
                break;
            case 'NULL':
                $datatype = \PDO::PARAM_NULL;
                break;
            default:
                $datatype = \PDO::PARAM_STR;
        }
        return $datatype;
    }
}
