<?php

namespace Leonsw\Trees;

use App\Model\Menu;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Event\ListenerProvider;
use Hyperf\Utils\ApplicationContext;
use Hyperf\Validation\Contract\ValidatorFactoryInterface;
use Hyperf\Validation\Rule;
use Hyperf\Validation\ValidatorFactory;


/**
 * @method Tree tree()
 */
trait TreeTrait
{

    protected $deleteChildren = false;

    protected $treeConfig = ['deep' => null, 'field' => 'parent_id', 'key' => 'id', 'value' => 'name', 'sortField' => 'sort'];

    public function scopeTree($query, array $config = [])
    {
        $this->treeConfig = array_merge($this->treeConfig, $config);
        if ($this->treeConfig['deep']) {
            $query->where('deep', '<=', $this->treeConfig['deep']);
        }
        $query = $query->orderBy($this->treeConfig['field'], 'ASC')
            ->orderBy($this->treeConfig['sortField'], 'ASC')
            ->orderBy($this->treeConfig['key'], 'ASC');

        $tree = new Tree($query->get(), [
            'field' => $this->treeConfig['field'],
            'key' => $this->treeConfig['key'],
            'value' => $this->treeConfig['value']
        ]);
        return $tree;
    }

    public static function bootTreeTrait()
    {
        static::saving(function (self $model) {
            $model->updateValidate();
            $model->updateDeep();
        });

        static::deleting(function (self $model) {
            $model->deleteChildren();
        });
    }


    /**
     * Use to loop detected.
     */
    public function updateDeep()
    {
        $deep = static::select('deep')->where(['id' => $this->{$this->treeConfig['field']}])->value('deep');

        $this->setAttribute('deep', $deep ? $deep + 1 : 1);
        $deep = $this->deep - $this->getOriginal('deep');

        if ($this->exists && $deep) {
            $children = static::tree()->range()->children($this->id);
            if ($children) {
                $this->whereIn('id', $children)->increment('deep', $deep);
            }
        }
    }

    public function updateValidate()
    {

        $validatorFactory = ApplicationContext::getContainer()->get(ValidatorFactoryInterface::class);

        $data = ['parent_id' => $this->{$this->treeConfig['field']}];
        $rules = [];
        if ($this->exists) {
            $rules[] = Rule::notIn(static::tree()->range()->children($this->id)->push($this->id));
        }
        if ($this->{$this->treeConfig['field']}) {
            $rules[] = Rule::exists($this->table, 'id')->where('id', $this->{$this->treeConfig['field']});
        }

        if ($rules) {
            $validator = $validatorFactory->make($data, [
                'parent_id' => $rules
            ])->validate();
        }
    }

    public function deleteChildren()
    {
        if ($this->deleteChildren) {
            $children = static::tree()->range()->children($this->id);
            if ($children) {
                static::destroy($children);
            }
        } else {
            $exist = static::where([$this->treeConfig['field'] => $this->id])->exists();
            if ($exist) {
                // need delete children
                throw new \RuntimeException('Please delete children or move children.');
            }
        }
    }
}
