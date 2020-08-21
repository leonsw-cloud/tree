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
namespace Leonsw\Tree\V1;

/**
 * Tree.
 */
class Tree
{
    public $field;

    public $key;

    public $value;

    public $i = 0;

    protected $models;

    protected $selection = false;

    protected $range = false;

    protected $spcer = true;

    public function __construct($models, array $config = [])
    {
        $this->models = $models;
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * 分组待处理.
     * @param number $id
     * @param string $children
     * @return unknown
     */
    public function group($id = 0, $children = false)
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
        return $this->format($this->generate());
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
     * @param null|mixed $fun
     */
    public function levels($fun = null, $id = 0)
    {
        $this->spcer = false;
        if ($fun === null) {
            $fun = function ($model, $children) {
                if ($children) {
                    $model['children'] = collect($children);
                }
                return $model;
            };
        }
        $data = $this->group($id);
        return $this->levelsInternal($fun, $data);
    }

    /**
     * 子节点.
     * @param number $id
     */
    public function children($id = 0)
    {
        $models = $this->generate($id, true);
        return $this->format($models);
    }

    /**
     * 排除当前ID及子节点.
     *
     * @param number $id
     * @return array $models
     */
    public function except($id = 0)
    {
        $models = $this->generate($id, false);
        return $this->format($models);
    }

    /**
     * 最后一级.
     */
    public function end()
    {
        $list = [];
        $this->spcer = false;
        $models = $this->group(0);
        foreach ($models as $key => $value) {
            foreach ($value as $model) {
                if (! isset($models[$model[$this->key]])) {
                    $list[] = $model;
                }
            }
        }
        return $list;
    }

    /**
     * 获取当前节点的路径数组 一般可以用于 breadcrumbs.
     * @param $id
     */
    public function paths($id)
    {
        $this->spcer = false;
        $models = $this->group(0);
        $paths = $this->pathInternal($id, $models);
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
    public function selection($key = null, $value = null)
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
     * key => key 列表 model range rule  range()->all() | ->children() | ->except().
     * @param unknown $key
     * @return \leonsw\tree\Tree
     */
    public function range($key = null)
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
    public function spcer($spcer = true)
    {
        $this->spcer = $spcer;
        return $this;
    }

    /**
     * 生成标准树.
     * @param number $id
     * @param string $children
     * @return unknown
     */
    public function generate($id = 0, $children = false)
    {
        $data = $this->group($id, $children);
        if (! $id || ! $children) {
            $id = 0;
        }
        return $this->generateInternal($data, $id);
    }

    protected function levelsInternal($fun, $data, $parentId = 0)
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

    protected function pathInternal($id, $models)
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

    protected function spcerInternal($models)
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

    protected function format($models)
    {
        $models = collect($models)->values();
        if ($this->selection) {
            $models = $models->map(function ($model) {
                return ['id' => $model->{$this->key}, 'value' => $model->{$this->value}];
            });
        } elseif ($this->range) {
            $models->pluck($this->key);
        }
        return $models;
    }

    protected function generateInternal($data, $parentId = 0, $deep = 1)
    {
        $models = [];
        if (isset($data[$parentId])) {
            foreach ($data[$parentId] as $key => $model) {
                $models[$key] = $model;
                if (isset($data[$model[$this->key]])) {
                    $return = $this->generateInternal($data, $model['id'], $deep + 1);
                    $models = $models + $return;
                }
            }
        }
        return $models;
    }
}