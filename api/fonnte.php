<?php
// api/fonnte.php
require_once __DIR__ . '/../config/database.php';

function kirimWA($target, $pesan) {
    // Normalisasi nomor telepon (hanya digit)
    $target = preg_replace('/[^0-9]/', '', $target);

    if (empty($target)) {
        return ['status' => false, 'message' => 'Nomor target kosong'];
    }

    $curl = curl_init();
    curl_setopt_array($curl, array(
        CURLOPT_URL => 'https://api.fonnte.com/send',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => '',
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 15,
        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
        CURLOPT_SSL_VERIFYPEER => false,
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_FOLLOWLOCATION => true,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => 'POST',
        CURLOPT_POSTFIELDS => array(
            'target' => $target,
            'message' => $pesan,
            'countryCode' => '62',
        ),
        CURLOPT_HTTPHEADER => array(
            "Authorization: " . FONNTE_TOKEN
        ),
    ));

    $response = curl_exec($curl);
    $err = curl_error($curl);
    curl_close($curl);

    if ($err) {
        error_log("Fonnte WA Error ($target): " . $err);
        return ['status' => false, 'message' => $err];
    } else {
        $result = json_decode($response, true);
        return $result;
    }
}
