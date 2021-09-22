<?php
/**
 * Script bot telegram untuk membantu proses pendaftaran
 * Bot menggunakan MySQL sebagai database dan PHPTelebot sebagai framework
 * Developer: Danns Bass
 * Email: dannsbass@gmail.com
 * Update: 21 Sep 2021 6:43 pm
 */
require_once __DIR__.'/src/PHPTelebot.php';
# contoh formulir
$formulir = [
    'nama' => 'Siapa nama anda?',
    'alamat' => 'Kirim alamat anda',
    'email' => 'Kirim email anda'
];
$jumlah_kolom = count($formulir);
$nama_kolom = array_keys($formulir);
$kolom = implode(' VARCHAR(4096),',$nama_kolom).' VARCHAR(4096), id INT(10)';
# sesuaikan koneksi ke database (MySQL)
$db = mysqli_connect('localhost','root','bismillah','bot');
# buat tabel untuk menyimpan input user
$db->query("CREATE TABLE IF NOT EXISTS bot ($kolom)");
# inisiasi bot telegram
$bot = new PHPTelebot('1641337330:AAE-qbPA8dMObWhPJV8FINdfWOyRB6UZ9Zg', 'danns4bot');
# jika user mengirim '/start'
$bot->cmd('/start',function()use($formulir,$nama_kolom,$db){
    $msg = Bot::message();
    $id = (int)$msg['from']['id'];
    $teks = $msg['text'];
    $select_id = $db->query("SELECT id FROM bot");
    $isi_kolom_id = $select_id->fetch_assoc();
    $isi_kolom_id = $isi_kolom_id['id'];
    // jika id user sudah tercatat di dalam kolom id
    if($isi_kolom_id == $id){
        foreach($formulir as $kolom=>$pertanyaan){
            // kosongkan datanya
            $db->query("UPDATE bot SET $kolom = NULL WHERE id = $id");
        }
        return Bot::sendMessage($formulir[$nama_kolom[0]]);
    }
    return Bot::sendMessage("Selamat datang. Untuk mendaftar, kirim /daftar");
});

# jika user mengirim teks
$bot->on('text',function()use($formulir,$db,$nama_kolom,$jumlah_kolom){
    $msg = Bot::message();
    $id = (int)$msg['from']['id'];
    $teks = $msg['text'];
    $cek_id = $db->query("SELECT id FROM bot WHERE id = $id");
    //jika id user belum masuk kolom
    if(mysqli_num_rows($cek_id)<1){
        //masukkan id user ke kolom id
        $db->query("INSERT INTO bot (id) VALUES ($id)");
        //kirim pertanyaan pertama dalam formulir ke user
        Bot::sendMessage($formulir[$nama_kolom[0]]);
    }else{
        //jika id user sudah ada di kolom

        //cek kolom terakhir
        $kolom_terakhir = $nama_kolom[$jumlah_kolom - 1];
        $query_kolom_terakhir = $db->query("SELECT $kolom_terakhir FROM bot WHERE id = $id");
        $isi_kolom_terakhir = $query_kolom_terakhir->fetch_assoc();
        //kalau kolom terakhir tidak kosong
        if($isi_kolom_terakhir[$kolom_terakhir] != NULL){
            Bot::sendMessage("Untuk mengulangi dari awal, kirim /start");
        }
        //kalau kolom terakhir masih kosong
        $no = 0;
        foreach($formulir as $kolom=>$pertanyaan){
            //cek kolom
            $cek_isi = $db->query("SELECT $kolom FROM bot WHERE id = $id");
            $isi = $cek_isi->fetch_assoc();
            //jika kolom kosong
            if($isi[$kolom] == NULL){
                //masukkan teks user ke dalam kolom
                $update = $db->query("UPDATE bot SET $kolom = '$teks' WHERE id = $id");
                //kirim pesan berikutnya dalam formulir ke user
                $nomor = $no + 1;
                $max = $jumlah_kolom;
                if($nomor < $max){
                    Bot::sendMessage($formulir[$nama_kolom[$nomor]]);
                    break;
                }else{
                    Bot::sendMessage("Terima kasih");
                }
            }else{
                //jika kolom tidak kosong
            }
            $no++;
        }
    }
});
$bot->run();
