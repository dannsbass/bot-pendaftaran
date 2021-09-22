<?php

# contoh draft formulir, silahkan tambahkan sendiri
$draft = [
    'nama' => 'Siapa nama kamu?',
    'email' => 'Kirim email kamu',
    'asal' => 'Kirim provinsi asal kamu',
    'ref' => 'Dari mana kamu dapat info ini?',
];

$pesan = [

  # kalau pendaftaran selesai
  'Pendaftaran selesai. Terima kasih. Data anda telah kami simpan. Untuk mengulangi dari awal, kirim /start',
];

# sesuaikan dengan database anda (mySQL)
$server_mysql = 'localhost';
$username_mysql = 'root';
$password_mysql = 'bismillah';
$nama_database_mysql = 'bot';

# sesuaikan dengan data bot telegram anda
$token_bot_telegram = 'TOKEN_BOT';
$username_bot_telegram = 'USERNAME_BOT';

# nama tabel untuk menyimpan data (bebas pilih nama apa saja)
$nama_tabel = 'bot';

# proses (biarkan ini, jangan diubah)
require_once __DIR__.'/src/Formulir.php';
Formulir::proses($draft,$pesan,$server_mysql,$username_mysql,$password_mysql,$nama_database_mysql,$token_bot_telegram,$username_bot_telegram,$nama_tabel);
