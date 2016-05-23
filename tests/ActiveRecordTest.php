<?php

namespace yii2tech\tests\unit\filedb;

use yii2tech\filedb\ActiveQuery;
use yii2tech\filedb\Connection;
use yii2tech\tests\unit\filedb\data\ar\ActiveRecord;
use yii2tech\tests\unit\filedb\data\ar\Customer;

class ActiveRecordTest extends TestCase
{
    /**
     * @var array[] list of test rows.
     */
    protected $testRows = [];

    protected function setUp()
    {
        parent::setUp();
        ActiveRecord::$db = $this->getConnection();
        $this->setUpTestRows();
    }

    /**
     * @return Connection connection instance.
     */
    protected function getConnection()
    {
        return new Connection([
            'path' => $this->getTestFilePath()
        ]);
    }

    /**
     * Sets up test rows.
     */
    protected function setUpTestRows()
    {
        $db = $this->getConnection();
        $rows = [];
        for ($i = 1; $i <= 10; $i++) {
            $rows[$i] = [
                'id' => $i,
                'name' => 'name' . $i,
                'email' => 'email' . $i,
                'address' => 'address' . $i,
                'statusId' => $i,
            ];
        }
        $db->writeData('Customer', $rows);
        $this->testRows = $rows;
    }

    // Tests :

    public function testFind()
    {
        // find one
        $result = Customer::find();
        $this->assertTrue($result instanceof ActiveQuery);
        $customer = $result->one();
        $this->assertTrue($customer instanceof Customer);

        // find all
        $customers = Customer::find()->all();
        $this->assertEquals(10, count($customers));
        $this->assertTrue($customers[0] instanceof Customer);
        $this->assertTrue($customers[1] instanceof Customer);

        // find by _id
        $testId = 1;
        $customer = Customer::findOne($testId);
        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals($testId, $customer->id);

        // find by column values
        $customer = Customer::findOne(['name' => 'name5']);
        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals(5, $customer->id);
        $this->assertEquals('name5', $customer->name);
        $customer = Customer::findOne(['name' => 'unexisting name']);
        $this->assertNull($customer);

        // find by attributes
        $customer = Customer::find()->where(['statusId' => 4])->one();
        $this->assertTrue($customer instanceof Customer);
        $this->assertEquals(4, $customer->statusId);

        // find count, sum, average, min, max, distinct
        $this->assertEquals(10, Customer::find()->count());
        $this->assertEquals(1, Customer::find()->where(['statusId' => 2])->count());

        // scope
        //$this->assertEquals(1, Customer::find()->activeOnly()->count());

        // asArray
        $testRow = $this->testRows[2];
        $customer = Customer::find()->where(['id' => 2])->asArray()->one();
        $this->assertEquals($testRow, $customer);

        // indexBy
        $customers = Customer::find()->indexBy('name')->all();
        $this->assertTrue($customers['name1'] instanceof Customer);
        $this->assertTrue($customers['name2'] instanceof Customer);

        // indexBy callable
        $customers = Customer::find()->indexBy(function ($customer) {
            return $customer->statusId . '-' . $customer->statusId;
        })->all();
        $this->assertTrue($customers['1-1'] instanceof Customer);
        $this->assertTrue($customers['2-2'] instanceof Customer);
    }

    public function testInsert()
    {
        $record = new Customer();
        $record->id = 99;
        $record->name = 'new name';
        $record->email = 'new email';
        $record->address = 'new address';
        $record->statusId = 7;

        $this->assertTrue($record->isNewRecord);

        $record->save();

        $this->assertFalse($record->isNewRecord);

        $refreshRecord = Customer::find()->where(['id' => $record->id])->one();
        $this->assertNotEmpty($refreshRecord);
    }

    /**
     * @depends testInsert
     */
    public function testUpdate()
    {
        $record = new Customer;
        $record->id = 99;
        $record->name = 'new name';
        $record->email = 'new email';
        $record->address = 'new address';
        $record->statusId = 7;
        $record->save();

        // save
        $record = Customer::findOne($record->id);
        $this->assertTrue($record instanceof Customer);
        $this->assertEquals(7, $record->statusId);
        $this->assertFalse($record->isNewRecord);

        $record->statusId = 9;
        $record->save();
        $this->assertEquals(9, $record->statusId);
        $this->assertFalse($record->isNewRecord);
        $record2 = Customer::findOne($record->id);
        $this->assertEquals(9, $record2->statusId);
        $this->assertEquals('new name', $record2->name); // @see https://github.com/yii2tech/filedb/pull/2

        // updateAll
        $pk = ['id' => $record->id];
        $ret = Customer::updateAll(['statusId' => 55], $pk);
        $this->assertEquals(1, $ret);
        $record = Customer::findOne($pk);
        $this->assertEquals(55, $record->statusId);
    }

    /**
     * @depends testInsert
     */
    public function testDelete()
    {
        // delete
        $record = new Customer();
        $record->id = 81;
        $record->name = 'new name';
        $record->email = 'new email';
        $record->address = 'new address';
        $record->statusId = 7;
        $record->save();

        $record = Customer::findOne($record->id);
        $record->delete();
        $record = Customer::findOne($record->id);
        $this->assertNull($record);

        // deleteAll
        $record = new Customer();
        $record->id = 82;
        $record->name = 'new name';
        $record->email = 'new email';
        $record->address = 'new address';
        $record->statusId = 7;
        $record->save();

        $ret = Customer::deleteAll(['name' => 'new name']);
        $this->assertEquals(1, $ret);
        $records = Customer::find()->where(['name' => 'new name'])->all();
        $this->assertEquals(0, count($records));
    }

    public function testUpdateAllCounters()
    {
        $this->assertEquals(1, Customer::updateAllCounters(['statusId' => 10], ['statusId' => 10]));

        $record = Customer::findOne(['statusId' => 10]);
        $this->assertNull($record);
    }
}