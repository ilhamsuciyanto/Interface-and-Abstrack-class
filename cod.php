<?php
require_once 'Pembayaran.php';
require_once 'Cetak.php';

#Penggunaan Class COD dari Extend
class COD extends Pembayaran implements Cetak {

    // 1 reference | 0 overrides
    public function prosesPembayaran() {
        if ($this->validasi()) {
            return "Pembayaran COD (Cash on Delivery) sebesar Rp {$this->jumlah}";
        }
        return "Jumlah tidak valid";
    }

    // 1 reference | 0 overrides
    public function cetakStruk() {
        return "Struk COD: Rp {$this->jumlah}";
    }
}
?>