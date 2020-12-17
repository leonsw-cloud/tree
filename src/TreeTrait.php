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
namespace Leonsw\Tree;

use Hyperf\Database\Model\Builder;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Rule;

/**
 * @method Builder deep(int $deep)
 * @method Tree tree(string $sort = 'sort', string $direction = 'DESC')
 */
trait TreeTrait
{
    public $deleteChildren = false;

    protected $tree = ['name' => 'name', 'pk' => 'id', 'fk' => 'parent_id'];

    public function scopeDeep(Builder $query, int $deep)
    {
        return $query->where('deep', '<=', $deep);
    }

    public function scopeTree(Builder $query, string $sort = 'sort', string $direction = 'DESC')
    {
        $query = $query->orderBy($this->tree['fk'], 'ASC')
            ->orderBy($sort, $direction)
            ->orderBy($this->tree['pk'], 'ASC');

        return new Tree($query->get(), $this->tree['name'], $this->tree['fk'], $this->tree['pk']);
    }

    public function bootTreeTrait()
    {
        $this->onSaving(function ($model) {
            $model->updateValidate();
            $model->updateDeep();
        });

        $this->onDeleting(function ($model) {
            $model->deleteChildren();
        });
    }

    /**
     * Use to loop detected.
     */
    public function updateDeep()
    {
        $fk = $this->getAttribute($this->tree['fk']);
        $pk = $this->getAttribute($this->tree['pk']);
        $deep = static::select('deep')->where($this->tree['pk'], $fk)->value('deep');

        $this->setAttribute('deep', $deep ? $deep + 1 : 1);
        $deep = $this->deep - $this->getOriginal('deep');

        if ($this->exists && $deep) {
            $children = static::tree()->children($pk)->pluck($this->tree['pk']);
            if ($children) {
                $this->whereIn($this->tree['pk'], $children)->increment('deep', $deep);
            }
        }
    }

    public function updateValidate()
    {
        $fk = $this->getAttribute($this->tree['fk']);
        $pk = $this->getAttribute($this->tree['pk']);
        $validatorFactory = ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class);

        $data = [$this->tree['fk'] => $fk];
        $rules = [];
        if ($this->exists) {
            $ids = static::tree()->children($pk)->pluck($this->tree['pk'])->push($pk);
            $rules[] = Rule::notIn($ids);
        }
        if ($fk) {
            $rules[] = Rule::exists($this->table, $this->tree['pk'])->where($this->tree['pk'], $fk);
        }

        if ($rules) {
            $validator = $validatorFactory->make($data, [
                $this->tree['fk'] => $rules,
            ])->validate();
        }
    }

    public function deleteChildren()
    {
        $fk = $this->getAttribute($this->tree['fk']);
        $pk = $this->getAttribute($this->tree['pk']);
        if ($this->deleteChildren) {
            $children = static::tree()->children($pk)->pluck($this->tree['pk']);
            if ($children) {
                static::destroy($children);
            }
        } else {
            $exist = static::where($this->tree['fk'], $pk)->exists();
            if ($exist) {
                // need delete children
                throw new \RuntimeException('Please delete children or move children.');
            }
        }
    }
}
