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


    public function setUp()
    {
        require_once dirname(__DIR__) . '/Model/Tree.php';
        require_once dirname(__DIR__) . '/Model/TreeV1.php';

        //$this->command('migrate', ['--path' => dirname(__DIR__) . '/database/migrations', '--realpath' => 1]);
        //sleep(1);
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

    public function testAll()
    {
        //dump($this->tree()->except(1)->pluck('name', 'id'));
        //dump($this->tree()->except(1)->pluck('id'));
        //dump($this->tree()->except(1)->all());
        //dump($this->tree()->except(1)->levels());

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
        dump(count($this->tree()->ends()));


        dump(count($this->tree()->ends()));

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


    //public function testChildren()
    //{
    //
    //}
    //
    //public function testParents()
    //{
    //
    //}
    //
    //public function testExcpt()
    //{
    //
    //}
    //
    //public function testLevel()
    //{
    //
    //}
    //
    //public function testSelection()
    //{
    //
    //}
    //
    //public function testLast()
    //{
    //
    //}
    //
    //public function testFirst()
    //{
    //
    //}
    //
    //public function testRange()
    //{
    //
    //}
    //
    //public function testSpcer()
    //{
    //
    //}
}

