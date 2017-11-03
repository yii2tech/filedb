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
 * Connection represents data file storage with particular path and format.
 *
 * Application configuration example:
 *
 * ```php
 * return [
 *     'components' => [
 *         'filedb' => [
 *             'class' => 'yii2tech\filedb\Connection',
 *             'path' => '@app/data/static',
 *         ]
 *     ],
 * ];
 * ```
 *
 * @property QueryProcessor|array|string $queryProcessor the query processor object or its configuration.
 * @property FileManager $fileManager the file manager instance. This property is read-only.
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
     * @var string data files format.
     * Format determines, which file manager should be used to read/write data files, using [[fileManagerMap]].
     */
    public $format = 'php';
    /**
     * @var array mapping between data file format (see [[format]]) and file manager classes.
     * The keys of the array are format names while the values the corresponding
     * file manager class name or configuration. Please refer to [[Yii::createObject()]] for
     * details on how to specify a configuration.
     */
    public $fileManagerMap = [
        'php' => 'yii2tech\filedb\FileManagerPhp',
        'json' => 'yii2tech\filedb\FileManagerJson',
    ];
    /**
     * @var string name of the data key, which should be used as row unique id - primary key.
     * If source data holds no corresponding key, the key of the row in source array will be used as its value.
     */
    public $primaryKeyName = 'id';
    /**
     * @var bool whether to cache read data in memory.
     * While enabled this option may speed up program execution, but will cost extra memory usage.
     */
    public $enableDataCache = true;

    /**
     * @var QueryProcessor|array|string the query processor object or its configuration.
     */
    private $_queryProcessor = 'yii2tech\filedb\QueryProcessor';
    /**
     * @var bool whether [[queryProcessor]] has been initialized or not.
     */
    private $isQueryProcessorInitialized = false;
    /**
     * @var FileManager file manager instance.
     */
    private $_fileManager;
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
     * @return FileManager file manager instance.
     * @throws InvalidConfigException on invalid configuration.
     */
    public function getFileManager()
    {
        if (!is_object($this->_fileManager)) {
            if (!isset($this->fileManagerMap[$this->format])) {
                throw new InvalidConfigException("Unsupported format '{$this->format}'.");
            }
            $config = $this->fileManagerMap[$this->format];
            if (!is_array($config)) {
                $config = ['class' => $config];
            }
            $this->_fileManager = Yii::createObject($config);
        }
        return $this->_fileManager;
    }

    /**
     * Reads the data from data file.
     * @param string $name data file name.
     * @param bool $refresh whether to reload the data even if it is found in the cache.
     * @return array[] data.
     * @throws InvalidConfigException on failure.
     */
    public function readData($name, $refresh = false)
    {
        if (isset($this->dataCache[$name]) && !$refresh) {
            return $this->dataCache[$name];
        }
        $rawData = $this->getFileManager()->readData($this->composeFullFileName($name));
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
        $this->getFileManager()->writeData($this->composeFullFileName($name), $data);
        unset($this->dataCache[$name]);
    }

    /**
     * Composes data file name with full path, but without extension.
     * @param string $name data file self name.
     * @return string data file name without extension.
     */
    protected function composeFullFileName($name)
    {
        return Yii::getAlias($this->path) . DIRECTORY_SEPARATOR . $name;
    }
}