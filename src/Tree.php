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

/**
 * Tree.
 */
class Tree
{

    protected $name;

    protected $fk;

    protected $pk;

    protected $group;

    protected $pkFkMap;

    protected $context;

    protected $contextFk;

    public function __construct($models, string $name = 'name', string $fk = 'parent_id', string $pk = 'id')
    {
        $this->fk = $fk;
        $this->pk = $pk;
        $this->name = $name;

        // 不能使用手动排序 会乱续 排的话需要 parent_id sort created_at 一起排
        //array_multisort(array_column($models, $this->fk), SORT_ASC, $models);

        foreach ($models as $model) {
            $fk = $model[$this->fk];
            $pk = $model[$this->pk];
            $this->group[$fk][$pk] = $model;
            $this->context[$fk][$pk] = is_object($model) ? clone $model : $model;
            $this->pkFkMap[$pk] = $fk;
        }
    }

    protected function reset(): void
    {
        $this->context = [];
        foreach ($this->group as $fk => $models) {
            foreach ($models as $pk => $model) {
                $this->context[$fk][$pk] = is_object($model) ? clone $model : $model;
            }
        }
        $this->contextFk = null;
    }

    protected function fk(int $pk): int
    {
        return $this->pkFkMap[$pk] ?? -1;
    }

    /**
     * 生成标准树.
     */
    protected function generate(int $fk = 0): array
    {
        // $data 考虑使用别的形式 $this->tempData
        $models = [];
        if (isset($this->context[$fk])) {
            foreach ($this->context[$fk] as $key => $model) {
                $models[$key] = $model;
                if (isset($this->context[$model[$this->pk]])) {
                    $return = $this->generate($model['id']);
                    $models = $models + $return;
                }
            }
        }
        return $models;
    }

    public function all()
    {
        $models = $this->generate($this->contextFk ?: 0);
        $this->reset();

        return collect($models)->values();
    }

    /**
     * 层级树 levels()
     * levels(function ($model, $children) {
     *     $model['children'] = $children;
     *     return $model;
     * });.
     * @param number $id
     */
    public function levels(Callable $fun = null): object
    {
        // 排除的ID，可能不会使用 考虑 except()->levels()
        // 考虑 children()->levels()
        if ($fun === null) {
            $fun = function ($model, $children) {
                $model['children'] = $children;
                return $model;
            };
        }
        $models = $this->levelsRecursive($fun, $this->contextFk ?: 0);
        $this->reset();
        return collect($models)->values();
    }


    protected function levelsRecursive($fun, int $fk = 0): array
    {
        $models = [];
        if (isset($this->context[$fk])) {
            foreach ($this->context[$fk] as $id => $model) {
                $return = null;
                if (isset($this->context[$id])) {
                    // 存在下级才执行
                    $return = $this->levelsRecursive($fun, $id);
                    $models[] = $fun($model, $return);
                } else {
                    $models[] = $model;
                }
            }
        }
        return $models;
    }

    public function pluck($value, ?string $key = null)
    {
        // 直接使用 pluck 会比较慢一点
        $models = collect($this->generate($this->contextFk ?: 0))->values();
        //$models = $models->map(function ($model) {
        //    return ['id' => $model[$this->key], 'value' => $model[$this->value]];
        //});
        $this->reset();
        return $models->pluck($value, $key);
    }
    /**
     * 子节点.
     * @param number $id
     */
    public function children(int $id = 0): self
    {
        // 从 group 哪里开始处理 ID
        $this->contextFk = $id;
        return $this;
    }


    /**
     * 排除当前ID及子节点.
     *
     * @param number $id
     */
    public function except(int $id = 0): self
    {
        // 考虑使用 $this->tempData
        // 找到当前id 的 parent_id unset($data['parent_id']['id']])

        $fk = $this->fk($id);
        unset($this->context[$id]);
        unset($this->context[$fk][$id]);
        $this->contextFk = null;

        return $this;
    }

    /**
     * 最后一级.
     */
    public function ends(): object
    {
        $fks = [$this->contextFk ?: 0];
        $models = [];
        while (!is_null($fk = array_shift($fks))) {
            foreach ($this->context[$fk] as $id => $model) {
                if (isset($this->context[$id])) {
                    //$models[] = $fun($model, $this->context[$id]);
                    $fks[] = $id;
                } else {
                    $models[] = $model;
                }
            }
        }
        return collect($models);
    }

    /**
     * 获取当前节点的路径数组 一般可以用于 breadcrumbs.
     * @param $id
     */
    public function parents(int $id, bool $self = false): self
    {

        $this->context = [];
        $nextId = $id;
        while (true) {
            $fk = $this->fk($nextId);
            $models = $this->group[$fk] ?? [];
            foreach ($models as $id => $model) {
                if ($id === $nextId) {
                    $this->context[$fk][$id] = $model;
                }
            }
            if ($fk === 0) break;
            $nextId = $fk;
        }
        ksort($this->context);

        // 不包含自己
        if (!$self) {
            array_pop($this->context);
        }

        return $this;
    }

    public function spcer(): self
    {
        foreach ($this->context as $fk => $models) {
            $lastModel = end($models);
            if ($lastModel['deep'] != 1) {
                $nbsp = str_repeat(' ', ($lastModel['deep'] - 2) * 4);
                $models = array_map(function ($model) use ($lastModel, $nbsp) {
                    if ($lastModel[$this->pk] === $model[$this->pk]) {
                        $model[$this->name] = $nbsp . '└─' . $model[$this->name];
                    } else {
                        $model[$this->name] = $nbsp . '├─' . $model[$this->name];
                    }
                    return $model;
                }, $models);
                $this->context[$fk] = $models;
            }
        }
        return $this;
    }
}
