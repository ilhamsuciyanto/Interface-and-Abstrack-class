<?php
require_once 'Pembayaran.php';
require_once 'Cetak.php';

class QRIS extends Pembayaran implements Cetak {

    public function prosesPembayaran() {
        if ($this->validasi()) {
            // Simulasi pembuatan kode QR
            return "QRIS: Kode QR dibuat untuk pembayaran Rp {$this->jumlah}. Menunggu scan...";
        }
        return "Jumlah tidak valid";
    }

    public function cetakStruk() {
        return "Struk QRIS: Pembayaran Rp {$this->jumlah} Berhasil (Digital)";
    }
}
?>