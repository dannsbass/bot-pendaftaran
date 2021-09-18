<?php
/**
 * Nama bot: Bot Pendaftaran
 * Deskripsi: bot sederhana untuk membantu proses pendaftaran (pengganti formulir konvensional)
 * Developer: Danns Bass
 * Email: dannsbass@gmail.com
 * Versi: 1.0
 * Rilis: 2021-09-17 23:04
 */

class Dannsbass {
  public static function file_user(){
    $folder = "storage/shared/clients";
    if(!file_exists($folder) or !is_dir($folder)){
      mkdir('storage');
      mkdir('storage/shared');
      mkdir($folder);
    }
    $message = Bot::message();
    $chat_id = $message['from']['id'];
    $file_user = "$folder/file$chat_id";
    return $file_user;
  }
  
  public static function file_data(){
    $file_user = self::file_user();
    $file_data = $file_user.'data';
    return $file_data;
  }

  public static function kirim_kalimat_sambutan($text){
    // ambil properti pesan
    $message = Bot::message();
    $nama = $message['from']['first_name'];
    $text = str_replace('<NAMA>',$nama,$text);
    # opsional
    $options = [
        'parse_mode' => 'html',
        'reply' => true,
    ];
    # kirim pesan balasan
    return Bot::sendMessage($text,$options);
  }
  
  public static function ambil($file_data,$kunci){
    $data = explode(":::",file_get_contents($file_data));
    $masukan = '';
    foreach($kunci as $ke=>$value){
        if($ke==0)continue; // ke-0 adalah /daftar
        $isi = $data[$ke];
        $masukan .= "$value: $isi\n";
    }
    return $masukan;
  }

  public static function proses_pendaftaran($formulir){
    $kunci = array_keys($formulir);
    # simpan jawaban user
    $file = self::file_user();
    $file_data = $file.'data';
    # hapus file data kalau sudah ada sebelumnya
    if(file_exists($file_data)) unlink($file_data);
    self::bikin_file($file,$kunci[1]); # menyimpan session
    return Bot::sendMessage($formulir[$kunci[0]]);
  }
  
  public static function proses_teks($file,$formulir){
    $kunci = array_keys($formulir);
    $daftar = $kunci[0];
    if(!file_exists($file)) return Bot::sendMessage("Kirim $daftar dulu");
    $isi_file = file_get_contents($file);
    $message = Bot::message();
    $pesan = $message['text'];
    $user = $message['from']['first_name'];
    $file = self::file_user();
    $file_data = $file.'data';
    $no = 0;
    $hasil_cek = self::cek($pesan,$isi_file);
    foreach($formulir as $key=>$value){
      $no++;
      if($isi_file == $key){
        #cek validitas pesan 
        if($hasil_cek === false) break;
        #kalau valid 
        self::isi_file($file_data,":::$pesan");
        # inputan user
        $value = str_replace(['<NAMA>','<NO HP>','<ALAMAT>','<EMAIL>'],$pesan,$value); 
        Bot::sendMessage($value);
        if($no<count($kunci)){
            $next = $kunci[$no];
            self::bikin_file($file,$next);
        }else{
            unlink($file);
        }
      }
    }
    if($hasil_cek === false) return;
    $akhir = count($kunci)-1;
    $akhir = $kunci[$akhir];
    if($isi_file == $akhir){
        $data = explode(":::",file_get_contents($file_data));
        $teks = "Berikut ini data anda:\n\n";
        $masukan = self::ambil($file_data,$kunci);
        $teks .= $masukan;
        $teks .= "\nApakah data anda sudah benar?";
        $keyboard[] = [
            ['text' => 'BENAR', 'callback_data' => 'BENAR'],
            ['text' => 'SALAH', 'callback_data' => 'SALAH']
        ];
        $options = ['reply_markup' => ['inline_keyboard' => $keyboard]];
        # balas ke user
        Bot::sendMessage($teks,$options);
        # laporkan ke kamu
        $id_kamu = 685631733; #sesuaikan
        Bot::send('sendMessage',['chat_id'=>$id_kamu,'text'=>"Member baru:\n$masukan"]);
        if(file_exists($file))unlink($file);
    }
  }

  public static function proses_pilihan($formulir){
    $message = Bot::message();
    $data = $message['data'];
    $text = $message['message']['text'];
    $message_id = $message['message']['message_id'];

    # jika user memilih BENAR
    if($data == 'BENAR'){
        $keyboard[] = [
            ['text' => 'Gabung ke Grup', 'url' => 'https://t.me/tmuxid']
        ];
        $options = [
            'text' => $text,
            'message_id' => $message_id,
            'reply_markup' => ['inline_keyboard' => $keyboard]
        ];
        Bot::editMessageText($options);
    }else{
        # jika user memilih SALAH
        $kunci = array_keys($formulir);
        $file = self::file_user();
        # hapus session sebelumnya
        if(file_exists($file.'data')) unlink($file.'data');
        # suruh kirim nama 
        // $message = Bot::message();
        // $message_id = $message['message']['message_id'];
        $options = [
            'text' => $formulir['/daftar'],
            'message_id' => $message_id
        ];
        Bot::editMessageText($options);
        # simpan berkas
        return self::bikin_file($file,$kunci[1]); # simpan session baru
    } 
    
  }

  public static function cek($pesan,$isi_file){
    if($isi_file == 'Nama' and preg_match('/([^a-zA-Z\s\']+)/',$pesan)){
      # nama harus betul
      Bot::sendMessage("Nama kamu kayaknya salah. Coba ulangi lagi, kirim nama yang benar.");
      return false;
    }

    if($isi_file == 'No HP' and preg_match('/([^\d]+)/',$pesan)){
      # no hp harus betul
      Bot::sendMessage("Ulangi lagi. Kirim angkanya saja, contoh 08123456789");
      return false;
    }
    
    if($isi_file == 'Alamat'){
      # alamat harus betul
    }
      
    if($isi_file == 'Email' and filter_var($pesan, FILTER_VALIDATE_EMAIL) === FALSE){
      Bot::sendMessage("Email kamu gak valid nih. Coba kirim lagi yang valid ya..");
      return false;
    }

    return true;
  }

  public static function bikin_file($namafile,$konten){
    $f = fopen($namafile,'w');
    fwrite($f,$konten);
    fclose($f);
  }

  public static function isi_file($namafile,$konten){
    $f = fopen($namafile,'a');
    fwrite($f,$konten);
    fclose($f);
  }

}
require_once __DIR__.'/PHPTelebot.php';