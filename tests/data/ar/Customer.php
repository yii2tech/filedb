<?php

namespace yii2tech\tests\unit\filedb\data\ar;

/**
 * Customer
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property string $address
 * @property int $statusId
 */
class Customer extends ActiveRecord
{
    public function getStatus()
    {
        return $this->hasOne(Status::class, ['id' => 'statusId']);
    }
}