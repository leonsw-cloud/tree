<?php
namespace Leonsw\Trees;

use App\Model\Menu;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

trait TreeTrait
{
    public $field = 'parent_id';

    public $key = 'id';

    public $value = 'name';

    public $sortField = 'sort';

    protected $deleteChildren = false;

    public function scopeTree($query, ?int $deep = null)
    {
        if ($deep) {
            $query->where('deep', '<=', $deep);
        }
        $query = $query->orderBy($this->field,  'ASC')
            ->orderBy($this->sortField, 'ASC')
            ->orderBy($this->key,  'ASC');

        $tree =  new Tree([
            'models' => $query->get(),
            'field' => $this->field,
            'key' => $this->key,
            'value' => $this->value
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
        $deep = static::select('deep')->where(['id' => $this->parent_id])->value('deep');

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
        $data = ['parent_id' => $this->parent_id];
        $rules = [];
        if ($this->exists) {
            $rules[] = Rule::notIn([$this->id] + static::tree()->range()->children($this->id));
        }
        if ($this->parent_id) {
            $rules[] = Rule::exists($this->table)->where('id', $this->parent_id);
        }

        if ($rules) {
            $validator = Validator::make($data, [
                'parent_id' => $rules
            ]);
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
            $exist = static::where(['parent_id' => $this->id])->exists();
            if ($exist) {
                // need delete children
                throw new \RuntimeException('Please delete children or move children.');
            }
        }
    }
}
