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
    public $field = 'parent_id';

    public $key = 'id';

    public $value = 'name';

    public $i = 0;

    protected $models;

    protected $group;

    protected $map;

    protected $context;

    protected $selection = false;

    protected $range = false;

    protected $spcer = true;

    public function __construct(array $models, array $config = [])
    {
        foreach ($models as $key => $model) {
            $parentId = $model[$this->field];
            $key = $model[$this->key];
            $this->group[$parentId][$key] = $model;
            $this->map[$key] = $parentId;

        }
        $this->context = $this->group;
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    protected function getParentId(int $id): int
    {
        return $this->map[$id];
    }

    /**
     * 获取全部的树.
     */
    public function all()
    {
        $data = $this->generate($this->childrenParenetId ?: 0);
        return $data;
    }

    /**
     * 层级树 levels()
     * levels(function ($model, $children) {
     *     if($children) {
     *         $model['children'] = $children;
     *     }
     *     return $model;
     * });.
     * @param number $id
     */
    public function levels($fun = null): array
    {
        // 排除的ID，可能不会使用 考虑 except()->levels()
        // 考虑 children()->levels()
        if ($fun === null) {
            $fun = function ($model, $children) {
                if ($children) {
                    $model['children'] = collect($children);
                }
                return $model;
            };
        }
        return $this->levelsInternal($fun, $this->childrenParenetId ?: 0);
    }


    protected function levelsInternal($fun, int $parentId = 0): array
    {
        $models = [];
        if (isset($this->context[$parentId])) {
            foreach ($this->context[$parentId] as $key => $model) {
                $return = null;
                if (isset($this->context[$model[$this->key]])) {
                    $return = $this->levelsInternal($fun, $model[$this->key]);
                }
                $models[] = $fun($model, $return);
            }
        }
        return $models;
    }

    public function pluck($value, ?string $key = null)
    {
        $data = $this->generate($this->childrenParenetId ?: 0);
        return collect($data)->values()->pluck($value, $key);
    }

    protected $childrenParenetId;
    /**
     * 子节点.
     * @param number $id
     */
    public function children(int $id = 0): self
    {
        // 从 group 哪里开始处理 ID
        $this->childrenParenetId = $id;
        return $this;
        $models = $this->generate($this->group, $id);
        return $this->format($models);
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

        unset($this->context[$id]);
        unset($this->context[$this->getParentId($id)][$id]);
        $this->childrenParenetId = null;

        return $this;
    }

    /**
     * 最后一级.
     */
    public function ends(): array
    {
        $list = [];
        foreach ($this->context as $parentId => $models) {
            foreach ($models as $id => $model) {
                if (! isset($this->context[$id])) {
                    $list[] = $model;
                }
            }
        }
        // 最后一级 上下文结构处理？
        return $list;
    }

    /**
     * 获取当前节点的路径数组 一般可以用于 breadcrumbs.
     * @param $id
     */
    public function parents($id): self
    {
        // 考虑 except()->paths()
        // 考虑 children()->paths()
        //$this->spcer = false;
        $this->parentsInternal($id);
        // 可以考虑用 unset() 删除自己
        sort($this->context);
        return $this;
    }

    protected function parentsInternal(int $id): array
    {
        $this->context = [];
        foreach ($this->group as $parentId => $value) {
            foreach ($value as $modelId => $model) {
                if ($id == $modelId) {
                    $this->context[$parentId][$modelId] = $model;
                    $this->context = array_merge($this->context, $this->parentsInternal($model[$this->field]));
                    break 2;
                }
            }
        }
        return $this->context;
    }

    /**
     * 生成标准树.
     */
    public function generate(int $parentId = 0, int $deep = 1): array
    {
        // $data 考虑使用别的形式 $this->tempData
        $models = [];
        if (isset($this->context[$parentId])) {
            foreach ($this->context[$parentId] as $key => $model) {
                $models[$key] = $model;
                if (isset($this->context[$model[$this->key]])) {
                    $return = $this->generate($model['id'], $deep + 1);
                    $models = $models + $return;
                }
            }
        }
        return $models;
    }


    /**
     * set spcer  tree()->spcer().
     * @param string $spcer
     * @return \leonsw\tree\Tree
     */
    public function spcer($spcer = true): self
    {
        $this->spcer = $spcer;
        return $this;
    }

    protected function spcerInternal(array $models): array
    {
        if (! $this->spcer) {
            return $models;
        }
        foreach ($models as $key => $model) {
            $lastModel = end($model);
            if ($lastModel['deep'] != 1) {
                $nbsp = str_repeat(' ', ($lastModel['deep'] - 2) * 4);
                $model = array_map(function ($item) use ($lastModel, $nbsp) {
                    if ($lastModel[$this->key] === $item[$this->key]) {
                        $item[$this->value] = $nbsp . '└─' . $item[$this->value];
                    } else {
                        $item[$this->value] = $nbsp . '├─' . $item[$this->value];
                    }
                    return $item;
                }, $model);
                $models[$key] = $model;
            }
        }
        return $models;
    }
}
