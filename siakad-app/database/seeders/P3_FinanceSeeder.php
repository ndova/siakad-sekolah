<?php

namespace Database\Seeders;

use App\Models\FeeType;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Payment;
use App\Models\PaymentMethod;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class P3_FinanceSeeder extends Seeder
{
    public static array $feeTypes = [];

    public function run(): void
    {
        $schoolId = P1_CoreSeeder::$school['id'];
        $bendaharaId = P1_CoreSeeder::$users['bendahara@siakad.test']['id'];

        PaymentMethod::create(['id' => Str::uuid(), 'school_id' => $schoolId, 'code' => 'TRF', 'name' => 'Transfer Bank', 'is_active' => true]);
        PaymentMethod::create(['id' => Str::uuid(), 'school_id' => $schoolId, 'code' => 'TUN', 'name' => 'Tunai', 'is_active' => true]);

        $fees = [
            ['code' => 'SPP', 'name' => 'SPP Bulanan', 'category' => 'spp', 'nominal' => 250000, 'period' => 'bulanan'],
            ['code' => 'DTR', 'name' => 'Daftar Ulang', 'category' => 'daftar_ulang', 'nominal' => 500000, 'period' => 'tahunan'],
            ['code' => 'UJN', 'name' => 'Biaya Ujian', 'category' => 'ujian', 'nominal' => 150000, 'period' => 'semesteran'],
        ];

        foreach ($fees as $f) {
            self::$feeTypes[$f['code']] = FeeType::create([
                'id' => Str::uuid(), 'school_id' => $schoolId, 'code' => $f['code'],
                'name' => $f['name'], 'category' => $f['category'],
                'nominal' => $f['nominal'], 'billing_period' => $f['period'], 'is_active' => true,
            ])->toArray();
        }

        $siswaKeys = ['Andi Pratama', 'Bunga Lestari', 'Cahya Ramadhan'];
        foreach ($siswaKeys as $name) {
            $s = P1_CoreSeeder::$students[$name];
            $total = 250000 + 500000;
            $status = $name === 'Andi Pratama' ? 'partial' : 'unpaid';

            $inv = Invoice::create([
                'id' => Str::uuid(), 'school_id' => $schoolId,
                'invoice_number' => 'INV-' . date('ym') . '-' . str_pad(rand(1, 9999), 4, '0', STR_PAD_LEFT),
                'student_id' => $s['id'], 'academic_year_id' => P1_CoreSeeder::$ta['id'],
                'semester' => '1', 'batch_id' => 'B-' . date('ymd'),
                'status' => $status, 'subtotal' => $total, 'discount' => 0, 'total' => $total,
                'due_date' => now()->addMonth(), 'created_by' => $bendaharaId,
            ]);

            foreach ([self::$feeTypes['SPP'], self::$feeTypes['DTR']] as $ft) {
                InvoiceItem::create([
                    'id' => Str::uuid(), 'invoice_id' => $inv->id, 'fee_type_id' => $ft['id'],
                    'fee_name' => $ft['name'], 'description' => "{$ft['name']} - Semester 1",
                    'quantity' => 1, 'unit_price' => $ft['nominal'], 'subtotal' => $ft['nominal'],
                    'period_month' => now()->month, 'period_year' => now()->year,
                ]);
            }

            if ($name === 'Andi Pratama') {
                Payment::create([
                    'id' => Str::uuid(), 'school_id' => $schoolId,
                    'payment_number' => 'PAY-' . date('ym') . '-0001',
                    'invoice_id' => $inv->id, 'student_id' => $s['id'],
                    'paid_by' => P1_CoreSeeder::$users['ortu.andi@siakad.test']['id'],
                    'payment_channel' => 'transfer', 'amount' => 250000, 'admin_fee' => 0,
                    'status' => 'verified', 'payment_date' => now()->subDays(5),
                    'verified_by' => $bendaharaId, 'verified_at' => now()->subDays(3),
                    'paid_at' => now()->subDays(3),
                ]);
            }
        }

        echo "✅ Finance seeded (fee types, invoices, payments)\n";
    }
}
