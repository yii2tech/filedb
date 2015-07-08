<?php

namespace yii2tech\tests\unit\filedb\data\ar;

class ActiveRecord extends \yii2tech\filedb\ActiveRecord
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