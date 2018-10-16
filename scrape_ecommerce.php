<?php
$keyword = "Nokia 105 Dual Sim";
$price = 255000;
$result = proses_keyword();
print_r($result);

function proses_keyword($keyword="",$price=0) {
    //$type = 0 range, 1 below, 2 ctype_upper
    echo "Proses Toped ".$keyword." ".$price.PHP_EOL;
    $toped = search_tokopedia($keyword,$price,0);
    //print_r($toped);
    echo "Proses bukalapak ".$keyword." ".$price.PHP_EOL;
    $bulak = search_bukalapak($keyword,$price,0);
    //print_r($bulak);
    echo "Proses lazada ".$keyword." ".$price.PHP_EOL;
    $lazada = search_lazada_new($keyword,$price,0);
    //print_r($lazada);
    echo "Proses blibli ".$keyword." ".$price.PHP_EOL;
    $blibli = search_blibli($keyword,$price,0);
    //print_r($blibli);
    echo "Proses jdid ".$keyword." ".$price.PHP_EOL;
    $jdid = search_jdid($keyword,$price,0);
    //print_r($jdid);

    $data = array("tokopedia"=>$toped,"bukalapak"=>$bulak,"lazada"=>$lazada,"blibli"=>$blibli,"jdid"=>$jdid);

    return json_encode($data);

}

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

function jsonp_decode($jsonp, $assoc = false) {
    if($jsonp[0] !== '[' && $jsonp[0] !== '{') {
        $jsonp = substr($jsonp, strpos($jsonp, '('));
    }
    return json_decode(trim($jsonp,'();'), $assoc);
}

function call($url, $method="GET", $payload=null)
{

    $conn = curl_init();

    $headers = array('Content-Type: application/json');

    curl_setopt($conn, CURLOPT_URL, $url);
    curl_setopt($conn, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($conn, CURLOPT_CUSTOMREQUEST, strtoupper($method));
    curl_setopt($conn, CURLOPT_FORBID_REUSE, 0);
    curl_setopt($conn, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($conn,CURLOPT_USERAGENT,'Mozilla/5.0 (Windows; U; Windows NT 5.1; en-US; rv:1.8.1.13) Gecko/20080311 Firefox/2.0.0.13');
    if (strlen($payload) > 1) {
        curl_setopt($conn, CURLOPT_POSTFIELDS, $payload);
    }

    $response = curl_exec($conn);

    return $response;
}

function search_tokopedia($keyword="",$price=0,$type=0) {
    //$type = 0 range, 1 below, 2 ctype_upper

    $url = "https://ace.tokopedia.com/search/product/v3?&device=desktop&catalog_rows=5&source=search&ob=23&st=product&rows=60&q=".urlencode($keyword);
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $yeye = json_decode($response);

    if ($price <> 0) {
        $min = $price * 0.8;
        $max = $price * 1.2;
    }

    $ada = 0;
    $data = array();
    if (isset($yeye->data->products)) {
        foreach ($yeye->data->products as $dat) {
            if (!$ada) {
                if ($type == 0) {
                    if ($dat->price_int >= $min && $dat->price_int <= $max) {
                        $ada = 1;
                        $data = array("name"=>$dat->name,"img"=>$dat->image_url,"url"=>$dat->url,"price"=>$dat->price_int);
                    }
                }elseif ($type == 1) {
                    if ($dat->price_int < $price) {
                        $ada = 1;
                        $data = array("name"=>$dat->name,"img"=>$dat->image_url,"url"=>$dat->url,"price"=>$dat->price_int);
                    }
                }elseif ($type == 2) {
                    if ($dat->price_int > $price) {
                        $ada = 1;
                        $data = array("name"=>$dat->name,"img"=>$dat->image_url,"url"=>$dat->url,"price"=>$dat->price_int);
                    }
                }

            }

        }

    }

    return $data;
}

function search_bukalapak($keyword="",$price=0,$type=0) {
    $url = "https://www.bukalapak.com/omniscience/v2?user=32247da095a528bb614c2ce7dda18228&word=".urlencode($keyword)."&key=efa871c40072792cedad312272ca2daa";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    $yeye = json_decode($response);

    if ($price <> 0) {
        $min = $price * 0.8;
        $max = $price * 1.2;
    }

    $ada = 0;
    $data = array();
    if (isset($yeye->product)) {
        foreach ($yeye->product as $dat) {
            if (!$ada) {
                if ($type == 0) {
                    if ($dat->price >= $min && $dat->price <= $max) {
                        $ada = 1;
                        $data = array("name"=>$dat->name,"img"=>$dat->img,"url"=>$dat->url,"price"=>$dat->price);
                    }
                }elseif ($type == 1) {
                    if ($dat->price < $price) {
                        $ada = 1;
                        $data = array("name"=>$dat->name,"img"=>$dat->img,"url"=>$dat->url,"price"=>$dat->price);
                    }
                }elseif ($type == 2) {
                    if ($dat->price > $price) {
                        $ada = 1;
                        $data = array("name"=>$dat->name,"img"=>$dat->img,"url"=>$dat->url,"price"=>$dat->price);
                    }
                }

            }

        }

    }

    return $data;
}

function search_lazada($keyword="",$price=0,$type=0) {
    $url = "https://www.lazada.co.id/catalog/?q=".urlencode($keyword)."&_keyori=ss&from=input&spm=a2o4j.home.search.go.57994ceeU2IlKI";
    $data = call($url);

    preg_match_all('#<script>(.*?)</script>#i', $data, $scripts);

    //print_r($scripts);
    $hasil = "";
    foreach ($scripts as $data) {
        //    print_r($data[0]);
        if (isset($data[0]) && strpos($data[0],"window.pageData=") > 0) {
            $hasil = $data[0];
        }
    }

    if ($price <> 0) {
        $min = $price * 0.8;
        $max = $price * 1.2;
    }

    $ada = 0;
    $data = array();

    if (strlen($hasil) > 3) {
        $hasil = str_replace("<script>window.pageData=","",$hasil);
        $hasil = str_replace("</script>","",$hasil);

        //print_r($hasil);
        $rere = json_decode($hasil);
        if (isset($rere->mods->listItems)) {
            foreach ($rere->mods->listItems as $datas) {
                if (!$ada) {
                    if ($type == 0) {
                        if ($datas->price >= $min && $datas->price <= $max) {
                            $ada = 1;
                            $data = array("name"=>$datas->name,"img"=>$datas->image,"url"=>$datas->productUrl,"price"=>$datas->price);
                        }
                    }elseif ($type == 1) {
                        if ($datas->price < $price) {
                            $ada = 1;
                            $data = array("name"=>$datas->name,"img"=>$datas->image,"url"=>$datas->productUrl,"price"=>$datas->price);
                        }
                    }elseif ($type == 2) {
                        if ($datas->price > $price) {
                            $ada = 1;
                            $data = array("name"=>$datas->name,"img"=>$datas->image,"url"=>$datas->productUrl,"price"=>$datas->price);
                        }
                    }

                }

            }
        }
    }

    return $data;
}
