<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\filedb;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\Json;

/**
 * FileManagerJson performs data storage using JSON format.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FileManagerJson extends FileManager
{
    /**
     * @var string data file extension to be used.
     */
    public $fileExtension = 'json';


    /**
     * {@inheritdoc}
     */
    public function readData($fileName)
    {
        $fileName = $this->composeActualFileName($fileName);
        if (!is_file($fileName)) {
            throw new InvalidConfigException("File '{$fileName}' does not exist.");
        }
        $content = file_get_contents($fileName);
        return Json::decode($content);
    }

    /**
     * {@inheritdoc}
     */
    public function writeData($fileName, array $data)
    {
        $fileName = $this->composeActualFileName($fileName);
        $content = Json::encode($data);
        $bytesWritten = file_put_contents($fileName, $content);
        if ($bytesWritten <= 0) {
            throw new Exception("Unable to write file '{$fileName}'.");
        }
    }
} 