<?php

namespace yii2tech\tests\unit\filedb\data\ar;

/**
 * Customer
 *
 * @property integer $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property integer $statusId
 */
class Customer extends ActiveRecord
{
    public function getStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'statusId']);
    }
}