<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\filedb;

use Yii;
use yii\base\Component;
use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\di\Instance;
use yii\helpers\VarDumper;

/**
 * Connection
 *
 * @property QueryProcessor|array|string $queryProcessor the query processor object or its configuration.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Connection extends Component
{
    /**
     * @var string path to directory, which holds data files.
     */
    public $path = '@app/filedb';
    /**
     * @var string name of the data key, which should be used as row unique id - primary key.
     * If source data holds no corresponding key, the key of the row in source array will be used as its value.
     */
    public $primaryKeyName = 'id';
    /**
     * @var boolean whether to cache read data in memory.
     * While enabled this option may speed up program execution, but will cost extra memory usage.
     */
    public $enableDataCache = true;

    /**
     * @var QueryProcessor|array|string the query processor object or its configuration.
     */
    private $_queryProcessor = 'yii2tech\filedb\QueryProcessor';
    /**
     * @var boolean whether [[queryProcessor]] has been initialized or not.
     */
    private $isQueryProcessorInitialized = false;
    /**
     * @var array read data cache.
     */
    private $dataCache = [];


    /**
     * @param array|string|QueryProcessor $queryProcessor query processor instance or its configuration.
     */
    public function setQueryProcessor($queryProcessor)
    {
        $this->_queryProcessor = $queryProcessor;
        $this->isQueryProcessorInitialized = false;
    }

    /**
     * @return QueryProcessor query processor instance
     */
    public function getQueryProcessor()
    {
        if (!$this->isQueryProcessorInitialized) {
            $this->_queryProcessor = Instance::ensure($this->_queryProcessor, QueryProcessor::className());
            $this->_queryProcessor->db = $this;
        }
        return $this->_queryProcessor;
    }

    /**
     * Reads the data from data file.
     * @param string $name data file name.
     * @param boolean $refresh whether to reload the data even if it is found in the cache.
     * @return array[] data.
     * @throws InvalidConfigException on failure.
     */
    public function readData($name, $refresh = false)
    {
        if (isset($this->dataCache[$name]) && !$refresh) {
            return $this->dataCache[$name];
        }
        $fileName = $this->composeFullFileName($name);
        $rawData = require $fileName;
        if (!is_array($rawData)) {
            throw new InvalidConfigException("File '{$fileName}' should return an array.");
        }
        $data = [];
        foreach ($rawData as $key => $value) {
            if (isset($value[$this->primaryKeyName])) {
                $pk = $value[$this->primaryKeyName];
            } else {
                $pk = $key;
                $value[$this->primaryKeyName] = $pk;
            }
            $data[$pk] = $value;
        }
        if ($this->enableDataCache) {
            $this->dataCache[$name] = $data;
        }
        return $data;
    }

    /**
     * Writes data into data file.
     * @param string $name data file name.
     * @param array[] $data data to be written.
     * @throws Exception on failure.
     */
    public function writeData($name, array $data)
    {
        $fileName = $this->composeFullFileName($name);
        $content = "<?php\n\nreturn " . VarDumper::export($data) . ";";
        $bytesWritten = file_put_contents($fileName, $content);
        if ($bytesWritten <= 0) {
            throw new Exception("Unable to write file '{$fileName}'.");
        }
        unset($this->dataCache[$name]);
    }

    /**
     * Composes data file full name.
     * @param string $name data pack name.
     * @return string data file full name.
     */
    protected function composeFullFileName($name)
    {
        return Yii::getAlias($this->path) . DIRECTORY_SEPARATOR . $name . '.php';
    }
}