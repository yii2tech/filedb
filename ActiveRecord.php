<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\filedb;

use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;
use Yii;
use yii\helpers\StringHelper;

/**
 * ActiveRecord
 *
 * @author Paul Klimov <klimov.paul@gmail.com>
 * @since 1.0
 */
class ActiveRecord extends BaseActiveRecord
{
    /**
     * Returns the static DB connection used by this AR class.
     * By default, the "filedb" application component is used as the connection.
     * You may override this method if you want to use a different connection.
     * @return Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('filedb');
    }

    /**
     * Returns the primary key name(s) for this AR class.
     * The default implementation will return ['id'].
     *
     * Note that an array should be returned even when the record only has a single primary key.
     *
     * For the primary key **value** see [[getPrimaryKey()]] instead.
     *
     * @return string[] the primary key name(s) for this AR class.
     */
    public static function primaryKey()
    {
        return ['id'];
    }

    /**
     * @inheritdoc
     * @return ActiveQuery the newly created [[ActiveQuery]] instance.
     */
    public static function find()
    {
        return Yii::createObject(ActiveQuery::className(), [get_called_class()]);
    }

    /**
     * Declares the name of the static data set associated with this AR class.
     * By default this method returns the class name as the data set name.
     * You may override this method if the collection is not named after this convention.
     * @return string|array the collection name.
     */
    public static function dataSetName()
    {
        return StringHelper::basename(get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function attributes()
    {
        static $attributes;
        if ($attributes === null) {
            $rows = static::getDb()->readData(static::dataSetName());
            $attributes = array_keys(reset($rows));
        }
        return $attributes;
    }

    /**
     * Inserts the record into the database using the attribute values of this record.
     *
     * Usage example:
     *
     * ```php
     * $customer = new Customer;
     * $customer->name = $name;
     * $customer->email = $email;
     * $customer->insert();
     * ```
     *
     * @param boolean $runValidation whether to perform validation before saving the record.
     * If the validation fails, the record will not be inserted into the database.
     * @param array $attributes list of attributes that need to be saved. Defaults to null,
     * meaning all attributes that are loaded from DB will be saved.
     * @return boolean whether the attributes are valid and the record is inserted successfully.
     */
    public function insert($runValidation = true, $attributes = null)
    {
        if ($runValidation && !$this->validate($attributes)) {
            return false;
        }
        $result = $this->insertInternal($attributes);

        return $result;
    }

    /**
     * @see ActiveRecord::insert()
     */
    protected function insertInternal($attributes = null)
    {
        if (!$this->beforeSave(true)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $currentAttributes = $this->getAttributes();
            foreach ($this->primaryKey() as $key) {
                if (isset($currentAttributes[$key])) {
                    $values[$key] = $currentAttributes[$key];
                }
            }
        }

        $db = static::getDb();
        $pkName = $db->primaryKeyName;
        if (!isset($values[$pkName])) {
            throw new InvalidConfigException("'" . get_class($this) . "::{$pkName}' must be set.");
        }
        $dataSetName = static::dataSetName();
        $data = $db->readData($dataSetName);
        if (isset($data[$values[$pkName]])) {
            throw new InvalidConfigException("'{$pkName}' value '{$values[$pkName]}' is already taken.");
        }
        $data[$values[$pkName]] = $values;
        $db->writeData($dataSetName, $data);

        $changedAttributes = array_fill_keys(array_keys($values), null);
        $this->setOldAttributes($values);
        $this->afterSave(true, $changedAttributes);

        return true;
    }

    /**
     * @see ActiveRecord::update()
     */
    protected function updateInternal($attributes = null)
    {
        if (!$this->beforeSave(false)) {
            return false;
        }
        $values = $this->getDirtyAttributes($attributes);
        if (empty($values)) {
            $this->afterSave(false, $values);
            return 0;
        }

        $db = static::getDb();
        $pkName = $db->primaryKeyName;
        $attributes = $this->getAttributes();
        if (!isset($attributes[$pkName])) {
            throw new InvalidConfigException("'" . get_class($this) . "::{$pkName}' must be set.");
        }
        $dataSetName = static::dataSetName();
        $data = $db->readData($dataSetName);
        if (!isset($data[$attributes[$pkName]])) {
            throw new InvalidConfigException("'{$pkName}' value '{$values[$pkName]}' does not exist.");
        }
        $data[$attributes[$pkName]] = $values;
        $db->writeData($dataSetName, $data);

        $changedAttributes = [];
        foreach ($values as $name => $value) {
            $changedAttributes[$name] = $this->getOldAttribute($name);
            $this->setOldAttribute($name, $value);
        }
        $this->afterSave(false, $changedAttributes);

        return 1;
    }

    /**
     * @inheritdoc
     */
    public function delete()
    {
        $result = false;
        if ($this->beforeDelete()) {

            $db = static::getDb();
            $pkName = $db->primaryKeyName;
            $attributes = $this->getAttributes();
            if (!isset($attributes[$pkName])) {
                throw new InvalidConfigException("'" . get_class($this) . "::{$pkName}' must be set.");
            }
            $dataSetName = static::dataSetName();
            $data = $db->readData($dataSetName);
            if (!isset($data[$attributes[$pkName]])) {
                return false;
            }
            unset($data[$attributes[$pkName]]);
            $db->writeData($dataSetName, $data);

            $result = 1;

            $this->afterDelete();
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public static function updateAll($attributes, $condition = '')
    {
        $count = 0;
        $records = static::findAll($condition);
        foreach ($records as $record) {
            $record->setAttributes($attributes, false);
            if ($record->save(false)) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public static function deleteAll($condition = null)
    {
        $count = 0;
        $records = static::findAll($condition);
        foreach ($records as $record) {
            $count += $record->delete();
        }
        return $count;
    }

    /**
     * @inheritdoc
     */
    public static function updateAllCounters($counters, $condition = '')
    {
        $count = 0;
        $records = static::findAll($condition);
        foreach ($records as $record) {
            foreach ($counters as $attribute => $increment) {
                $record->$attribute += $increment;
            }
            $record->save(false);
            $count++;
        }
        return $count;
    }

    /**
     * Returns a value indicating whether the given active record is the same as the current one.
     * The comparison is made by comparing the data set names and the primary key values of the two active records.
     * If one of the records [[isNewRecord|is new]] they are also considered not equal.
     * @param ActiveRecord $record record to compare to
     * @return boolean whether the two active records refer to the same row in the same data set.
     */
    public function equals($record)
    {
        if ($this->isNewRecord || $record->isNewRecord) {
            return false;
        }

        return $this->dataSetName() === $record->dataSetName() && (string) $this->getPrimaryKey() === (string) $record->getPrimaryKey();
    }
}