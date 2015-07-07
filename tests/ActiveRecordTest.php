<?php

namespace yii2tech\tests\unit\staticdb;

use yii2tech\staticdb\ActiveQuery;
use yii2tech\staticdb\Connection;
use yii2tech\tests\unit\staticdb\data\ar\ActiveRecord;
use yii2tech\tests\unit\staticdb\data\ar\Customer;

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
}