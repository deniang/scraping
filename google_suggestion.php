<?php

$data = gsscrape("mobil baru");
print_r($data);

function gsscrape($keyword) {
    return json_decode(utf8_decode(file_get_contents('http://suggestqueries.google.com/complete/search?output=firefox&client=firefox&hl=en-US&q='.urlencode($keyword))),true);
}