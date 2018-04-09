<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\filedb;

use Yii;
use yii\base\Component;
use yii\db\QueryInterface;
use yii\db\QueryTrait;
use yii\helpers\ArrayHelper;

/**
 * Query represents the data file search inquiry.
 *
 * For example:
 *
 * ```php
 * $query = new Query();
 * // compose the query
 * $query->from('status')
 *     ->where(['type' => 'public'])
 *     ->limit(10);
 * // build and execute the query
 * $rows = $query->all();
 * ```
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class Query extends Component implements QueryInterface
{
    use QueryTrait;

    /**
     * @var string data file name to be selected from.
     * @see from()
     */
    public $from;


    /**
     * Executes the query and returns all results as an array.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return array the query results. If the query results in nothing, an empty array will be returned.
     */
    public function all($db = null)
    {
        $rows = $this->fetchData($db);
        return $this->populate($rows);
    }

    /**
     * Executes the query and returns a single row of result.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `filedb` application component will be used.
     * @return array|bool the first row (in terms of an array) of the query result. False is returned if the query
     * results in nothing.
     */
    public function one($db = null)
    {
        $rows = $this->fetchData($db);
        return empty($rows) ? false : reset($rows);
    }

    /**
     * Returns the number of records.
     * @param string $q the COUNT expression. Defaults to '*'.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `filedb` application component will be used.
     * @return int number of records.
     */
    public function count($q = '*', $db = null)
    {
        $data = $this->fetchData($db);
        return count($data);
    }

    /**
     * Returns a value indicating whether the query result contains any row of data.
     * @param Connection $db the database connection used to execute the query.
     * If this parameter is not given, the `db` application component will be used.
     * @return bool whether the query result contains any row of data.
     */
    public function exists($db = null)
    {
        $data = $this->fetchData($db);
        return !empty($data);
    }

    /**
     * Sets data file name to be selected from.
     * @param string $name data file name.
     * @return $this the query object itself
     */
    public function from($name)
    {
        $this->from = $name;
        return $this;
    }

    /**
     * Fetches data from storage.
     * @param Connection|null $db connection to be used for data fetching.
     * If this parameter is not given, the `filedb` application component will be used.
     * @return array[] fetched data.
     */
    protected function fetchData($db)
    {
        if ($db === null) {
            $db = Yii::$app->get('filedb');
        }

        return $db->getQueryProcessor()->process($this);
    }

    /**
     * Converts the raw query results into the format as specified by this query.
     * This method is internally used to convert the data fetched from database
     * into the format as required by this query.
     * @param array $rows the raw query result from database
     * @return array the converted query result
     */
    public function populate($rows)
    {
        if ($this->indexBy === null) {
            return array_values($rows); // reset storage internal keys
        }
        $result = [];
        foreach ($rows as $row) {
            $result[ArrayHelper::getValue($row, $this->indexBy)] = $row;
        }
        return $result;
    }
}