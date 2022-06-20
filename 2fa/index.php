<?php
#
# OATH Code by "vhdm"
# https://gist.github.com/vhdm/f6e42479e1fb9f119d3d
#

$git_secret='xxx';
$proton_secret='xxx';

$raw = file_get_contents('php://input');

function get_timestamp($renegotiation) {
	return floor(microtime(true)/$renegotiation);

}

function base32_decode($b32) {

	$lookup_table = array(
		"A" => 0,	"B" => 1,
		"C" => 2,	"D" => 3,
		"E" => 4,	"F" => 5,
		"G" => 6,	"H" => 7,
		"I" => 8,	"J" => 9,
		"K" => 10,	"L" => 11,
		"M" => 12,	"N" => 13,
		"O" => 14,	"P" => 15,
		"Q" => 16,	"R" => 17,
		"S" => 18,	"T" => 19,
		"U" => 20,	"V" => 21,
		"W" => 22,	"X" => 23,
		"Y" => 24,	"Z" => 25,
		"2" => 26,	"3" => 27,
		"4" => 28,	"5" => 29,
		"6" => 30,	"7" => 31
	);

	$l 	= strlen($b32);
	$n	= 0;
	$j	= 0;
	$binary = "";

	for ($i = 0; $i < $l; $i++) {

		$n = $n << 5;
		$n = $n + $lookup_table[$b32[$i]];
		$j = $j + 5;

		if ($j >= 8) {
			$j = $j - 8;
			$binary .= chr(($n & (0xFF << $j)) >> $j);
		}
	}
	return $binary;
}

function oath_truncate($hash) {
	$offset = ord($hash[19]) & 0xf;

	return (
	((ord($hash[$offset+0]) & 0x7f) << 24 ) |
	((ord($hash[$offset+1]) & 0xff) << 16 ) |
	((ord($hash[$offset+2]) & 0xff) << 8 ) |
	(ord($hash[$offset+3]) & 0xff)
	) % pow(10, 6);
}

function oath_hotp($key, $counter) {
	$bin_counter = pack('N*', 0) . pack('N*', $counter);
	$hash = hash_hmac('sha1', $bin_counter, $key, true);
	return str_pad(oath_truncate($hash), 6, '0', STR_PAD_LEFT);
}

if(!empty($raw)) {
	switch($raw) {
		case 'git':
			print(oath_hotp(base32_decode($git_secret), get_timestamp(30)));
			break;
		case 'proton':
			print(oath_hotp(base32_decode($proton_secret), get_timestamp(30)));
			break;
		default:
			print('error');
			break;
	}
	exit();
}
?>

<!doctype html>
<html lang="cs">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>2FA</title>
	<link href="bootstrap.min.css" rel="stylesheet">
	<!-- Favicons -->
	<link rel="apple-touch-icon" href="./favicon/apple-touch-icon.png" sizes="180x180">
	<link rel="icon" href="./favicon/favicon-32x32.png" sizes="32x32" type="image/png">
	<link rel="icon" href="./favicon/favicon-16x16.png" sizes="16x16" type="image/png">
	<link rel="mask-icon" href="./favicon/safari-pinned-tab.svg" color="#7952b3">
	<!-- Custom styles -->
	<link href="custom.css" rel="stylesheet">
</head>

<body class="bg-light">
<main class="container text-center">
<form method="post" action="." enctype="multipart/form-data">

<div class="row my-4 mx-2 align-items-center justify-content-center">

	<div class="col-3 col-md-1">

	<svg xmlns="http://www.w3.org/2000/svg" onclick="gen_token('git')" width="56" height="56" viewBox="0 0 24 24"><path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/></svg>

	</div>

	<div class="col-5 col-md-3 bg-white rounded-3 border border-dark">
		<div class="row">
			<div id="git" class="col fs-2">&nbsp;</div>
			<div class="col-3 col-md-2 d-flex align-items-center">
				<svg xmlns="http://www.w3.org/2000/svg" onclick="clip('git')" class="bi bi-clipboard" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
			</div>
		</div>
	</div>

</div>

<div class="row m-4 mx-2 align-items-center justify-content-center">

	<div class="col-3 col-md-1">

	<svg width="56" height="56" onclick="gen_token('proton')" viewBox="0 0 979 785" fill="none" xmlns="http://www.w3.org/2000/svg"><g clip-path="url(#clip0_6150_150890)"><path d="M0 22.6001C0 3.50006 22.2 -6.89994 36.9 5.20006L424.7 327.4C462.2 358.6 516.6 358.6 554.1 327.4L941.8 5.20006C956.5 -6.99994 978.7 3.50006 978.7 22.5001V683.6C978.7 739.5 933.4 784.9 877.5 784.9H101.2C45.3 785 0 739.6 0 683.7V22.6001Z" fill="#686868"/><path fill-rule="evenodd" clip-rule="evenodd" d="M621.5 271.4L426.2 444.1C392.9 473.5 343.2 474.2 309.1 445.8L0 188.6V22.6003C0 3.50034 22.2 -6.89966 36.9 5.20034L424.7 327.4C462.2 358.6 516.6 358.6 554.1 327.4L621.5 271.4Z" fill="url(#paint0_linear_6150_150890)"/><path fill-rule="evenodd" clip-rule="evenodd" d="M770.6 147.501V785.001H877.5C933.4 785.001 978.7 739.601 978.7 683.701V22.6007C978.7 3.50066 956.5 -6.89934 941.8 5.30066L770.6 147.501Z" fill="url(#paint1_linear_6150_150890)"/></g><defs><linearGradient id="paint0_linear_6150_150890" x1="367.24" y1="470.973" x2="143.929" y2="-481.876" gradientUnits="userSpaceOnUse"><stop stop-color="#E1E1E1"/><stop offset="1" stop-color="#686868"/></linearGradient><linearGradient id="paint1_linear_6150_150890" x1="1292.76" y1="1293.89" x2="530.792" y2="-332.99" gradientUnits="userSpaceOnUse"><stop offset="0.271" stop-color="#E1E1E1"/><stop offset="1" stop-color="#686868"/></linearGradient><clipPath id="clip0_6150_150890"><rect width="979" height="785" fill="white"/></clipPath></defs></svg>

	</div>

	<div class="col-5 col-md-3 bg-white rounded-3 border border-dark">
		<div class="row">
			<div id="proton" class="col fs-2">&nbsp;</div>
			<div class="col-3 col-md-2 d-flex align-items-center">
				<svg xmlns="http://www.w3.org/2000/svg" onclick="clip('proton')" class="bi bi-clipboard" width="16" height="16" fill="currentColor" viewBox="0 0 16 16"><path d="M4 1.5H3a2 2 0 0 0-2 2V14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V3.5a2 2 0 0 0-2-2h-1v1h1a1 1 0 0 1 1 1V14a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3.5a1 1 0 0 1 1-1h1v-1z"/><path d="M9.5 1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-3a.5.5 0 0 1-.5-.5v-1a.5.5 0 0 1 .5-.5h3zm-3-1A1.5 1.5 0 0 0 5 1.5v1A1.5 1.5 0 0 0 6.5 4h3A1.5 1.5 0 0 0 11 2.5v-1A1.5 1.5 0 0 0 9.5 0h-3z"/></svg>
			</div>
		</div>
	</div>

</div>

</form>
</main>

<script src="custom.js"></script>

</body>
</html>

