<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

if (!Schema::hasColumn('orders', 'pos_split_cash')) { 
  Schema::table('orders', function (Blueprint $table) { 
    $table->decimal('pos_split_cash', 13, 6)->nullable(); 
    $table->decimal('pos_split_online', 13, 6)->nullable(); 
  }); 
}

if (!Schema::hasColumn('orders', 'tip_amount')) { 
  Schema::table('orders', function (Blueprint $table) { 
    $table->decimal('tip_amount', 13, 6)->default(0); 
    $table->unsignedBigInteger('tip_employee_id')->nullable(); 
  }); 
}
echo "Columns added.\n";
