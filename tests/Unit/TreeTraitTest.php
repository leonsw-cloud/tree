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
use LeonswTests\Tree\Model\Tree as Model;

/**
 * @internal
 * @coversNothing
 */
class TreeTraitTest extends HttpTestCase
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

        $this->command('migrate', ['--path' => dirname(__DIR__) . '/database/migrations', '--realpath' => 1]);
        $this->command('db:seed', ['--path' => dirname(__DIR__) . '/database/seeders', '--realpath' => 1]);
    }

    public function tearDown()
    {
        $this->command('migrate:rollback', ['--step' => 1, '--path' => dirname(__DIR__) . '/database/migrations', '--realpath' => 1]);
    }


    public function testDeep()
    {
        $query = Model::deep(2)->getQuery();
        $this->assertEquals([
            [
                'type' => 'Basic',
                'column' => 'deep',
                'operator' => '<=',
                'value' => 2,
                'boolean' => 'and',
            ]
        ], $query->wheres);
        $query = Model::deep(1)->getQuery();
        $this->assertEquals([
            [
                'type' => 'Basic',
                'column' => 'deep',
                'operator' => '<=',
                'value' => 1,
                'boolean' => 'and',
            ]
        ], $query->wheres);
    }

    public function testTree()
    {

        $tree = Model::tree();
        $this->assertInstanceOf(Tree::class, $tree);

        $query = Model::deep(5);
        $tree = $query->tree();
        $this->assertInstanceOf(Tree::class, $tree);

        $query = $query->getQuery();
        $this->assertEquals([
            [
                'type' => 'Basic',
                'column' => 'deep',
                'operator' => '<=',
                'value' => 5,
                'boolean' => 'and',
            ]
        ], $query->wheres);


        $this->assertEquals([
            [
                'column' => 'parent_id',
                'direction' => 'asc',
            ],
            [
                'column' => 'sort',
                'direction' => 'desc',
            ],
            [
                'column' => 'id',
                'direction' => 'asc',
            ],
        ], $query->orders);
    }

    public function testUpdate()
    {
        $model = $this->updateParentId(2, 36);
        $tree = Model::tree();
        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18,
            6, 19, 20, 21,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
                        2,
                        7, 22, 23, 24,
                        8, 25, 26, 27,
                        9, 28, 29, 30,
            12, 37, 38, 39,
        ], $tree->pluck('id')->toArray());

        $this->assertEquals(4, $model->deep);
        $this->assertEquals([
            5, 6, 6, 6,
            5, 6, 6, 6,
            5, 6, 6, 6,
        ], $tree->children(2)->pluck('deep')->toArray());


        $model = $this->updateParentId(2, 0);
        $tree = Model::tree();
        $this->assertEquals([
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
        ], $tree->pluck('id')->toArray());

        $this->assertEquals(1, $model->deep);
        $this->assertEquals([
            2, 3, 3, 3,
            2, 3, 3, 3,
            2, 3, 3, 3,
        ], $tree->children(2)->pluck('deep')->toArray());

        $model = $this->updateParentId(4, 16);
        $model = $this->updateParentId(8, 15);
        $tree = Model::tree();

        $this->assertEquals([
            1,
            5, 16,
               4, 13, 14, 15,
                           8, 25, 26, 27,
               17, 18,
            6, 19, 20, 21,
            2,
            7, 22, 23, 24,
            9, 28, 29, 30,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $tree->pluck('id')->toArray());

        $this->assertEquals([
            2, 3, 4, 5, 5, 5, 6, 7, 7, 7,
            3, 3,
            2, 3, 3, 3,
        ], $tree->children(1)->pluck('deep')->toArray());

        $model = $this->updateParentId(4, 1);
        $model = $this->updateParentId(8, 2);


        //
        try {
            $this->updateParentId(1, 1);
        } catch (\Throwable $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
        }

        try {
            $this->updateParentId(1, 5);
        } catch (\Throwable $e) {
            $this->assertEquals('The given data was invalid.', $e->getMessage());
        }

    }

    public function testCreate()
    {
        $model = new Model();
        $model->parent_id = 5;
        $model->name = 'Name 40';
        $model->save();
        $this->assertEquals('3', $model->deep);
        $this->assertEquals('5', $model->parent_id);

        $tree = Model::tree();
        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18, 40,
            6, 19, 20, 21,
            2,
            7, 22, 23, 24,
            8, 25, 26, 27,
            9, 28, 29, 30,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $tree->pluck('id')->toArray());

        $tree = Model::tree();
        $model = new Model();
        $model->parent_id = 40;
        $model->name = 'Name 41';
        $model->save();

        $this->assertEquals('4', $model->deep);
        $this->assertEquals('40', $model->parent_id);

        $tree = Model::tree();
        $this->assertEquals([
            1,
            4, 13, 14, 15,
            5, 16, 17, 18, 40, 41,
            6, 19, 20, 21,
            2,
            7, 22, 23, 24,
            8, 25, 26, 27,
            9, 28, 29, 30,
            3,
            10, 31, 32, 33,
            11, 34, 35, 36,
            12, 37, 38, 39,
        ], $tree->pluck('id')->toArray());
    }

    public function testDelete()
    {
        try {
            $model = Model::find(5);
            $model->delete();
        } catch (\Throwable $e) {
            $this->assertEquals('Please delete children or move children.', $e->getMessage());
        }

        $model = Model::find(5);
        $model->deleteChildren = true;
        $model->delete();

        $tree = Model::tree();
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
        ], $tree->pluck('id')->toArray());
    }

    public function updateParentId($id, $fk)
    {

        $model = Model::find($id);
        $model->parent_id = $fk;
        $model->save();
        return $model;
    }
}

