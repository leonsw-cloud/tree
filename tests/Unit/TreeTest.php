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
use Leonsw\Tree\Tree;

/**
 * @internal
 * @coversNothing
 */
class TreeTest extends HttpTestCase
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
        require_once dirname(__DIR__) . '/Model/Tree.php';
        require_once dirname(__DIR__) . '/Model/TreeV1.php';

        //$this->command('migrate', ['--path' => dirname(__DIR__) . '/database/migrations', '--realpath' => 1]);
        //$this->command('db:seed', ['--path' => dirname(__DIR__) . '/database/seeders', '--realpath' => 1]);
    }

    public function tree()
    {
        $data = \LeonswTests\Tree\Model\Tree::select('id', 'parent_id', 'name', 'deep')->get()->toArray();

        $tree = new Tree($data);
        return $tree;
    }

    public function treeV1()
    {
        $data = \LeonswTests\Tree\Model\TreeV1::select('id', 'parent_id', 'name', 'deep')->get()->toArray();

        $tree = new \Leonsw\Tree\V1\Tree($data, ['field' => 'parent_id', 'key' => 'id', 'value' => 'name']);
        return $tree;
    }

    public function tearDown()
    {
        //$this->command('migrate:rollback', ['--step' => 1, '--path' => dirname(__DIR__) . '/database/migrations', '--realpath' => 1]);
    }

    public function te2stTime()
    {
        $treeV1 = $this->treeV1();
        $startAt = microtime(true);
        foreach (range(1, 500) as $index) {
            //$treeV1->all();
            $treeV1->levels();
            //$treeV1->selection('id', 'name')->all();
            //$treeV1->end();
            //$treeV1->except(100);
            //$treeV1->except(14);
            //$a = $treeV1->children(14);
            //$a = $treeV1->paths(19191);
            //dump(count($a));
        }
        dump('tree v1', microtime(true) - $startAt);
        //dump($a);

        $tree = $this->tree();
        $startAt = microtime(true);
        foreach (range(1, 500) as $index) {
            //$tree->spcer()->all();
            $tree->levels();
            //$tree->spcer()->pluck('name', 'id');
            //$tree->ends();
            //$tree->except(14)->all();
            //$a = $tree->children(14)->all();
            $b = $tree->parents(19191)->all();
            //dump(count($a));
        }
        dump('tree v2', microtime(true) - $startAt);
        //dump($b);
    }

    public function testAll()
    {
        $tree = $this->tree();
        $all = $tree->all();
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

        $all = $tree->children(1)->all();

        $this->assertEquals([
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
        ], $all->pluck('id')->toArray());

        $all = $tree->children(5)->all();
        $this->assertEquals([
            16, 17, 18,
        ], $all->pluck('id')->toArray());

        $all = $tree->except(1)->all();

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

        $all = $tree->except(2)->all();

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

        $all = $tree->except(3)->except(2)->all();

        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
        ], $all->pluck('id')->toArray());

        $all = $tree->except(3)->except(2)->except(5)->all();

        $this->assertEquals([
            1,
            4, 13, 14, 15,
            6, 19, 20, 21,
        ], $all->pluck('id')->toArray());

        $all = $tree->parents(20)->all();

        $this->assertEquals([
            1, 6
        ], $all->pluck('id')->toArray());
    }

    public function testLevels()
    {
        $tree = $this->tree();
        $levels = $tree->levels();

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


        $levels = $tree->except(5)->levels();

        $this->assertEquals([
            4, 6
        ], $levels[0]['children']->pluck('id')->toArray());


        $levels = $tree->children(5)->levels();

        $this->assertEquals([
            16, 17 ,18
        ], collect($levels)->pluck('id')->toArray());

        $this->assertArrayNotHasKey('children', $levels[0]);


        $levels = $tree->parents(17)->levels();

        $this->assertArrayHasKey('children', $levels[0]);
        $this->assertEquals([
            1
        ], collect($levels)->pluck('id')->toArray());

        $this->assertArrayNotHasKey('children', $levels[0]['children']->get(0));
        $this->assertEquals([
            5
        ], $levels[0]['children']->pluck('id')->toArray());


        $levels = $tree->parents(17, true)->levels();
        $this->assertArrayNotHasKey('children', $levels[0]['children']->get(0)['children']->get(0));
        $this->assertEquals([
            17
        ], $levels[0]['children']->get(0)['children']->pluck('id')->toArray());

        $levels = $tree->levels(function ($model, $children) {
            if ($children) {
                $model['child'] = collect($children);
            }
            return $model;
        });

        $this->assertIsObject($levels);
        $this->assertEquals(3, count($levels));
        $this->assertArrayHasKey('child', $levels[0]);

        // spcer ...
        $levels = $tree->spcer()->levels();
        $this->assertEquals([
            'id' => 17,
            'parent_id' => 5,
            'name' => '    ├─Name 17',
            'deep' => 3,
        ], $levels->get(0)['children']->get(1)['children']->get(1));
    }

    public function testPluck()
    {
        $tree = $this->tree();
        $pluck = $tree->pluck('id');
        $this->assertEquals($this->treeAllId, $pluck->toArray());


        $pluck = $tree->pluck('name');
        $this->assertEquals($this->treeAllName, $pluck->toArray());


        $pluck = $tree->pluck('name', 'id');
        $this->assertEquals($this->treeAllName, $pluck->values()->toArray());
        $this->assertEquals($this->treeAllId, array_keys($pluck->toArray()));


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

        $pluck = $tree->except(2)->except(1)->pluck('id');

        $this->assertEquals([
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $pluck->toArray());

        $pluck = $tree->except(11)->except(2)->except(1)->except(31)->pluck('id');

        $this->assertEquals([
            3,
            10, 32, 33,
            12, 37, 38, 39,
        ], $pluck->toArray());

        $pluck = $tree->parents(16)->pluck('id');

        $this->assertEquals([
            1, 5
        ], $pluck->toArray());


        // spcer ...
        $pluck = $tree->parents(16)->spcer()->pluck('name');

        $this->assertEquals([
            'Name 1',
            '└─Name 5',
        ], $pluck->toArray());
    }

    public function testEnds()
    {
        $tree = $this->tree();
        $ends = $tree->ends();
        $this->assertEquals([
            13, 14, 15,
            16, 17, 18,
            19, 20, 21,

            22, 23, 24,
            25, 26, 27,
            28, 29, 30,

            31, 32, 33,
            34, 35, 36,
            37, 38, 39,
        ], $ends->pluck('id')->toArray());


        $ends = $tree->children(5)->ends();
        $this->assertEquals([
            16, 17, 18,
        ], $ends->pluck('id')->toArray());


        $ends = $tree->except(5)->ends();
        $this->assertEquals([
            13, 14, 15,
            19, 20, 21,

            22, 23, 24,
            25, 26, 27,
            28, 29, 30,

            31, 32, 33,
            34, 35, 36,
            37, 38, 39,
        ], $ends->pluck('id')->toArray());

        $ends = $tree->except(5)->except(2)->ends();
        $this->assertEquals([
            13, 14, 15,
            19, 20, 21,

            31, 32, 33,
            34, 35, 36,
            37, 38, 39,
        ], $ends->pluck('id')->toArray());

        $ends = $tree->parents(21)->ends();
        $this->assertEquals([
            6
        ], $ends->pluck('id')->toArray());
    }

    public function tes1tAll()
    {
        //dump($this->tree()->except(1)->pluck('name', 'id'));
        //dump($this->tree()->except(1)->pluck('id'));
        //dump($this->tree()->except(1)->all());
        //dump($this->tree()->except(1)->levels());


        //$this->assertEquals($treev1All, $treeAll);
        //$this->assertEquals($treev1Levels, $treeLevels);
        //$this->assertEquals($treev1Selection, $treeSelection);
        //$this->assertEquals($tree->spcer()->all(), $tree->spcer()->all());
        //$this->assertEquals($tree->spcer()->levels(), $tree->spcer()->levels());
        //$this->assertEquals($tree->spcer()->pluck('name', 'id'), $tree->spcer()->pluck('name', 'id'));

        //dump($this->tree()->children(1)->all());
        //dump($this->tree()->children(1)->pluck('id'));
        //dump($this->tree()->children(1)->pluck('name', 'id'));

        //dump($this->tree()->except(5)->levels());
        //dump($this->tree()->children(1)->levels());

        //dump($this->tree()->parents(5)->all());
        //dump($this->tree()->parents(5)->levels());
        //dump($this->tree()->parents(5)->pluck('id'));
        //dump($this->tree()->parents(5)->pluck('name', 'id'));


        // 不能使用连缀 必须获取所有 无法选择相应节点
        //dump(count($this->tree()->ends()));
        //
        //
        //dump(count($this->tree()->ends()));

        //dump(count($this->treeV1()->end()));


        //dump($this->treeV1()->paths(5));

        //dump($this->treeV1()->children(5));
        //dump($this->treeV1()->except(1));
        //dump($this->treeV1()->levels(null));

        // 考虑使用 parents
        //dump($this->treeV1()->paths(16));

        // 考虑使用 last
        //dump($this->treeV1()->end());

        //dump($this->treeV1()->selection('id', 'name')->all());

        // 递归优化


        // 考虑和 selection 合并为 pluck()
        // 返回集合可以不用处理
        // 最后使用上下文生成
        // $this->generate($this->context, $id)
        //  children()->pluck('id')
        //  except()->pluck('id', 'name')

        // dump($this->tree()->range()->children(5));

        // spcer 的问题
        // generate 前处理 spcer

        //dd($this->tree()->all());

        // 过度 1
        // except
        // children()
        // paths()
        // end()

        // 过度 2
        // spcer()


        // 最后一级
        // 使用集合考虑 直接返回对象
        // 外部 pluck(), 也可用于 resource

        // pluck()
        // all()
        // levels()


    }
}

