<?php
function getRelatedTerms($term)
{
    hit();
    $url = sprintf('http://www.google.com/search?q=%s', urlencode($term));

    $userAgent = 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.3) Gecko/20100423 Ubuntu/10.04 (lucid) Firefox/3.6.3';

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $userAgent,
    ));
    $googleRes = curl_exec($ch);
    curl_close($ch);

    if (strlen($googleRes) > 100) {
        $mulai = strpos($googleRes,',"refpd":true,"rfs":');
        $data = substr($googleRes,$mulai+21,strlen($googleRes));

        $mulai_lg = strpos($data,'],"');
        $data = substr($data,0,$mulai_lg);

        $data = str_replace('"',"",$data);

        $data = explode(',',$data);

        return $data;
    }else {
        return "kosong";
    }

}

function hit() {
    $url = "http://www.google.com/ncr";
    $userAgent = 'Mozilla/5.0 (X11; U; Linux x86_64; en-US; rv:1.9.2.3) Gecko/20100423 Ubuntu/10.04 (lucid) Firefox/3.6.3';

    $ch = curl_init($url);
    curl_setopt_array($ch, array(
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERAGENT => $userAgent,
    ));
    $googleRes = curl_exec($ch);
    curl_close($ch);
    print_r($googleRes);
}

$keyword = isset($_GET["key"]) ? $_GET["key"] : "manfaat";

$result = getRelatedTerms($keyword);

if (count($result) > 0) {
    print_r($result);
    foreach( $result as $rs) {
        echo $rs.PHP_EOL;
    }
}else {
    hit();
}