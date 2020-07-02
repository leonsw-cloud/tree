<?php

namespace Leonsw\Trees;


use Illuminate\Support\Arr;

/**
 * Tree
 *
 * @author Kids Return <390391483@qq.com>
 */
class Tree
{

    public $field;

    public $key;

    public $value;

    public $models;

    public $modelClass;

    protected $selection = false;

    protected $range = false;

    protected $spcer = true;

    public function __construct(array $config = [])
    {
        foreach ($config as $key => $value) {
            $this->{$key} = $value;
        }
    }

    /**
     * 分组待处理
     * @param number $id
     * @param string $children
     * @return unknown
     */
    public function group($id = 0, $children = false)
    {
        $models = [];
        foreach ($this->models as $key => $model) {
            if ($id == $model[$this->key]) continue;
            $models[$model[$this->field]][$model[$this->key]] = $model;
        }
        return $this->spcerInternal($models);
    }

    /**
     * 获取全部的树
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
     * });
     * @param number $id
     */
    public function levels($fun = null, $id = 0)
    {
        $data = $this->group($id);
        return $this->levelsInternal($fun, $data);
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

    /**
     * 子节点
     * @param number $id
     */
    public function children($id = 0)
    {
        $models = $this->generate($id, true);
        return $this->format($models);
    }

    /**
     * 排除当前ID及子节点
     *
     * @param number $id
     * @return Array $models
     */
    public function except($id = 0)
    {
        $models = $this->generate($id, false);
        return $this->format($models);
    }


    /**
     * 最后一级
     */
    public function end()
    {
        $list = [];
        $this->spcer = false;
        $models = $this->group(0);
        foreach ($models as $key => $value) {
            foreach ($value as $model) {
                if (!isset($models[$model[$this->key]])) {
                    $list[] = $model;
                }
            }
        }
        return $list;
    }


    /**
     * 获取当前节点的路径数组 一般可以用于 breadcrumbs
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

    public $i = 0;
    protected function pathInternal($id, $models)
    {
        $list = [];
        foreach ($models as $key => $value) {
            foreach ($value as $model) {
                if($id == $model[$this->key]) {
                    $list[] = $model;
                    $list = array_merge($list, $this->pathInternal($model[$this->field], $models));
                    break 2;
                }
            }
        }
        return $list;
    }

    /**
     * key => value 选择列表  selection()->all()
     *
     * @param number $id
     * @param Array $params
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
     * key => key 列表 model range rule  range()->all() | ->children() | ->except()
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
     * set spcer  tree()->spcer()
     * @param string $spcer
     * @return \leonsw\tree\Tree
     */
    public function spcer($spcer = true)
    {
        $this->spcer = $spcer;
        return $this;
    }

    protected function spcerInternal($models)
    {
        if (!$this->spcer) return $models;
        foreach ($models as $key => $model) {
            $lastModel = end($model);
            if (1 != $lastModel['deep']) {
                $nbsp =  str_repeat(' ', ($lastModel['deep'] - 2) * 4);
                $model = array_map(function($item) use ($lastModel, $nbsp) {
                    if($lastModel[$this->key] === $item[$this->key]) {
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
        if ($this->selection) {
            $models = Arr::pluck($models, $this->value, $this->key);
        } elseif ($this->range) {
            $models = Arr::pluck($models, $this->key, $this->key);
        }
        return $models;
    }

    /**
     * 生成标准树
     * @param number $id
     * @param string $children
     * @return unknown
     */
    public function generate($id = 0, $children = false)
    {
        $data = $this->group($id, $children);
        if (!$id || !$children) $id = 0;
        return $this->generateInternal($data, $id);
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
