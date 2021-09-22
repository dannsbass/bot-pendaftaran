<?php
/**
 * Script bot telegram untuk membantu proses pendaftaran
 * Bot menggunakan MySQL sebagai database dan PHPTelebot sebagai framework
 * Developer: Danns Bass
 * Email: dannsbass@gmail.com
 * Update: 22 Sep 2021
 */
require_once __DIR__.'/src/PHPTelebot.php';
# contoh formulir
$formulir = [
    'nama' => 'Siapa nama anda?',
    'email' => 'Kirim email anda',
    'asal' => 'Kirim asal provinsi anda',
    'ref' => 'Dari mana kamu dapat info ini?',
];
$jumlah_kolom = count($formulir);
$nama_kolom = array_keys($formulir);
$kolom = implode(' VARCHAR(4096),',$nama_kolom).' VARCHAR(4096), id INT(10)';
$nama_tabel = 'bot';
# sesuaikan koneksi ke database (MySQL)
$db = mysqli_connect('localhost','root','bismillah','bot');
# buat tabel untuk menyimpan input user
$db->query("CREATE TABLE IF NOT EXISTS $nama_tabel ($kolom)");
# inisiasi bot telegram
$bot = new PHPTelebot('tokenbot', 'namabot');
# jika user mengirim '/start'
$bot->cmd('/start',function()use($formulir,$nama_kolom,$db,$nama_tabel){
    $msg = Bot::message();
    $id = (int)$msg['from']['id'];
    $teks = $msg['text'];
    $select_id = $db->query("SELECT id FROM $nama_tabel WHERE id = $id");
    $isi_kolom_id = $select_id->num_rows;
    // jika id user sudah tercatat di dalam kolom id
    if($isi_kolom_id > 0){
        foreach($formulir as $kolom=>$pertanyaan){
            // kosongkan datanya
            $db->query("UPDATE $nama_tabel SET $kolom = NULL WHERE id = $id");
        }
        return Bot::sendMessage($formulir[$nama_kolom[0]],['reply'=>true]);
    }
    return Bot::sendMessage("Selamat datang. Untuk mendaftar, kirim /daftar",['reply'=>true]);
});

# jika user mengirim teks
$bot->on('text',function()use($formulir,$db,$nama_kolom,$jumlah_kolom,$nama_tabel){
    $msg = Bot::message();
    $id = (int)$msg['from']['id'];
    $teks = $msg['text'];
    $cek_id = $db->query("SELECT id FROM $nama_tabel WHERE id = $id");
    //jika id user belum masuk kolom
    if($cek_id->num_rows < 1){
        //masukkan id user ke kolom id
        $db->query("INSERT INTO $nama_tabel (id) VALUES ($id)");
        //kirim pertanyaan pertama dalam formulir ke user
        Bot::sendMessage($formulir[$nama_kolom[0]],['reply'=>true]);
    }else{
        //jika id user sudah ada di kolom

        //cek kolom terakhir
        $kolom_terakhir = $nama_kolom[$jumlah_kolom - 1];
        $query_kolom_terakhir = $db->query("SELECT $kolom_terakhir FROM $nama_tabel WHERE id = $id");
        $isi_kolom_terakhir = $query_kolom_terakhir->fetch_assoc();
        //kalau kolom terakhir tidak kosong
        if($isi_kolom_terakhir[$kolom_terakhir] != NULL){
            Bot::sendMessage("Untuk mengulangi dari awal, kirim /start",['reply'=>true]);
        }
        //kalau kolom terakhir masih kosong
        $no = 0;
        foreach($formulir as $kolom=>$pertanyaan){
            //cek kolom
            $cek_isi = $db->query("SELECT $kolom FROM $nama_tabel WHERE id = $id");
            $isi = $cek_isi->fetch_assoc();
            //jika kolom kosong
            if($isi[$kolom] == NULL){
                //masukkan teks user ke dalam kolom
                $update = $db->query("UPDATE $nama_tabel SET $kolom = '$teks' WHERE id = $id");
                //kirim pesan berikutnya dalam formulir ke user
                $nomor = $no + 1;
                $max = $jumlah_kolom;
                if($nomor < $max){
                    Bot::sendMessage($formulir[$nama_kolom[$nomor]],['reply'=>true]);
                    break;
                }else{
                    Bot::sendMessage("Terima kasih",['reply'=>true]);
                }
            }else{
                //jika kolom tidak kosong
            }
            $no++;
        }
    }
});
$bot->run();
