<?php

namespace Tests\Feature;

use App\Models\Member;
use App\Models\Transaction;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Database\Seeders\MemberSeeder;
use Tests\TestCase;

class WalletApiTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Set up aplikasi sebelum setiap test dijalankan.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);
        $this->seed(MemberSeeder::class);
    }

    /** @test */
    public function test_balance_tampil()
    {
        $response = $this->getJson("/api/wallet/MBR-0001/balance");

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'code'    => 200,
                'message' => 'Saldo ditampilkan.',
                'data'    => [
                    'member_code' => 'MBR-0001',
                    'name'        => 'Budi Santoso',
                    'balance'     => 500000.00,
                ],
            ]);
    }

    /** @test */
    public function test_deposit_berhasil()
    {
        // 1. Ambil data member Siti dari database
        $member = Member::where('member_code', 'MBR-0002')->first();

        // 2. Pastikan statusnya 'active' (case-sensitive) sebelum request
        // Ini untuk menjamin method isActive() di Controller return true
        $member->status = 'active';
        $member->save();

        // 3. Eksekusi API
        $response = $this->postJson("/api/wallet/MBR-0002/deposit", [
            'amount'      => 50000,
            'description' => 'Setoran Tunai'
        ]);

        // 4. Assertions
        $response->assertStatus(200);

        // Menggunakan fresh() untuk mengambil nilai saldo terbaru dari DB
        $this->assertEquals(1300000, $member->fresh()->balance);
    }

    /** @test */
    public function test_withdraw_berhasil()
    {
        // Budi Santoso (MBR-0001) saldo awal 500.000
        $response = $this->postJson("/api/wallet/MBR-0001/withdraw", [
            'amount'      => 100000,
            'description' => 'Tarik Tunai ATM'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Withdraw berhasil.']);

        // Verifikasi saldo akhir (500.000 - 100.000)
        $member = Member::where('member_code', 'MBR-0001')->first();
        $this->assertEquals(400000, $member->balance);
    }

    /** @test */
    public function test_withdraw_gagal_saldo_tidak_cukup()
    {
        // Ahmad Fauzi (MBR-0003) saldo awal 0.00
        $response = $this->postJson("/api/wallet/MBR-0003/withdraw", [
            'amount'      => 10000,
            'description' => 'Tarik Tunai'
        ]);

        // Berdasarkan Controller Anda, exception dilempar dan di-catch menjadi status 422
        $response->assertStatus(422)
            ->assertJson(['status' => 'error']);
    }

    /** @test */
    public function test_member_tidak_aktif()
    {
        // Pastikan database bersih dan seeder berjalan
        $response = $this->getJson("/api/wallet/MBR-0004/balance");

        // Jika masih 500, berarti Exception belum di-render ke 403
        $response->assertStatus(403)
            ->assertJson([
                'status' => 'error',
                'message' => 'Member tidak aktif.'
            ]);
    }

    /** @test */
    public function test_history_transaksi_berhasil_dimuat()
    {
        // Buat satu transaksi manual untuk Budi agar history tidak kosong
        $member = Member::where('member_code', 'MBR-0001')->first();
        Transaction::create([
            'reference_number' => 'TXN-TEST-001',
            'member_id'        => $member->id,
            'type'             => 'deposit',
            'amount'           => 10000,
            'balance_before'   => 490000,
            'balance_after'    => 500000,
            'description'      => 'Initial Deposit'
        ]);

        $response = $this->getJson("/api/wallet/MBR-0001/history");

        $response->assertStatus(200)
            ->assertJson([
                'status'  => 'success',
                'message' => 'Histori transaksi ditemukan.'
            ])
            ->assertJsonStructure([
                'data',
                'meta' => ['current_page', 'last_page', 'total']
            ]);
    }
}
