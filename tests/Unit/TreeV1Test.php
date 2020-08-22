<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://doc.hyperf.io
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace LeonswTests\Tree\Unit;

use Leonsw\Tests\HttpTestCase;
use LeonswTests\Tree\Model\TreeV1;

/**
 * @internal
 * @coversNothing
 */
class TreeV1Test extends HttpTestCase
{


    protected $treeAllName = [
        'Name 1',
        'Name 4',
        'Name 13',
        'Name 14',
        'Name 15',
        'Name 5',
        'Name 16',
        'Name 17',
        'Name 18',
        'Name 6',
        'Name 19',
        'Name 20',
        'Name 21',
        'Name 2',
        'Name 7',
        'Name 22',
        'Name 23',
        'Name 24',
        'Name 8',
        'Name 25',
        'Name 26',
        'Name 27',
        'Name 9',
        'Name 28',
        'Name 29',
        'Name 30',
        'Name 3',
        'Name 10',
        'Name 31',
        'Name 32',
        'Name 33',
        'Name 11',
        'Name 34',
        'Name 35',
        'Name 36',
        'Name 12',
        'Name 37',
        'Name 38',
        'Name 39',
    ];

    protected $treeSpcerAllName = [
        'Name 1',
        '├─Name 4',
        '    ├─Name 13',
        '    ├─Name 14',
        '    └─Name 15',
        '├─Name 5',
        '    ├─Name 16',
        '    ├─Name 17',
        '    └─Name 18',
        '└─Name 6',
        '    ├─Name 19',
        '    ├─Name 20',
        '    └─Name 21',
        'Name 2',
        '├─Name 7',
        '    ├─Name 22',
        '    ├─Name 23',
        '    └─Name 24',
        '├─Name 8',
        '    ├─Name 25',
        '    ├─Name 26',
        '    └─Name 27',
        '└─Name 9',
        '    ├─Name 28',
        '    ├─Name 29',
        '    └─Name 30',
        'Name 3',
        '├─Name 10',
        '    ├─Name 31',
        '    ├─Name 32',
        '    └─Name 33',
        '├─Name 11',
        '    ├─Name 34',
        '    ├─Name 35',
        '    └─Name 36',
        '└─Name 12',
        '    ├─Name 37',
        '    ├─Name 38',
        '    └─Name 39',
    ];


    protected $treeAllId = [
        1,
        4, 13, 14, 15,
        5, 16, 17, 18,
        6, 19, 20, 21,
        2,
        7, 22, 23, 24,
        8, 25, 26, 27,
        9, 28, 29, 30,
        3,
        10, 31, 32, 33,
        11, 34, 35, 36,
        12, 37, 38, 39,
    ];

    public function setUp()
    {
        require_once dirname(__DIR__) . '/Model/TreeV1.php';
    }

    public function tree()
    {
        $data = TreeV1::select('id', 'parent_id', 'name', 'deep')->get()->toArray();

        $tree = new \Leonsw\Tree\V1\Tree($data, ['field' => 'parent_id', 'key' => 'id', 'value' => 'name']);
        return $tree;
    }



    public function testAll()
    {
        $tree = $this->tree();
        $all = $tree->spcer(false)->all();

        $this->assertEquals([
            'id' => 1,
            'parent_id' => 0,
            'name' => 'Name 1',
            'deep' => 1,
        ], $all->get(0));


        $this->assertEquals($this->treeAllId, $all->pluck('id')->toArray());
        $this->assertEquals($this->treeAllName, $all->pluck('name')->toArray());

        $all = $tree->spcer()->all();
        $this->assertEquals($this->treeSpcerAllName, $all->pluck('name')->toArray());

        $all = $tree->children(1);

        $this->assertEquals([
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
        ], $all->pluck('id')->toArray());

        $all = $tree->children(5);
        $this->assertEquals([
            16, 17, 18,
        ], $all->pluck('id')->toArray());

        $all = $tree->except(1);

        $this->assertEquals([
            2,
            7, 22, 23, 24,
            8, 25, 26, 27,
            9, 28, 29, 30,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $all->pluck('id')->toArray());

        $all = $tree->except(2);

        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $all->pluck('id')->toArray());

        $all = $tree->except(3);

        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
            2,
            7, 22, 23, 24,
            8, 25, 26, 27,
            9, 28, 29, 30,
        ], $all->pluck('id')->toArray());

        $all = $tree->except(5);

        $this->assertEquals([
            1,
            4, 13, 14, 15,
            6, 19, 20, 21,
            2,
            7, 22, 23, 24,
            8, 25, 26, 27,
            9, 28, 29, 30,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $all->pluck('id')->toArray());

        $all = $tree->paths(20);

        $this->assertEquals([
            1, 6, 20,
        ], $all->pluck('id')->toArray());
    }

    public function testLevels()
    {
        $tree = $this->tree();
        $levels = $tree->levels();
        $levels = collect($levels);

        $this->assertIsObject($levels);

        $this->assertEquals(3, count($levels));
        $this->assertEquals(3, $levels[2]['id']);

        $this->assertArrayHasKey('children', $levels[0]);
        $this->assertEquals(3, count($levels[0]['children']));
        $this->assertEquals(3, $levels[0]['children']->count());
        $this->assertEquals([
            4, 5, 6
        ], $levels[0]['children']->pluck('id')->toArray());


        $this->assertArrayHasKey('children', $levels[0]['children']->get(1));
        $this->assertEquals(3, $levels[0]['children']->get(1)['children']->count()); // 5
        $this->assertEquals([
            16, 17, 18
        ], $levels[0]['children']->get(1)['children']->pluck('id')->toArray());


        $levels = $tree->levels(null, 5);
        $levels = collect($levels);

        $this->assertEquals([
            4, 6
        ], $levels[0]['children']->pluck('id')->toArray());

        $levels = $tree->levels(function ($model, $children) {
            if ($children) {
                $model['child'] = collect($children);
            }
            return $model;
        });

        $levels = collect($levels);

        $this->assertIsObject($levels);
        $this->assertEquals(3, count($levels));
        $this->assertArrayHasKey('child', $levels[0]);
    }

    public function testPluck()
    {
        $tree = $this->tree();
        $pluck = $tree->spcer(false)->selection()->all();
        $this->assertEquals($this->treeAllId, $pluck->pluck('id')->toArray());


        $pluck = $tree->spcer(false)->selection()->all();
        $this->assertEquals($this->treeAllName, $pluck->pluck('value')->toArray());


        $pluck = $tree->spcer(false)->selection()->all();
        $this->assertEquals([
            'id' => 1,
            'value' => 'Name 1'
        ], $pluck->values()->toArray()[0]);

        $this->assertEquals([
            'id' => 39,
            'value' => 'Name 39'
        ], $pluck->values()->toArray()[38]);


        $pluck = $tree->children(2)->pluck('id');

        $this->assertEquals([
            7, 22, 23, 24,
            8, 25, 26, 27,
            9, 28, 29, 30,
        ], $pluck->toArray());

        $pluck = $tree->children(8)->pluck('id');
        $this->assertEquals([
            25, 26, 27,
        ], $pluck->toArray());

        $pluck = $tree->except(3)->pluck('id');

        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
            2,
            7, 22, 23, 24,
            8, 25, 26, 27,
            9, 28, 29, 30,
        ], $pluck->toArray());

        $pluck = $tree->except(2)->pluck('id');

        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $pluck->toArray());

        $pluck = $tree->paths(16)->pluck('id');

        $this->assertEquals([
            1, 5, 16,
        ], $pluck->toArray());

        // spcer ...
        $pluck = $tree->paths(16)->pluck('value');

        $this->assertEquals([
            'Name 1',
            'Name 5',
            'Name 16',
        ], $pluck->toArray());
    }
}

