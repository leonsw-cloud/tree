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
use Hyperf\Database\Migrations\Migration;
use Hyperf\Database\Schema\Blueprint;
use Hyperf\Database\Schema\Schema;

class CreateTreeTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tree', function (Blueprint $table) {
            $table->id();
            $table->fk('parent_id')->default(0);
            $table->string('name');
            $table->sort();
            $table->deep();
            $table->dateTimes();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tree');
    }
}
