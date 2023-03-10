<?php
#
# Simple reboot/shutdown form
#

$FILE='/tmp/setup.log';

if (isset($_POST['action'])) {
	if ($_POST['action'] == 'reboot') {
		file_put_contents($FILE, 'reboot');
	}
	if ($_POST['action'] == 'shutdown') {
		file_put_contents($FILE, 'shutdown');
	}
	#PRG
	header('Location: /setup/');
	exit();
}

?>

<!doctype html>
<html lang="cs">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Setup</title>
	<link href="bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
<main class="container text-center">
<form method="post" action="." enctype="multipart/form-data">

<div class="row my-4 mx-2 align-items-center justify-content-center">

<div class="row my-4">
	<form method="post" action="." enctype="multipart/form-data">
	<div class="d-grid gap-2 col-8 col-md-4 mx-auto">
		<button class="btn btn-danger" type="submit" name="action" value="reboot">Reboot</button>
		<button class="btn btn-danger" type="submit" name="action" value="shutdown">Shutdown</button>
	</div>
	</form>
</div>

</div>
</form>
</main>
</body>
</html>

