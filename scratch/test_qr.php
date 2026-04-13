<?php
require 'vendor/autoload.php';
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Support\Facades\File;

if (!File::exists('public/storage/qr_codes/')) {
    File::makeDirectory('public/storage/qr_codes/', 0777, true);
}

// Mimic the service logic
$url = "http://localhost/menu/test-slug";
$path = 'public/storage/qr_codes/test.svg';

// We need to bootstrap Laravel or use the package directly if possible.
// Since I can't easily bootstrap Laravel in a script, I'll just check the service code again.
