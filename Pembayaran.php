<?php

abstract class Pembayaran {
    protected $jumlah;

public function __construct($jumlah) {
    $this->jumlah = $jumlah;
    }


abstract public function prosesPembayaran();

public function validasi() {
    return $this->jumlah >0;
    }
}
?>