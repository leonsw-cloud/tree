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
        $this->models = $models;

        foreach ($models as $key => $model) {
            $parentId = $model[$this->field];
            $key = $model[$this->key];
            $this->group[$parentId][$key] = $model;
            $this->map[$key] = $parentId;

        }
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    protected function getParentId(int $id): int
    {
        return $this->map[$id];
    }

    public function group2($data)
    {
        $group = [];
        $map = [];
        return $group;
    }

    /**
     * 分组待处理.
     */
    public function group(int $id = 0)
    {
        $models = [];
        foreach ($this->models as $key => $model) {
            if ($id == $model[$this->key]) {
                continue;
            }
            $models[$model[$this->field]][$model[$this->key]] = $model;
        }
        return $this->spcerInternal($models);
    }

    /**
     * 获取全部的树.
     */
    public function all()
    {
        $data = $this->generate($this->group);
        return $this->format($data);
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
    public function levels($fun = null, int $id = 0): array
    {
        // 排除的ID，可能不会使用 考虑 except()->levels()
        // 考虑 children()->levels()
        $this->spcer = false;
        if ($fun === null) {
            $fun = function ($model, $children) {
                if ($children) {
                    $model['children'] = collect($children);
                }
                return $model;
            };
        }
        //$data = $this->group($id);
        $data = $this->group;
        if ($id) {
            // 排除的 ID
            unset($data[$id]);
            unset($data[$this->getParentId($id)][$id]);
        }
        return $this->levelsInternal($fun, $data);
    }

    /**
     * 子节点.
     * @param number $id
     */
    public function children(int $id = 0): array
    {
        // 从 group 哪里开始处理
        $models = $this->generate($this->group, $id);
        return $this->format($models);
    }

    /**
     * 排除当前ID及子节点.
     *
     * @param number $id
     */
    public function except(int $id = 0): array
    {
        $data = $this->group;
        // 考虑使用 $this->tempData
        // 找到当前id 的 parent_id unset($data['parent_id']['id']])

        unset($data[$id]);
        unset($data[$this->getParentId($id)][$id]);
        $models = $this->generate($data);
        return $this->format($models);
    }

    /**
     * 最后一级.
     */
    public function end(): array
    {
        $list = [];
        $this->spcer = false;
        foreach ($this->group as $key => $value) {
            foreach ($value as $model) {
                if (! isset($models[$model[$this->key]])) {
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
    public function paths($id): array
    {
        // 考虑 except()->paths()
        // 考虑 children()->paths()
        $this->spcer = false;
        $paths = $this->pathInternal($id, $this->group);
        // 只处理 group 结构？ 返回 context
        sort($paths);
        return $this->format($paths);
    }

    /**
     * key => value 选择列表  selection()->all().
     *
     * @param number $id
     * @param array $params
     * @param null|mixed $key
     * @param null|mixed $value
     */
    public function selection($key = null, $value = null): self
    {
        $this->selection = true;
        $this->range = false;
        if ($key) {
            $this->key = $key;
        }
        if ($value) {
            $this->value = $value;
        }
        return $this;
    }

    /**
     * 考虑和 selection 合并为 pluck()
     * children()->pluck('id')
     * except()->pluck('id', 'name')
     * key => key 列表 model range rule  range()->all() | ->children() | ->except().
     * @param unknown $key
     * @return \leonsw\tree\Tree
     */
    public function range($key = null): self
    {
        $this->spcer = false;
        $this->range = true;
        $this->selection = false;
        if ($key) {
            $this->key = $key;
        }
        return $this;
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

    /**
     * 生成标准树.
     */
    public function generate(array $data, int $parentId = 0, int $deep = 1): array
    {
        // $data 考虑使用别的形式 $this->tempData
        $models = [];
        if (isset($data[$parentId])) {
            foreach ($data[$parentId] as $key => $model) {
                $models[$key] = $model;
                if (isset($data[$model[$this->key]])) {
                    $return = $this->generate($data, $model['id'], $deep + 1);
                    $models = $models + $return;
                }
            }
        }
        return $models;
    }

    protected function levelsInternal($fun, array $data, int $parentId = 0): array
    {
        $models = [];
        if (isset($data[$parentId])) {
            foreach ($data[$parentId] as $key => $model) {
                $return = null;
                if (isset($data[$model[$this->key]])) {
                    $return = $this->levelsInternal($fun, $data, $model[$this->key]);
                }
                $models[] = $fun($model, $return);
            }
        }
        return $models;
    }

    protected function pathInternal(int $id, array $models): array
    {
        $list = [];
        foreach ($models as $key => $value) {
            foreach ($value as $model) {
                if ($id == $model[$this->key]) {
                    $list[] = $model;
                    $list = array_merge($list, $this->pathInternal($model[$this->field], $models));
                    break 2;
                }
            }
        }
        return $list;
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

    protected function format(array $models): array
    {
        $models = collect($models)->values();
        if ($this->selection) {
            $models = $models->pluck($this->value, $this->key);
        } elseif ($this->range) {
            $models = $models->pluck($this->key);
        }
        return $models->toArray();
    }
}
