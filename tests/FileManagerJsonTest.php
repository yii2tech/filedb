<?php

namespace yii2tech\tests\unit\filedb;

use yii2tech\filedb\FileManagerJson;

class FileManagerJsonTest extends TestCase
{
    public function testWriteData()
    {
        $fileManager = new FileManagerJson();

        $testPath = $this->getTestFilePath();
        $fileName = $testPath . DIRECTORY_SEPARATOR . 'test';
        $data = [
            [
                'id' => 0,
                'name' => 'test',
            ],
        ];
        $fileManager->writeData($fileName, $data);

        $this->assertFileExists($fileName . '.json');
    }

    /**
     * @depends testWriteData
     */
    public function testReadData()
    {
        $fileManager = new FileManagerJson();

        $testPath = $this->getTestFilePath();
        $fileName = $testPath . DIRECTORY_SEPARATOR . 'test';
        $data = [
            [
                'id' => 0,
                'name' => 'test',
            ],
        ];
        $fileManager->writeData($fileName, $data);

        $this->assertEquals($data, $fileManager->readData($fileName));
    }
}