<?php
/**
 * Script bot telegram untuk membantu proses pendaftaran
 * Bot menggunakan MySQL sebagai database dan PHPTelebot sebagai framework
 * Developer: Danns Bass
 * Email: dannsbass@gmail.com
 * Update: 22 Sep 2021
 */
class Formulir{
  
  public static function proses($draft,$pesan,$server_mysql,$username_mysql,$password_mysql,$nama_database_mysql,$token_bot_telegram,$username_bot_telegram,$nama_tabel){
    
    # koneksi ke database
    $db = mysqli_connect($server_mysql,$username_mysql,$password_mysql,$nama_database_mysql);
    
    # inisiasi bot telegram
    $bot = new PHPTelebot($token_bot_telegram,$username_bot_telegram);
    
    # bersihkan nama kolom dari karakter yang tak diinginkan
    foreach ($draft as $kolom => $pertanyaan){
      $formulir[self::sterilkan($kolom)] = $pertanyaan;
    }
    
    $jumlah_kolom = count($formulir);
    
    $nama_kolom = array_keys($formulir);
    
    $kolom = implode(' VARCHAR(4096),',$nama_kolom).' VARCHAR(4096), id INT(10)';
    
    $nama_tabel = self::sterilkan($nama_tabel);
    
    # buat tabel baru kalau belum ada
    $db->query("CREATE TABLE IF NOT EXISTS $nama_tabel ($kolom)");
    
    
    # jika user mengirim '/start'
    $bot->cmd('/start',function()use($formulir,$nama_kolom,$db,$nama_tabel,$pesan){
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
            
            # kirim pesan pertama dari formulir
            return Bot::sendMessage($formulir[$nama_kolom[0]],['reply'=>true]);
        }
        
        # jika id user belum tercatat di kolom id 
        //masukkan id user ke kolom id
        $db->query("INSERT INTO $nama_tabel (id) VALUES ($id)");
            
        //kirim pertanyaan pertama dalam formulir ke user
        return Bot::sendMessage($formulir[$nama_kolom[0]],['reply'=>true]);
    });
    
    # jika user mengirim teks
    $bot->on('text',function()use($formulir,$db,$nama_kolom,$jumlah_kolom,$nama_tabel,$pesan){
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
                Bot::sendMessage($pesan[0],['reply'=>true]);
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
                    
                    # kolom berikutnya
                    $nomor = $no + 1;
                    $max = $jumlah_kolom;
                    
                    # jika isi formulir belum habis
                    if($nomor < $max){
                      # kirim pesan berikutnya dalam formulir ke user
                        Bot::sendMessage($formulir[$nama_kolom[$nomor]],['reply'=>true]);
                        break;
                    }else{
                      # jika isi formulir sudah habis
                        Bot::sendMessage($pesan[0],['reply'=>true]);
                    }
                }else{
                    //jika kolom tidak kosong
                }
                $no++;
            }
        }
    });
    $bot->run();
  }
  
  # output: string yang sudah disterilkan
  private static function sterilkan($nama){
    return preg_replace('/[^0-9a-zA-Z_]+/','_',$nama);
  }
}
require_once __DIR__.'/PHPTelebot.php';