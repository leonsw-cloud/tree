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
    protected $field;

    protected $key;

    protected $value;

    

    protected $group;

    protected $map;

    protected $context;

    protected $contextParenetId;

    public function __construct(array $models, string $field = 'parent_id', string $key = 'id', string $value = 'name')
    {
        $this->field = $field;
        $this->key = $key;
        $this->value = $value;

        foreach ($models as $key => $model) {
            $parentId = $model[$this->field];
            $key = $model[$this->key];
            $this->group[$parentId][$key] = $model;
            $this->map[$key] = $parentId;
        }
        $this->context = $this->group;
    }

    protected function reset()
    {
        $this->context = $this->group;
        $this->contextParenetId = null;
        $this->spcer = false;
    }

    protected function parentId(int $id): int
    {
        return $this->map[$id];
    }

    public function all(): array
    {
        $data = $this->generate($this->contextParenetId ?: 0);
        $this->reset();
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
    public function levels(Callable $fun = null): array
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
        $data = $this->levelsInternal($fun, $this->contextParenetId ?: 0);
        $this->reset();
        return $data;
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

    public function pluck($value, ?string $key = null): array
    {
        $data = collect($this->generate($this->contextParenetId ?: 0))->values()->pluck($value, $key);
        $this->reset();
        return $data;
    }
    /**
     * 子节点.
     * @param number $id
     */
    public function children(int $id = 0): self
    {
        // 从 group 哪里开始处理 ID
        $this->contextParenetId = $id;
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

        unset($this->context[$id]);
        unset($this->context[$this->parentId($id)][$id]);
        $this->contextParenetId = null;

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
    public function parents(int $id): self
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

    public function spcer(): self
    {
        // 对 context 使用
        foreach ($this->context as $key => $model) {
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
                $this->context[$key] = $model;
            }
        }
        return $this;
    }
}
