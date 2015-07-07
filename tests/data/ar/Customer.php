<?php

namespace yii2tech\tests\unit\staticdb\data\ar;

class Customer extends ActiveRecord
{
    public function getStatus()
    {
        return $this->hasOne(Status::className(), ['id' => 'statusId']);
    }
}