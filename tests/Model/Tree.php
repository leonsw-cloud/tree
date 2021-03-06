<?php

declare(strict_types=1);
/**
 * This file is part of Leonsw.
 *
 * @link     https://leonsw.com
 * @document https://docs.leonsw.com
 * @contact  leonsw.com@gmail.com
 * @license  https://leonsw.com/LICENSE
 */
namespace LeonswTests\Tree\Model;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use Leonsw\Database\Traits\Filter;
use Leonsw\Tree\TreeTrait;

class Tree extends Model
{
    use Filter;
    use TreeTrait;
    //use SoftDeletes;

    protected $table = 'tree';

    protected $dateFormat = 'Y-m-d H:i:s.u';

    protected $fillable = [];

    protected $casts = [];
}
