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

function search_blibli($keyword="",$price=0,$type=0) {
    $keyword = str_replace(" ","-",$keyword);

    $url = "https://www.blibli.com/".$keyword."/53400?listField=Search+Results+Page&originalSearchUrl=".$keyword."&searchSEOCustomUrl=".$keyword."&searchTermSeoContent=".$keyword."&searchDefaultSorting=SEARCH_RELEVANCE&searchCustomRootUrl=%2Fjual&pvSwitch=true";
    $url = "https://www.blibli.com/jual/".$keyword;
    $data = call($url);

    preg_match_all('/((<[\\s\\/]*script\\b[^>]*>)([^>]*)(<\\/script>))/i', $data, $scripts);

    $hasil = "";
    foreach ($scripts as $data) {

        if (isset($data[8]) && strpos($data[8],"itemListElement") > 0) {
            $hasil = $data[8];
        }
    }

    //print_r(json_decode($hasil));
    $rere = json_decode($hasil);

    if ($price <> 0) {
        $min = $price * 0.8;
        $max = $price * 1.2;
    }

    $ada = 0;
    $data = array();
    $data1 = array();
    $kecil = 1000000;

    if (isset($rere->itemListElement)) {
        foreach ($rere->itemListElement as $datas) {
            $harga = $datas->offers->price;

            if (!$ada) {
                if ($type == 0) {
                    if ($datas->offers->price >= $min && $datas->offers->price <= $max) {
                        $ada = 1;
                        $data = array("name"=>$datas->name,"img"=>$datas->image,"url"=>$datas->url,"price"=>$datas->offers->price);
                    }
                }elseif ($type == 1) {
                    if ($datas->offers->price < $price) {
                        $ada = 1;
                        $data = array("name"=>$datas->name,"img"=>$datas->image,"url"=>$datas->url,"price"=>$datas->offers->price);
                    }
                }elseif ($type == 2) {
                    if ($datas->offers->price > $price) {
                        $ada = 1;
                        $data = array("name"=>$datas->name,"img"=>$datas->image,"url"=>$datas->url,"price"=>$datas->offers->price);
                    }
                }

                if ($harga < $kecil) {
                    $data1 = array("name"=>$datas->name,"img"=>$datas->image,"url"=>$datas->url,"price"=>$datas->offers->price);
                }

            }

        }
    }

    if (count($data) > 0) {
        return $data;
    }else {
        return $data1;
    }


}
