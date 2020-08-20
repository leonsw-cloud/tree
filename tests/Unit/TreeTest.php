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

/**
 * @internal
 * @coversNothing
 */
class TreeTest extends HttpTestCase
{

    public function setUp()
    {
        require_once dirname(__DIR__) . '/Model/Tree.php';
        //$this->command('migrate', ['--path' => dirname(__DIR__) . '/database/migrations', '--realpath' => 1]);
        //sleep(1);
        //$this->command('db:seed', ['--path' => dirname(__DIR__) . '/database/seeders', '--realpath' => 1]);
    }

    public function tearDown()
    {
        //$this->command('migrate:rollback', ['--step' => 1, '--path' => dirname(__DIR__) . '/database/migrations', '--realpath' => 1]);
    }

    public function testIndex()
    {
        $this->assertTrue(true);
    }
}

