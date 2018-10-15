function search_jdid($keyword="",$price=0,$type=0) {
    require_once('simple_html_dom.php');

    $url = "https://www.jd.id/search?keywords=".urlencode($keyword);
    $data = call($url);
    $html = str_get_html($data);

    if ($price <> 0) {
        $min = $price * 0.8;
        $max = $price * 1.2;
    }

    $ada = 0;
    $data = array();

    foreach($html->find(".list-products-t") as $element)  {

        foreach ($element->find(".item") as $items) {
            //print_r($items);
            foreach ($items->find('img[class=ui-switchable-imgscroll-img]') as $img) {
                $image = $img->src;
                //echo "Img : ".$image.PHP_EOL;
            }
            foreach ($items->find('a') as $href) {

                if (strpos($href->href,".html")) {

                    if (strlen($href->{'title'}) > 2) {
                        $url = $href->href;
                        $nama = $href->{'title'};
//                        echo "Href : ".$url.PHP_EOL;
//                        echo "Name : ".$nama.PHP_EOL;
                    }

                }
            }

            foreach ($items->find('.p-price') as $price) {
                $text = $price->innertext;
                $cek = strpos($text,">Rp ");
                if ($cek) {
                    $temp1 = substr($text,$cek);
                    $cek1 = strpos($temp1,"</span>");
                    $temp2 = substr($temp1,0,$cek1);
                    $harga = preg_replace("/[^0-9]/", "", $temp2);
                    //echo "Harga : ".$harga.PHP_EOL;

                    if (!$ada) {
                        if ($type == 0) {
                            if ($harga >= $min && $harga <= $max) {
                                $ada = 1;
                                $data = array("name"=>$nama,"img"=>$image,"url"=>$url,"price"=>$harga);
                            }
                        }elseif ($type == 1) {
                            if ($harga < $price) {
                                $ada = 1;
                                $data = array("name"=>$nama,"img"=>$image,"url"=>$url,"price"=>$harga);
                            }
                        }elseif ($type == 2) {
                            if ($harga > $price) {
                                $ada = 1;
                                $data = array("name"=>$nama,"img"=>$image,"url"=>$url,"price"=>$harga);
                            }
                        }
                    }
                }
            }
        }

    }

    return $data;
}
