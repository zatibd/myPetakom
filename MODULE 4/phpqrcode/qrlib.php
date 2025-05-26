<?php
function generateQR($text, $filepath = false, $size = 4) {
    $urlEncoded = urlencode($text);
    $api = "https://chart.googleapis.com/chart?chs=" . ($size * 33) . "x" . ($size * 33) . "&cht=qr&chl=$urlEncoded&chld=L|1";

    $imageData = file_get_contents($api);
    if (!$imageData) {
        die("Failed to fetch QR code.");
    }

    if ($filepath) {
        file_put_contents($filepath, $imageData);
    } else {
        header('Content-Type: image/png');
        echo $imageData;
    }
}
?>
