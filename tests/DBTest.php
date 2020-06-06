<?php
use PHPUnit\Framework\TestCase;

use Kore\DB;
use Kore\Config;
use Kore\Log;

class DBTest extends TestCase
{
    protected function setUp(): void
    {
        Config::create('test');
        Log::init('test');
    }

    public function testConnectionNoConfig()
    {
        $this->expectExceptionMessage('Databse config noconfig is not found!');
        DB::connection("noconfig");
    }

    public function testConnection()
    {
        $this->assertInstanceOf(DB::class, DB::connection());
    }

    public function testCRUD()
    {
        // CREATE

        $query =<<<SQL
INSERT INTO test
    SET
        col1 = :col1,
        col2 = :col2,
        col3 = :col3
SQL;
        DB::connection()->insert($query, [
            'col1' => 1,
            'col2' => 'hoge',
            'col3' => 'fugafuga']);

        $this->assertSame(true, true);

        // READ

        $query =<<<SQL
SELECT col1, col2, col3 FROM test
    WHERE col1 = :col1
SQL;
        $result = DB::connection()->select($query, [
            'col1' => 1]);

        $this->assertSame(1, count($result));

        // UPDATE

        $query =<<<SQL
UPDATE test
    SET
        col2 = :col2
    WHERE 
        col1 = :col1
SQL;
        DB::connection()->update($query, [
            'col1' => 1,
            'col2' => 'piyo']);

        $this->assertSame(true, true);       

        // DELETE

        $query =<<<SQL
DELETE FROM test
    WHERE col1 = :col1
SQL;
        DB::connection()->delete($query, [
            'col1' => 1]);

        $this->assertSame(true, true);
    }

    public function testGetInClause() {
        $this->assertSame(['IN (test_0, test_1, test_2)',['test_0' => 1, 'test_1' => 2, 'test_2' => 3]], DB::getInClause('test', [1,2,3]));
    }
}