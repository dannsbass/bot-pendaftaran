<?php
require_once __DIR__.'/src/Dannsbass.php';

# masukkan token dan username bot kamu di sini (info hubungi @BotFather)
define('TOKEN_BOT','xxx'); # ganti xxx dengan token botmu
define('USERNAME_BOT','yyy'); # ganti yyy dengan username botmu

$bot = new PHPTelebot(TOKEN_BOT,USERNAME_BOT);

# formulir yang akan diisi oleh user, bisa dimodifikasi
$formulir = [
    "/daftar"   =>  "Silahkan kirim nama lengkap anda",
    "Nama"      =>  "Oke <NAMA>, silahkan tulis nomor HP anda",
    "No HP"     =>  "Oke, sekarang kirim alamat anda",
    "Alamat"    =>  "Oke, sekarang kirim email anda.",
    "Email"     =>  "Sip, pendaftaran selesai."
]; 

# kalau user mengirim pesan '/start' atau menekan tombol START
$bot->cmd('/start',function()use($formulir){
    $daftar = array_keys($formulir)[0];
    $text = "Assalamualaikum <b><NAMA></b>. Selamat datang di komunitas Termux Indonesia. Untuk mendaftar, kirim $daftar";
    return Dannsbass::kirim_kalimat_sambutan($text);
});

# jika user mengirim '/daftar'
$bot->cmd(array_keys($formulir)[0],function()use($formulir){
    return Dannsbass::proses_pendaftaran($formulir);
});

# jika user mengirim data diri
$bot->on('text',function()use($formulir){
    return Dannsbass::proses_teks(Dannsbass::file_user(),$formulir);
});

# jika user memilih BENAR atau SALAH
$bot->on('callback',function()use($formulir){
    return Dannsbass::proses_pilihan($formulir);
});

# kalau user kirim selain teks (misalnya foto, video, stiker, dll)
$bot->on('photo|video|audio|voice|document|sticker|venue|location|inline',function(){
    if(Bot::type() != 'text'){
        return Bot::sendMessage("Kirim teks saja ya");
    }
});

# jalankan bot
$bot->run();
