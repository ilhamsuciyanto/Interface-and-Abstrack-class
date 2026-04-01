<?php
require_once 'Pembayaran.php';
require_once 'Cetak.php';

#Penggunaan Class Pajak dari Extend
class Pajak extends Pembayaran implements Cetak {

    private float $persenPajak = 0.11; // PPN 11%

    // 1 reference | 0 overrides
    public function prosesPembayaran() {
        if ($this->validasi()) {
            $totalPajak = $this->jumlah * $this->persenPajak;
            $totalBayar = $this->jumlah + $totalPajak;
            return "Pembayaran dengan Pajak: Rp {$this->jumlah} + Pajak Rp {$totalPajak} = Rp {$totalBayar}";
        }
        return "Jumlah tidak valid";
    }

    // 1 reference | 0 overrides
    public function cetakStruk() {
        $totalPajak = $this->jumlah * $this->persenPajak;
        $totalBayar = $this->jumlah + $totalPajak;
        return "Struk Pajak: Harga Rp {$this->jumlah} | Pajak 11% Rp {$totalPajak} | Total Rp {$totalBayar}";
    }
}
?>