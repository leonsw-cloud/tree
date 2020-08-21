<?php

declare (strict_types=1);

namespace LeonswTests\Tree\Model;

use Hyperf\Database\Model\SoftDeletes;
use Hyperf\DbConnection\Model\Model;
use Leonsw\Database\Traits\Filter;
use Leonsw\Tree\V1\TreeTrait;

/**
 */
class TreeV1 extends Model
{
    use Filter;
    use TreeTrait;
    //use SoftDeletes;

    protected $table = 'tree';
    protected $dateFormat = 'Y-m-d H:i:s.u';
    protected $fillable = [];
    protected $casts = [];
}
