<?php

namespace yii2tech\tests\unit\filedb;

use yii2tech\filedb\Connection;
use yii2tech\filedb\QueryProcessor;

class QueryProcessorTest extends TestCase
{
    /**
     * @return QueryProcessor query processor instance.
     */
    protected function createQueryProcessor()
    {
        $db = new Connection();
        return new QueryProcessor(['db' => $db]);
    }

    // Tests :

    public function testApplyOrderBy()
    {
        $queryProcessor = new QueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 10; $i++) {
            $rawData[$i] = [
                'number' => $i,
                'name' => 'name' . $i,
            ];
        }

        $data = $queryProcessor->applyOrderBy($rawData, ['number' => SORT_DESC]);
        $this->assertEquals(10, $data[0]['number']);
        $this->assertEquals(1, $data[9]['number']);

        $data = $queryProcessor->applyOrderBy($rawData, ['name' => SORT_ASC]);
        $this->assertEquals('name1', $data[0]['name']);
        $this->assertEquals('name10', $data[1]['name']);
        $this->assertEquals('name2', $data[2]['name']);
    }

    public function testApplyLimit()
    {
        $queryProcessor = new QueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 10; $i++) {
            $rawData[$i] = [
                'number' => $i
            ];
        }

        $data = $queryProcessor->applyLimit($rawData, 2, null);
        $expectedData = [
            ['number' => 1],
            ['number' => 2],
        ];
        $this->assertEquals($expectedData, $data);

        $data = $queryProcessor->applyLimit($rawData, 2, 5);
        $expectedData = [
            ['number' => 6],
            ['number' => 7],
        ];
        $this->assertEquals($expectedData, $data);

        $data = $queryProcessor->applyLimit($rawData, null, 8);
        $expectedData = [
            ['number' => 9],
            ['number' => 10],
        ];
        $this->assertEquals($expectedData, $data);
    }

    public function testFilterHashCondition()
    {
        $queryProcessor = new QueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 10; $i++) {
            $rawData[$i] = [
                'number' => $i
            ];
        }

        $data = $queryProcessor->filterHashCondition($rawData, ['number' => 5]);
        $this->assertEquals([5 => ['number' => 5]], $data);
    }

    /**
     * @depends testFilterHashCondition
     */
    public function testFilterAndCondition()
    {
        $queryProcessor = new QueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 10; $i++) {
            $rawData[$i] = [
                'number' => $i,
                'name' => 'name' . $i,
            ];
        }

        $data = $queryProcessor->filterAndCondition($rawData, 'AND', [['number' => 5], ['name' => 'name5']]);
        $this->assertEquals([5 => ['number' => 5, 'name' => 'name5']], $data);

        $data = $queryProcessor->filterAndCondition($rawData, 'AND', [['number' => 4], ['name' => 'name5']]);
        $this->assertEmpty($data);
    }

    /**
     * @depends testFilterHashCondition
     */
    public function testFilterOrCondition()
    {
        $queryProcessor = $this->createQueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 10; $i++) {
            $rawData[$i] = [
                'id' => $i,
                'number' => $i,
                'name' => 'name' . $i,
            ];
        }

        $data = $queryProcessor->filterOrCondition($rawData, 'OR', [['number' => 5], ['number' => 4]]);
        $expectedData = [
            5 => $rawData[5],
            4 => $rawData[4],
        ];
        $this->assertEquals($expectedData, $data);
    }

    /**
     * @depends testFilterHashCondition
     */
    public function testFilterNotCondition()
    {
        $queryProcessor = $this->createQueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 2; $i++) {
            $rawData[$i] = [
                'id' => $i,
                'number' => $i,
                'name' => 'name' . $i,
            ];
        }

        $data = $queryProcessor->filterNotCondition($rawData, 'NOT', [['number' => 1]]);
        $this->assertEquals([2 => $rawData[2]], $data);
    }

    public function testFilterBetweenCondition()
    {
        $queryProcessor = new QueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 10; $i++) {
            $rawData[$i] = [
                'number' => $i,
            ];
        }

        $data = $queryProcessor->filterBetweenCondition($rawData, 'BETWEEN', ['number', 2, 3]);
        $this->assertEquals([2 => $rawData[2], 3 => $rawData[3]], $data);

        $data = $queryProcessor->filterBetweenCondition($rawData, 'NOT BETWEEN', ['number', 2, 9]);
        $this->assertEquals([1 => $rawData[1], 10 => $rawData[10]], $data);
    }

    public function testFilterInCondition()
    {
        $queryProcessor = new QueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 5; $i++) {
            $rawData[$i] = [
                'number' => $i,
            ];
        }

        $data = $queryProcessor->filterInCondition($rawData, 'IN', ['number', [2, 3]]);
        $this->assertEquals([2 => $rawData[2], 3 => $rawData[3]], $data);

        $data = $queryProcessor->filterInCondition($rawData, 'NOT IN', ['number', [2, 3, 4]]);
        $this->assertEquals([1 => $rawData[1], 5 => $rawData[5]], $data);
    }

    public function testFilterLikeCondition()
    {
        $queryProcessor = $this->createQueryProcessor();

        $rawData = [];
        for ($i = 1; $i <= 10; $i++) {
            $rawData[$i] = [
                'id' => $i,
                'number' => $i,
                'name' => 'name' . $i,
            ];
        }

        $data = $queryProcessor->filterLikeCondition($rawData, 'LIKE', ['name', '2']);
        $this->assertEquals([2 => $rawData[2]], $data);

        $data = $queryProcessor->filterLikeCondition($rawData, 'NOT LIKE', ['name', '1']);
        $expectedData = $rawData;
        unset($expectedData[1]);
        unset($expectedData[10]);
        $this->assertEquals($expectedData, $data);

        $data = $queryProcessor->filterLikeCondition($rawData, 'OR LIKE', ['name', ['2', '3']]);
        $this->assertEquals([2 => $rawData[2], 3 => $rawData[3]], $data);

        $data = $queryProcessor->filterLikeCondition($rawData, 'OR LIKE', ['name', ['2', '3']]);
        $this->assertEquals([2 => $rawData[2], 3 => $rawData[3]], $data);

        $data = $queryProcessor->filterLikeCondition($rawData, 'OR NOT LIKE', ['name', ['name', '3']]);
        $expectedData = $rawData;
        unset($expectedData[3]);
        $this->assertEquals($expectedData, $data);
    }

    /**
     * @depends testFilterInCondition
     */
    public function testFilterInConditionArrayField()
    {
        $queryProcessor = new QueryProcessor();

        $rawData = [
            1 => [
                'tags' => ['tag1', 'tag2'],
            ],
            2 => [
                'tags' => ['tag2', 'tag3'],
            ],
            3 => [
                'tags' => ['tag4', 'tag5'],
            ],
        ];

        $data = $queryProcessor->filterInCondition($rawData, 'IN', ['tags', 'tag3']);
        $this->assertEquals([2 => ['tags' => ['tag2', 'tag3']]], $data);

        $data = $queryProcessor->filterInCondition($rawData, 'NOT IN', ['tags', 'tag2']);
        $this->assertEquals([3 => ['tags' => ['tag4', 'tag5']]], $data);
    }
}