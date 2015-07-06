<?php

namespace yii2tech\tests\unit\staticdb;

use yii2tech\staticdb\Connection;
use yii2tech\staticdb\QueryProcessor;

class ConnectionTest extends TestCase
{
    public function testSetupQueryProcessor()
    {
        $db = new Connection();
        $queryProcessor = new QueryProcessor();
        $db->setQueryProcessor($queryProcessor);
        $this->assertEquals($queryProcessor, $db->getQueryProcessor());
        $this->assertSame($db, $queryProcessor->db);

        $db = new Connection();
        $defaultQueryProcessor = $db->getQueryProcessor();
        $this->assertTrue($defaultQueryProcessor instanceof QueryProcessor);
        $this->assertSame($db, $defaultQueryProcessor->db);
    }

    public function testReadData()
    {
        $testPath = $this->getTestFilePath();

        $db = new Connection();
        $db->path = $testPath;

        file_put_contents($testPath . DIRECTORY_SEPARATOR . 'test.php', '<?php return [1 => ["name" => "test"]];');

        $data = $db->readData('test');
        $this->assertEquals([1 => ["id" => 1, "name" => "test"]], $data);
    }

    /**
     * @depends testReadData
     */
    public function testWriteData()
    {
        $testPath = $this->getTestFilePath();

        $db = new Connection();
        $db->path = $testPath;

        $data = [
            [
                'id' => 0,
                'name' => 'test',
            ],
        ];
        $db->writeData('test', $data);

        $this->assertEquals($data, $db->readData('test'));
    }

    /**
     * @depends testWriteData
     */
    public function testEnableDataCache()
    {
        $testPath = $this->getTestFilePath();
        $db = new Connection();
        $db->enableDataCache = true;
        $db->path = $testPath;

        $data = [
            [
                'id' => 0,
                'name' => 'test'
            ],
        ];
        $db->writeData('test', $data);
        $db->readData('test');

        $dataOverride = [
            [
                'id' => 0,
                'name' => 'override'
            ],
        ];
        $dbClone = clone $db;
        $dbClone->writeData('test', $dataOverride);

        $this->assertEquals($data, $db->readData('test'));
        $this->assertEquals($dataOverride, $db->readData('test', true));

        $dataAutoRefresh = [
            [
                'id' => 0,
                'name' => 'auto refresh'
            ],
        ];
        $db->writeData('test', $dataAutoRefresh);
        $this->assertEquals($dataAutoRefresh, $db->readData('test'));
    }
}