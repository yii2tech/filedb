<?php

namespace yii2tech\tests\unit\filedb\data\ar;

class Status extends ActiveRecord
{
    public function getCustomers()
    {
        return $this->hasMany(Customer::class, ['statusId' => 'id']);
    }
}