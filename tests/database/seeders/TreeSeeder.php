<?php

declare(strict_types=1);

use Hyperf\Database\Seeders\Seeder;
use LeonswTests\Tree\Model\Tree;

class TreeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $ids = $this->insert(3, 'Deep 1');
        $ids2 = [];
        foreach ($ids as $id) {
            $ids2 = array_merge($ids2, $this->insert(3, 'Deep 2', $id));
        }

        foreach ($ids2 as $id) {
            $ids3 = $this->insert(3, 'Deep 3', $id);
        }
    }

    public function insert(int $count, string $name, int $parentId = 0)
    {
        $ids = [];
        foreach (range(1, $count) as $value) {
            $model = Tree::create([
                'parent_id' => $parentId,
                'name' => $name . ' _ ' . mt_rand(100000, 999999),
            ]);
            $ids[] = (int) $model->id;
        }
        return $ids;
    }
}
