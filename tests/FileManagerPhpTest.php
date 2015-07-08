<?php

namespace yii2tech\tests\unit\filedb;

use yii2tech\filedb\FileManagerPhp;

class FileManagerPhpTest extends TestCase
{
    public function testWriteData()
    {
        $fileManager = new FileManagerPhp();

        $testPath = $this->getTestFilePath();
        $fileName = $testPath . DIRECTORY_SEPARATOR . 'test';
        $data = [
            [
                'id' => 0,
                'name' => 'test',
            ],
        ];
        $fileManager->writeData($fileName, $data);

        $this->assertEquals($data, require($fileName . '.php'));
    }

    /**
     * @depends testWriteData
     */
    public function testReadData()
    {
        $fileManager = new FileManagerPhp();

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