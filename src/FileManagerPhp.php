<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\filedb;

use yii\base\Exception;
use yii\base\InvalidConfigException;
use yii\helpers\VarDumper;

/**
 * FileManagerPhp performs data storage inside PHP code files.
 *
 * Note: runtime update of the data stored in PHP files may be affected by PHP files require cache
 * in case you are running HHVM or using APC or similar solution.
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class FileManagerPhp extends FileManager
{
    /**
     * @var string data file extension to be used.
     */
    public $fileExtension = 'php';


    /**
     * {@inheritdoc}
     */
    public function readData($fileName)
    {
        $fileName = $this->composeActualFileName($fileName);
        $data = require $fileName;
        if (!is_array($data)) {
            throw new InvalidConfigException("File '{$fileName}' should return an array.");
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function writeData($fileName, array $data)
    {
        $fileName = $this->composeActualFileName($fileName);
        $content = "<?php\n\nreturn " . VarDumper::export($data) . ";";
        $bytesWritten = file_put_contents($fileName, $content);
        if ($bytesWritten <= 0) {
            throw new Exception("Unable to write file '{$fileName}'.");
        }
        $this->invalidateScriptCache($fileName);
    }

    /**
     * Invalidates precompiled script cache (such as OPCache or APC) for the given file.
     * @param string $fileName file name.
     * @since 1.0.1
     */
    protected function invalidateScriptCache($fileName)
    {
        if (function_exists('opcache_invalidate')) {
            opcache_invalidate($fileName, true);
        }
        if (function_exists('apc_delete_file')) {
            @apc_delete_file($fileName);
        }
    }
}