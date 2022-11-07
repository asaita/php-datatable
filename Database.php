<?php
class Database extends PDO
{
    //$username = "cyberint_ddouser", $passwd = "9shJiY8zB%zd"
    public function __construct($dsn="mysql:dbname=contact_app_db;host=localhost;charset=utf8", $username = "root", $passwd = "", $options = null)
    {

        parent::__construct($dsn, $username, $passwd, $options);

    }



    public function listele($tablo_adi,$alanlar,$sart,$sartdegerleri)

    {

        //$sorgu2="SELECT ".$alanlar." FROM ".$tablo_adi." WHERE ".$sart;

        $sorgu=$this->prepare("SELECT ".$alanlar." FROM ".$tablo_adi." WHERE ".$sart);

        //echo $sorgu2;
        //exit(0);

        //Tüm kayıtları listelemek için Database sınıfındaki listele fonksiyonuna uymak için aşağıdaki şart değerini yazdım
        if ($sartdegerleri==0){$sorgu->execute();}
        else {$sorgu->execute($sartdegerleri);}
        

        $gelen_degerler=$sorgu->fetchAll(PDO::FETCH_ASSOC);

        return $gelen_degerler;

    }

    public function ekle($tablo_adi,$alanlar,$fake_degerler,$degerler)

    {



            $alan_dizi=explode(',',$alanlar);

            $fakedeger_dizi=explode(',',$fake_degerler);

            if((count($alan_dizi)==count($fakedeger_dizi)) AND (count($alan_dizi)==count($degerler)))

                {

                    foreach($fakedeger_dizi as $d)

                        if($d!='?')

                        {

                            return 0;

                        }

                }

        $sorgu=$this->prepare("INSERT INTO  ".$tablo_adi."(".$alanlar.") VALUES(".$fake_degerler.")");

        $sorgu->execute($degerler);

        if($sorgu->rowCount()>0)

        {

            return 1;

        }

        else

        {

            return 0;

        }

    }

    public function guncelle($tablo_adi,$alanlar,$degerler,$sart)

    {



         $sorgu=$this->prepare("UPDATE ".$tablo_adi." SET ".$alanlar." WHERE ".$sart);

        $sorgu->execute($degerler);

        if($sorgu->rowCount()>0)

        {

            return 1;

        }

        else

        {

            return 0;

        }



    }



    public function kategori_getir($sart,$sart_degerleri)

    {

        $sorgu=$this->prepare("SELECT * FROM varlik_gruplari WHERE ".$sart);

        $sorgu->execute($sart_degerleri);

        $items=$sorgu->fetchAll(PDO::FETCH_ASSOC);

        return $items;

    }

    public function tedbir_etkinlik_durumu_hesapla($denetim_id,$varlik_dizi)
    {
        $tedbirler_baslik_dizi=array();
        $tedbirler_anabaslik_dizi=array();
        if(count($varlik_dizi)>0)
        {
            foreach ($varlik_dizi as $ana_grup_id=>$grup_dizi)
            {
                foreach($grup_dizi as $grup_id)
                {
                    if(!isset($tedbirler_baslik_dizi[$grup_id]))
                    {
                        $tedbirler_baslik_dizi[$grup_id]=array();
                    }
                    if(!isset($tedbirler_anabaslik_dizi[$grup_id]))
                    {
                        $tedbirler_anabaslik_dizi[$grup_id]=array();
                    }

                    $varlik_grubu_tedbirler=$this->listele("varlik_grubu_tedbir","*","denetim_id=? AND ana_grup_id=? AND grup_id=? AND durum=? ",array($denetim_id,$ana_grup_id,$grup_id,1));
                    if(count($varlik_grubu_tedbirler)>0)
                    {
                        foreach($varlik_grubu_tedbirler as $varlik_grubu_tedbir)
                        {

                            $tedbir_bilgi=$this->listele("tedbirler","*","id=?",array($varlik_grubu_tedbir["tedbir_id"]));
                            $tedbir_baslikbilgi=$this->listele("tedbir_baslik","*","id=?",array($tedbir_bilgi[0]["tedbir_baslik_id"]));
                            if(!isset($tedbirler_baslik_dizi[$grup_id][$tedbir_bilgi[0]["tedbir_baslik_id"]]))
                            {
                                $tedbirler_baslik_dizi[$grup_id][$tedbir_bilgi[0]["tedbir_baslik_id"]]=array();
                            }
                            $tedbirler_baslik_dizi[$grup_id][$tedbir_bilgi[0]["tedbir_baslik_id"]][]=$varlik_grubu_tedbir["tedbir_id"];
                            if(!isset($tedbirler_anabaslik_dizi[$grup_id][$tedbir_baslikbilgi[0]["ana_baslik_id"]]))
                            {
                                $tedbirler_anabaslik_dizi[$grup_id][$tedbir_baslikbilgi[0]["ana_baslik_id"]]=array();
                            }
                            $tedbirler_anabaslik_dizi[$grup_id][$tedbir_baslikbilgi[0]["ana_baslik_id"]][]=$varlik_grubu_tedbir["tedbir_id"];


                        }

                    }
                }
            }

        }
        return array($tedbirler_anabaslik_dizi,$tedbirler_baslik_dizi);

    }
    public function tedbir_etkinlik_durumu_hesapla2($denetim_id)
    {
        $tedbir_varlik_grubu_anabaslik_dizi=array();
        $tedbir_varlik_grubu_baslik_dizi=array();
        $sonuc=array();
        $denetimdeki_varlik_gruplari=$this->listele("denetim_varlik","grup_id","denetim_id=? AND durum=? GROUP BY grup_id",array($denetim_id,1));
        if(count($denetimdeki_varlik_gruplari)>0)
        {
            foreach($denetimdeki_varlik_gruplari as $vgrupbilgi)
            {

               if(!isset($tedbir_varlik_grubu_anabaslik_dizi[$vgrupbilgi["grup_id"]]))
               {
                   $tedbir_varlik_grubu_anabaslik_dizi[$vgrupbilgi["grup_id"]]=array();
               }

                $sgls3='SELECT tedbir_ana_baslik.id as baslik_id FROM tedbir_etkinlik_durumu 
LEFT JOIN tedbirler ON tedbirler.id=tedbir_etkinlik_durumu.tedbir_id 
LEFT JOIN tedbir_baslik ON tedbir_baslik.id=tedbirler.tedbir_baslik_id 
LEFT JOIN tedbir_ana_baslik ON tedbir_ana_baslik.id=tedbir_baslik.ana_baslik_id 
LEFT JOIN varlik_alt_gruplari ON tedbir_etkinlik_durumu.varlik_grup_id=varlik_alt_gruplari.id WHERE tedbir_etkinlik_durumu.denetim_id=? AND tedbir_etkinlik_durumu.durum=? AND tedbir_etkinlik_durumu.varlik_grup_id=? GROUP BY tedbir_ana_baslik.id';
                $sorgu3=$this->prepare($sgls3);
                $sorgu3->execute(array($denetim_id,1,$vgrupbilgi["grup_id"]));
                $tedbir_basliklari_etkinlik_durumu3=$sorgu3->fetchAll(PDO::FETCH_ASSOC);
                if(count($tedbir_basliklari_etkinlik_durumu3)>0)
                {
                    foreach($tedbir_basliklari_etkinlik_durumu3 as $tedbir_basliklari_etkinlik3)
                    {

                        $sgls4='SELECT tedbir_etkinlik_durumu.tedbirin_uygulanma_durumu FROM tedbir_etkinlik_durumu 
LEFT JOIN tedbirler ON tedbirler.id=tedbir_etkinlik_durumu.tedbir_id 
LEFT JOIN tedbir_baslik ON tedbir_baslik.id=tedbirler.tedbir_baslik_id 
LEFT JOIN tedbir_ana_baslik ON tedbir_ana_baslik.id=tedbir_baslik.ana_baslik_id 
LEFT JOIN varlik_alt_gruplari ON tedbir_etkinlik_durumu.varlik_grup_id=varlik_alt_gruplari.id WHERE tedbir_etkinlik_durumu.denetim_id=? AND tedbir_etkinlik_durumu.durum=? AND tedbir_etkinlik_durumu.varlik_grup_id=? AND tedbir_ana_baslik.id=?';
                        $sorgu4=$this->prepare($sgls4);
                        $sorgu4->execute(array($denetim_id,1,$vgrupbilgi["grup_id"],$tedbir_basliklari_etkinlik3["baslik_id"]));
                        $tedbir_degerleri4=$sorgu4->fetchAll(PDO::FETCH_ASSOC);
                        $toplam_puan2=0;
                        if(count($tedbir_degerleri4)>0)
                        {
                            $puan2=100/count($tedbir_degerleri4);

                            foreach($tedbir_degerleri4 as $tedbir_deger4)
                            {
                                $carpan2=0;
                                if(isset($tedbir_deger4["tedbirin_uygulanma_durumu"]))
                                {
                                    switch($tedbir_deger4["tedbirin_uygulanma_durumu"])
                                    {
                                        case 'U':
                                            $carpan2=1;
                                            break;
                                        case 'K':
                                            $carpan2=0.5;
                                            break;
                                        case 'Ç':
                                            $carpan2=0.75;
                                            break;
                                        case 'T':
                                            $carpan2=1;
                                            break;
                                        default:
                                            $carpan2=0;
                                            break;
                                    }

                                    $toplam_puan2+=($carpan2*$puan2);

                                }


                            }
                        }
                        $tedbir_varlik_grubu_anabaslik_dizi[$vgrupbilgi["grup_id"]][$tedbir_basliklari_etkinlik3["baslik_id"]]=$toplam_puan2;

                    }
                }


            }

           foreach($denetimdeki_varlik_gruplari as $vgrupbilgi)
            {
                $sgls = 'SELECT tedbir_baslik.id as baslik_id FROM tedbir_etkinlik_durumu
LEFT JOIN tedbirler ON tedbirler.id=tedbir_etkinlik_durumu.tedbir_id 
LEFT JOIN tedbir_baslik ON tedbir_baslik.id=tedbirler.tedbir_baslik_id 
LEFT JOIN tedbir_ana_baslik ON tedbir_ana_baslik.id=tedbir_baslik.ana_baslik_id 
LEFT JOIN varlik_alt_gruplari ON tedbir_etkinlik_durumu.varlik_grup_id=varlik_alt_gruplari.id WHERE tedbir_etkinlik_durumu.denetim_id=? AND tedbir_etkinlik_durumu.durum=? AND tedbir_etkinlik_durumu.varlik_grup_id=? GROUP BY tedbir_baslik.id';
                $sorgu = $this->prepare($sgls);
                $sorgu->execute(array($denetim_id, 1, $vgrupbilgi["grup_id"]));
                $tedbir_basliklari_etkinlik_durumu = $sorgu->fetchAll(PDO::FETCH_ASSOC);
                if (count($tedbir_basliklari_etkinlik_durumu) > 0)
                {
                    foreach ($tedbir_basliklari_etkinlik_durumu as $tedbir_basliklari_etkinlik)
                    {
                        $sgls2 = 'SELECT tedbir_etkinlik_durumu.tedbirin_uygulanma_durumu FROM tedbir_etkinlik_durumu 
LEFT JOIN tedbirler ON tedbirler.id=tedbir_etkinlik_durumu.tedbir_id 
LEFT JOIN tedbir_baslik ON tedbir_baslik.id=tedbirler.tedbir_baslik_id 
LEFT JOIN tedbir_ana_baslik ON tedbir_ana_baslik.id=tedbir_baslik.ana_baslik_id 
LEFT JOIN varlik_alt_gruplari ON tedbir_etkinlik_durumu.varlik_grup_id=varlik_alt_gruplari.id WHERE tedbir_etkinlik_durumu.denetim_id=? AND tedbir_etkinlik_durumu.durum=? AND tedbir_etkinlik_durumu.varlik_grup_id=? AND tedbir_baslik.id=?';
                        $sorgu2 = $this->prepare($sgls2);
                        $sorgu2->execute(array($denetim_id, 1, $vgrupbilgi["grup_id"], $tedbir_basliklari_etkinlik["baslik_id"]));
                        $tedbir_degerleri = $sorgu2->fetchAll(PDO::FETCH_ASSOC);
                        $toplam_puan = 0;
                        if (count($tedbir_degerleri) > 0) {
                            $puan = 100 / count($tedbir_degerleri);

                            foreach ($tedbir_degerleri as $tedbir_deger) {
                                $carpan = 0;
                                if (isset($tedbir_deger["tedbirin_uygulanma_durumu"])) {
                                    switch ($tedbir_deger["tedbirin_uygulanma_durumu"]) {
                                        case 'U':
                                            $carpan = 1;
                                            break;
                                        case 'K':
                                            $carpan = 0.5;
                                            break;
                                        case 'Ç':
                                            $carpan = 0.75;
                                            break;
                                        case 'T':
                                            $carpan = 1;
                                            break;
                                        default:
                                            $carpan = 0;
                                            break;
                                    }

                                    $toplam_puan += ($carpan * $puan);

                                }


                            }
                        }

                        $tedbir_varlik_grubu_baslik_dizi[$vgrupbilgi["grup_id"]][$tedbir_basliklari_etkinlik["baslik_id"]]=$toplam_puan;

                    }
                }

            }



        }
        $sonuc[0]=$tedbir_varlik_grubu_anabaslik_dizi;
        $sonuc[1]=$tedbir_varlik_grubu_baslik_dizi;

        return $sonuc;


    }

}
?>