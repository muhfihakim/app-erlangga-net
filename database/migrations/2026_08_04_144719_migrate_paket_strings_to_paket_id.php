<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\Paket;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add paket_id to pre_orders, customers, pppoes
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->foreignId('paket_id')->nullable()->constrained('pakets')->onDelete('set null');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->foreignId('paket_id')->nullable()->constrained('pakets')->onDelete('set null');
        });
        Schema::table('pppoes', function (Blueprint $table) {
            $table->foreignId('paket_id')->nullable()->constrained('pakets')->onDelete('set null');
        });

        // Helper to get or create Paket
        $getOrCreatePaket = function ($namaPaket) {
            $paket = DB::table('pakets')->where('nama_paket', $namaPaket)->first();
            if ($paket) {
                return $paket->id;
            }
            return DB::table('pakets')->insertGetId([
                'nama_paket' => $namaPaket,
                'harga' => 0,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        };

        // 1. PreOrders
        $preOrders = DB::table('pre_orders')->get();
        foreach ($preOrders as $po) {
            if (!empty($po->paket_internet)) {
                $paketId = $getOrCreatePaket($po->paket_internet);
                DB::table('pre_orders')->where('id', $po->id)->update(['paket_id' => $paketId]);
            }
        }

        // 2. Customers
        $customers = DB::table('customers')->get();
        foreach ($customers as $c) {
            if (!empty($c->paket_internet)) {
                $paketId = $getOrCreatePaket($c->paket_internet);
                DB::table('customers')->where('id', $c->id)->update(['paket_id' => $paketId]);
            }
        }

        // 3. Pppoes
        $pppoes = DB::table('pppoes')->get();
        foreach ($pppoes as $p) {
            if (!empty($p->profile_paket)) {
                $paketId = $getOrCreatePaket($p->profile_paket);
                DB::table('pppoes')->where('id', $p->id)->update(['paket_id' => $paketId]);
            }
        }

        // Drop old columns
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->dropColumn('paket_internet');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn('paket_internet');
        });
        Schema::table('pppoes', function (Blueprint $table) {
            $table->dropColumn('profile_paket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Re-add columns
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->string('paket_internet')->nullable();
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->string('paket_internet')->nullable();
        });
        Schema::table('pppoes', function (Blueprint $table) {
            $table->string('profile_paket')->nullable();
        });

        // Migrate back
        $pakets = DB::table('pakets')->get();

        $preOrders = DB::table('pre_orders')->get();
        foreach ($preOrders as $po) {
            if ($po->paket_id) {
                $paket = $pakets->firstWhere('id', $po->paket_id);
                if ($paket) {
                    DB::table('pre_orders')->where('id', $po->id)->update(['paket_internet' => $paket->nama_paket]);
                }
            }
        }

        $customers = DB::table('customers')->get();
        foreach ($customers as $c) {
            if ($c->paket_id) {
                $paket = $pakets->firstWhere('id', $c->paket_id);
                if ($paket) {
                    DB::table('customers')->where('id', $c->id)->update(['paket_internet' => $paket->nama_paket]);
                }
            }
        }

        $pppoes = DB::table('pppoes')->get();
        foreach ($pppoes as $p) {
            if ($p->paket_id) {
                $paket = $pakets->firstWhere('id', $p->paket_id);
                if ($paket) {
                    DB::table('pppoes')->where('id', $p->id)->update(['profile_paket' => $paket->nama_paket]);
                }
            }
        }

        // Drop foreign keys and columns
        Schema::table('pre_orders', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);
            $table->dropColumn('paket_id');
        });
        Schema::table('customers', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);
            $table->dropColumn('paket_id');
        });
        Schema::table('pppoes', function (Blueprint $table) {
            $table->dropForeign(['paket_id']);
            $table->dropColumn('paket_id');
        });
    }
};
