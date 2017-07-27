<p align="center">
    <a href="https://github.com/yii2tech" target="_blank">
        <img src="https://avatars2.githubusercontent.com/u/12951949" height="100px">
    </a>
    <h1 align="center">File DB Extension for Yii 2</h1>
    <br>
</p>

This extension provides ActiveRecord interface for the data declared in static files.
Such solution allows declaration of static entities like groups, statuses and so on via files, which are
stored under version control instead of database.

> Note: although this extension allows writing of the data, it is not recommended. You should consider
  using regular relational database based on SQLite in case you need sophisticated local data storage.

For license information check the [LICENSE](LICENSE.md)-file.

[![Latest Stable Version](https://poser.pugx.org/yii2tech/filedb/v/stable.png)](https://packagist.org/packages/yii2tech/filedb)
[![Total Downloads](https://poser.pugx.org/yii2tech/filedb/downloads.png)](https://packagist.org/packages/yii2tech/filedb)
[![Build Status](https://travis-ci.org/yii2tech/filedb.svg?branch=master)](https://travis-ci.org/yii2tech/filedb)


Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist yii2tech/filedb
```

or add

```json
"yii2tech/filedb": "*"
```

to the require section of your composer.json.


Usage
-----

This extension works similar to regular Yii2 database access layer.
First of all you should add a [[\yii2tech\filedb\Connection]] component to your application configuration:

```php
return [
    'components' => [
        'filedb' => [
            'class' => 'yii2tech\filedb\Connection',
            'path' => '@app/data/static',
        ],
        // ...
    ],
    // ...
];
```

Now you can declare actual entities and their data via files stored under '@app/data/static' path.
By default regular PHP code files are used for this, but you can choose different format via [[\yii2tech\filedb\Connection::format]].
Each entity should have a file with corresponding name, like 'UserGroup', 'ItemStatus' etc. So full file names for
them would be '/path/to/project/data/static/UserGroup.php', '/path/to/project/data/static/ItemStatus.php' and so on.
Each file should return an array containing actual entity data, for example:

```php
// file 'UserGroup.php'
return [
    [
        'id' => 1,
        'name' => 'admin',
        'description' => 'Site administrator',
    ],
    [
        'id' => 2,
        'name' => 'member',
        'description' => 'Registered front-end user',
    ],
];
```

In file DB each data row should have a unique field, which identifies it - a primary key. Its name is specified
by [[\yii2tech\filedb\Connection::primaryKeyName]].
You may ommit primary key at rows declaration in this case key, under which row is declared in the data array will
be used as primary key value. So previous data file example can be rewritten in following way:

```php
// file 'UserGroup.php'
return [
    1 => [
        'name' => 'admin',
        'description' => 'Site administrator',
    ],
    2 => [
        'name' => 'member',
        'description' => 'Registered front-end user',
    ],
];
```


## Querying Data <span id="querying-data"></span>

You may execute complex query on the data declared in files using [[\yii2tech\filedb\Query]] class.
This class works similar to regular [[\yii\db\Query]] and uses same syntax.
For example:

```php
use yii2tech\filedb\Query;

$query = new Query();
$query->from('UserGroup')
    ->limit(10);
$rows = $query->all();

$query = new Query();
$row = $query->from('UserGroup')
    ->where(['name' => 'admin'])
    ->one();
```


## Using ActiveRecord <span id="using-active-record"></span>

The main purpose of this extension is provide an ActiveRecord interface for the static data.
It is done via [[\yii2tech\filedb\ActiveRecord]] and [[\yii2tech\filedb\ActiveQuery]] classes.
Particular ActiveRecord class should extend [[\yii2tech\filedb\ActiveRecord]] and override its `fileName()` method,
specifying source data file name. For example:

```php
class UserGroup extends \yii2tech\filedb\ActiveRecord
{
    public static function fileName()
    {
        return 'UserGroup';
    }
}
```

> Note: by default `fileName()` returns own class base name (without namespace), so if you declare source data
  file with name equal to the class base name, you can ommit `fileName()` method overriding.

[[\yii2tech\filedb\ActiveRecord]] works similar to regular [[\yii\db\ActiveRecord]], allowing finding, validation
and saving models. It can establish relations to other ActiveRecord classes, which are usually represents entities
from relational database. For example:

```php
class UserGroup extends \yii2tech\filedb\ActiveRecord
{
    public function getUsers()
    {
        return $this->hasMany(User::className(), ['groupId' => 'id']);
    }
}

class User extends \yii\db\ActiveRecord
{
    public function getGroup()
    {
        return $this->hasOne(UserGroup::className(), ['id' => 'groupId']);
    }
}
```

So relational queries can be performed like following:

```php
$users = User::find()->with('group')->all();
foreach ($users as $user) {
    echo 'username: ' . $user->name . "\n";
    echo 'group: ' . $user->group->name . "\n\n";
}

$adminGroup = UserGroup::find()->where(['name' => 'admin'])->one();
foreach ($adminGroup->users as $user) {
    echo $user->name . "\n";
}
```
