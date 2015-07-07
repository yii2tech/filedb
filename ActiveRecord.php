<?php
/**
 * @link https://github.com/yii2tech
 * @copyright Copyright (c) 2015 Yii2tech
 * @license [New BSD License](http://www.opensource.org/licenses/bsd-license.php)
 */

namespace yii2tech\staticdb;

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
     * By default, the "staticdb" application component is used as the connection.
     * You may override this method if you want to use a different connection.
     * @return Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('staticdb');
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
        // TODO: Implement insert() method.
    }


}