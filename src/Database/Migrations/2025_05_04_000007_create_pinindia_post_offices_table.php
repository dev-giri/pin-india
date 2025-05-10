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
        Schema::create($this->prefix.'post_offices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pincode_id')->constrained($this->prefix.'pincodes');
            $table->string('name')->index();
            $table->string('office');
            $table->string('type')->nullable();
            $table->string('delivery')->nullable();
            $table->double('latitude', 15, 8)->nullable();
            $table->double('longitude', 15, 8)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists($this->prefix.'post_offices');
    }
};
