<?php
require_once 'Pembayaran.php';
require_once 'Cetak.php';

#Penggunaan Class Diskon dari Extend
class Diskon extends Pembayaran implements Cetak {

    // Diskon otomatis berdasarkan harga tertentu
    private function hitungDiskon(): float {
        if ($this->jumlah >= 850000) {
            return 0.20; // Diskon 20% jika >= 850 ribu
        } elseif ($this->jumlah >= 500000) {
            return 0.10; // Diskon 10% jika >= 500 ribu
        } elseif ($this->jumlah >= 75000) {
            return 0.05; // Diskon 5% jika >= 75 ribu
        }
        return 0; // Tidak ada diskon
    }

    // 1 reference | 0 overrides
    public function prosesPembayaran() {
        if ($this->validasi()) {
            $persen     = $this->hitungDiskon();
            $potongan   = $this->jumlah * $persen;
            $totalBayar = $this->jumlah - $potongan;
            $persenLabel = $persen * 100;
            return "Pembayaran dengan Diskon {$persenLabel}%: Rp {$this->jumlah} - Rp {$potongan} = Rp {$totalBayar}";
        }
        return "Jumlah tidak valid";
    }

    // 1 reference | 0 overrides
    public function cetakStruk() {
        $persen     = $this->hitungDiskon();
        $potongan   = $this->jumlah * $persen;
        $totalBayar = $this->jumlah - $potongan;
        $persenLabel = $persen * 100;
        return "Struk Diskon: Harga Rp {$this->jumlah} | Diskon {$persenLabel}% Rp {$potongan} | Total Rp {$totalBayar}";
    }
}
?>