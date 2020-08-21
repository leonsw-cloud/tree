<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use LeonswTests\Tree\Model\Tree;

class TreeSeeder extends Seeder
{
    protected $i = 0;

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ids = $this->insert(3);
        $ids2 = [];
        foreach ($ids as $id) {
            $ids2 = array_merge($ids2, $this->insert(3, $id));
        }

        foreach ($ids2 as $id) {
            $ids3 = $this->insert(3, $id);
        }
    }

    public function insert(int $count, int $parentId = 0)
    {
        $ids = [];
        foreach (range(1, $count) as $value) {
            $this->i ++;
            $model = Tree::create([
                'parent_id' => $parentId,
                'name' => 'Name ' . $this->i,
            ]);
            $ids[] = (int) $model->id;
        }
        return $ids;
    }
}
