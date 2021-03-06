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
            $this->pkFkMap[$pk] = $fk;
        }
        $this->context = $this->group;
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
    public function levels(callable $fun = null): object
    {
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

    public function pluck($value, ?string $key = null)
    {
        // 直接使用 pluck 会比较慢一点
        $models = collect($this->generate($this->contextFk ?: 0));
        $this->reset();
        return $models->pluck($value, $key);
    }

    /**
     * 子节点.
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
        if ($id !== 0) {
            $fk = $this->fk($id);
            unset($this->context[$id], $this->context[$fk][$id]);

            $this->contextFk = null;
        }

        return $this;
    }

    /**
     * 最后一级.
     */
    public function ends(): object
    {
        $fks = [$this->contextFk ?: 0];
        $models = [];
        while (! is_null($fk = array_shift($fks))) {
            foreach ($this->context[$fk] as $id => $model) {
                if (isset($this->context[$id])) {
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
        $ids = [$id];
        while (! is_null($nextId = array_shift($ids))) {
            $fk = (int) $this->fk($nextId);
            $models = $this->group[$fk] ?? [];
            foreach ($models as $id => $model) {
                if ($id === $nextId) {
                    $this->context[$fk][$id] = $model;
                }
            }
            if ($fk !== 0) {
                $ids[] = $fk;
            }
        }
        ksort($this->context);

        // 不包含自己
        if (! $self) {
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

    protected function reset(): void
    {
        $this->context = $this->group;
        $this->contextFk = null;
    }

    protected function fk(int $pk): ?int
    {
        return $this->pkFkMap[$pk] ?? null;
    }

    /**
     * 生成标准树.
     */
    protected function generate(int $fk = 0): array
    {
        $models = [];
        if (isset($this->context[$fk])) {
            foreach ($this->context[$fk] as $pk => $model) {
                $models[$pk] = $model;
                if (isset($this->context[$pk])) {
                    $return = $this->generate($pk);
                    $models = $models + $return;
                }
            }
        }
        return $models;
    }

    protected function levelsRecursive($fun, int $fk = 0): array
    {
        $models = [];
        if (isset($this->context[$fk])) {
            foreach ($this->context[$fk] as $pk => $model) {
                $model = is_object($model) ? clone $model : $model;
                if (isset($this->context[$pk])) {
                    // 存在下级才执行
                    $return = $this->levelsRecursive($fun, $pk);
                    $models[] = $fun($model, $return);
                } else {
                    $models[] = $model;
                }
            }
        }
        return $models;
    }
}
