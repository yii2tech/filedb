<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\filedb;

use yii\base\Component;

/**
 * FileManager is a base class for the file managers.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
abstract class FileManager extends Component
{
    /**
     * @var string data file extension to be used.
     */
    public $fileExtension = 'dat';


    /**
     * Reads the data from data file.
     * @param string $fileName file name without extension.
     * @return array[] data.
     */
    abstract public function readData($fileName);

    /**
     * Writes data into data file.
     * @param string $fileName file name without extension.
     * @param array $data data to be written.
     */
    abstract public function writeData($fileName, array $data);

    /**
     * Composes data file actual name.
     * @param string $fileName data file name without extension.
     * @return string data file full name.
     */
    protected function composeActualFileName($fileName)
    {
        return $fileName . '.' . $this->fileExtension;
    }
}