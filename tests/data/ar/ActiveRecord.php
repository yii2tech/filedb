<?php

namespace yii2tech\tests\unit\staticdb\data\ar;

class ActiveRecord extends \yii2tech\staticdb\ActiveRecord
{
    public static $db;

    /**
     * @inheritdoc
     */
    public static function getDb()
    {
        return self::$db;
    }
} 