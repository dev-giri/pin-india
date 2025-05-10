<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private $prefix;

    public function __construct()
    {
        $this->prefix = config('pinindia.table_prefix') ? config('pinindia.table_prefix') . '_' : '';
    }

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create($this->prefix.'regions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('circle_id')->constrained($this->prefix.'circles');
            $table->string('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'regions');
    }
};
