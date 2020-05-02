<?php
namespace CoreLibrary;

use CoreLibrary\Config;
use CoreLibrary\Log;

class DB
{
    /**
     * @var \PDO
     */
    protected $pdo;

    /**
     * @param string $dbconfig
     * @return void
     */
    private final function __construct($dbconfig)
    {
        $this->connect($dbconfig);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public final function __clone()
    {
        throw new \Exception('__clone is not allowed!');
    }

    /**
     * @param string $dbconfig
     * @return self
     */
    public static function connection($dbconfig='database')
    {
        static $instances = [];
        if (empty($instances[$dbconfig])) {
            $instances[$dbconfig] = new static($dbconfig);
        }
        return $instances[$dbconfig];
    }

    /**
     * @param string $dbconfig
     * @return void
     * @throws \PDOException
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
     * @param string $query
     * @param array $param
     * @return array
     */
    public function select($query, $params = [])
    {
        $stm = $this->execute($query, $params);

        $result = $stm->fetchAll();
        return $result;
    }

    /**
     * @param string $query
     * @param array $param
     * @return int
     */
    public function count($query, $params = [])
    {
        $stm = $this->execute($query, $params);

        $result = $stm->fetchColumn();
        return $result;
    }

    /**
     * @param string $query
     * @param array $param
     * @return int
     */
    public function insert($query, $params = [])
    {
        $stm = $this->execute($query, $params);

        return self::$pdo->lastInsertId();
    }

    /**
     * @param string $query
     * @param array $param
     * @return int
     */
    public function update($query, $params = [])
    {
        $stm = $this->execute($query, $params);

        return $stm->rowCount();
    }

    /**
     * @param string $query
     * @param array $param
     * @return int
     */
    public function delete($query, $params = [])
    {
        $stm = $this->execute($query, $params);

        return $stm->rowCount();
    }

    /**
     * @param callable $callback
     * @return void
     */
    public function transaction($callback)
    {
        try {
            $this->pdo->beginTransaction();
            $callback();
            $this->pdo->commit();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            $this->pdo->rollback();
        }
    }

    /**
     * @param string $query
     * @param array $param
     * @return \PDOStatement
     */
    private function execute($query, $params)
    {
        $stm = $this->pdo->prepare($query);

        foreach ($params as $key => $value) {
            $stm->bindValue(":{$key}", $value, $this->datatype($value));
        }

        $stm->execute();
        return $stm;
    }

    /**
     * @param mixed $value
     * @return int
     */
    private function datatype($value)
    {
        $datatype;
        switch (gettype($value)) {
            case 'boolean':
                $datatype = \PDO::PARAM_BOOL;
            case 'integer':
                $datatype = \PDO::PARAM_INT;
            case 'double':
                // doubleに対応するdatatypeがないのでSTR
                $datatype = \PDO::PARAM_STR;
            case 'string':
                $datatype = \PDO::PARAM_STR;
            case 'NULL':
                $datatype = \PDO::PARAM_NULL;
            default :
                $datatype = \PDO::PARAM_STR;
        }
        return $datatype;
    }
}
