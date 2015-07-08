<?php

namespace yii2tech\tests\unit\filedb;

use yii2tech\filedb\Connection;
use yii2tech\tests\unit\filedb\data\ar\ActiveRecord;
use yii2tech\tests\unit\filedb\data\ar\Customer;
use yii2tech\tests\unit\filedb\data\ar\Status;

class ActiveRelationTest extends TestCase
{
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

        $statuses = [];
        for ($i = 1; $i <= 5; $i++) {
            $statuses[] = [
                'id' => $i,
                'name' => 'name' . $i,
                'rating' => $i,
            ];
        }
        $db->writeData(Status::fileName(), $statuses);

        $customers = [];
        for ($i = 1; $i <= 10; $i++) {
            $customers[] = [
                'id' => $i,
                'name' => 'name' . $i,
                'email' => 'email' . $i,
                'address' => 'address' . $i,
                'statusId' => ($i % 5) + 1,
            ];
        }
        $db->writeData(Customer::fileName(), $customers);
    }

    // Tests :

    public function testFindLazy()
    {
        /* @var $customer Customer */
        $customer = Customer::findOne(['id' => 2]);
        $this->assertFalse($customer->isRelationPopulated('status'));
        $status = $customer->status;

        $this->assertTrue($customer->isRelationPopulated('status'));
        $this->assertTrue($status instanceof Status);
        $this->assertEquals((string) $status->id, (string) $customer->statusId);
        $this->assertEquals(1, count($customer->relatedRecords));

        /* @var $status Customer */
        $status = Status::findOne(['id' => 2]);
        $this->assertFalse($status->isRelationPopulated('customers'));
        $customers = $status->customers;
        $this->assertTrue($status->isRelationPopulated('customers'));
        $this->assertTrue($customers[0] instanceof Customer);
        $this->assertEquals((string) $status->id, (string) $customers[0]->statusId);
    }

    public function testFindEager()
    {
        /* @var $customers Customer[] */
        $customers = Customer::find()->with('status')->all();
        $this->assertCount(10, $customers);
        $this->assertTrue($customers[0]->isRelationPopulated('status'));
        $this->assertTrue($customers[1]->isRelationPopulated('status'));
        $this->assertTrue($customers[0]->status instanceof Status);
        $this->assertEquals((string) $customers[0]->status->id, (string) $customers[0]->statusId);
        $this->assertTrue($customers[1]->status instanceof Status);
        $this->assertEquals((string) $customers[1]->status->id, (string) $customers[1]->statusId);

        /* @var $statuses Customer[] */
        $statuses = Status::find()->with('customers')->all();
        $this->assertCount(5, $statuses);
        $this->assertTrue($statuses[0]->isRelationPopulated('customers'));
        $this->assertTrue($statuses[1]->isRelationPopulated('customers'));
        $this->assertNotEmpty($statuses[0]->customers);
        $this->assertTrue($statuses[0]->customers[0] instanceof Customer);
        $this->assertEquals((string) $statuses[0]->id, (string) $statuses[0]->customers[0]->statusId);
    }
}