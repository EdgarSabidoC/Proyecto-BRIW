<!DOCTYPE HTML>
<html lang='es-MX'>
	<head>
		<title>Buscador empresarial - Webbots</title>
		<!-- Metadatos -->
		<meta charset='UTF-8'>
		<meta http-equiv='X-UA-Compatible'
					content='IE=edge'>
		<meta name='viewport'
					content='width=device-width, initial-scale=1.0'>
		<meta name='author' content='Edgar Sabido Cortés, Carlos Antonio Ruiz Domínguez y Alexis De Jesús Rosaldo Pacheco'>
		<meta name='description'
					content='Buscador empresarial con MariaDB, índices Fulltext y crawler'>
		<!-- Favicon-->
		<link rel="icon" type="image/x-icon" href="assets/favicon.ico" />
		<!-- Bootstrap icons-->
		<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
		<!-- Core theme CSS (includes Bootstrap)-->
		<link href="css/styles.css" rel="stylesheet" />
	</head>
	<body class="d-flex flex-column h-100">
		<!-- Navigation-->
		<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
			<div class="container px-5">
				<a class="navbar-brand" href="index.php">Buscador Empresarial</a>
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation"><span class="navbar-toggler-icon"></span></button>
				<div class="collapse navbar-collapse" id="navbarSupportedContent">
					<ul class="navbar-nav ms-auto mb-2 mb-lg-0">
						<li class="nav-item"><a class="nav-link" href="index.php">Inicio</a></li>
						<li class="nav-item"><a class="nav-link" href="URLs.php">Añadir Sitios</a></li>
					</ul>
				</div>
			</div>
		</nav>
		<!-- Page content-->
		<section class="bg-dark py-5">
			<div class="container px-5">
				<!--form-->
				<div class="bg-secondary rounded-3 py-5 px-4 px-md-5 mb-5">
					<div class="search">
						<div class="text-center mb-5">
							<h1 class="fw-bolder">INDEXAR SITIOS WEB</h1>
						</div>
						<div class="row gx-5 justify-content-center">
							<div class="col-lg-8 col-xl-6">
								<form id='buscador' class='form' method='POST'>
									<!-- Message input-->
									<div class="form-floating mb-3">
										<textarea id="textArea" name="textArea" class="form-control" type="text" placeholder="Ingresar Sitio..." style="height: 10rem"></textarea>
										<label for="Ingresar Sitio">Ingresar Sitios</label>
									</div>
									<!-- Submit Button-->
									<div class="d-grid gap-3 d-sm-flex justify-content-sm-center">
										<button class="btn btn-primary btn-lg" id='erase' name='erase' type="submit">Borrar</button>
										<button class="btn btn-primary btn-lg" id='save' name='save' type="submit">Guardar</button>
										<button class="btn btn-primary btn-lg" id="submitButton" name='submitButton' type="submit">Enviar</button>
									</div>
								</form>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>
		<!-- Este contenedor se genera a partir del script de PHP -->
		<section class='container'>
			<?php
				include_once 'scripts/Crawler/functions.php';
				if(!empty(get_cookie("myUrls"))) {
					if(isset($_COOKIE['index'])){
						include_once 'scripts/Crawler/script.php';
					}
					$arr_cookie = explode(PHP_EOL, get_cookie("myUrls"));
					echo "
					<div class='bg-light rounded-3 py-5 px-4 px-md-5 mb-5'> 
					<div class='row gx-5 justify-content-center'>
						<div class=table-wrapper>
						<table class='table table-earnings'>
							<thead class='thead-dark'>
								<tr scope='col'>
									<th>URLs</th>
								</tr>
							</thead>
							<tbody>
					";
					foreach ($arr_cookie as $value) {
						echo "
								<tr>
									<td>".$value."</td>
								</tr>";
					}

					echo "
							</tbody>
						</table>
						</div>
					</div>
					</div>
					";
				}
			?>
			<?php
				if ($_SERVER['REQUEST_METHOD'] === 'POST'
				&& isset($_POST['submitButton'])
				&& !isset($_COOKIE['user'])
				&& !isset($_COOKIE['myUrls'])) {
					include_once 'scripts/Crawler/uploadURLs.php';
					setcookie("index", 3, time()+30);
					header('Refresh:0');
				} elseif($_SERVER['REQUEST_METHOD'] === 'POST'
				&& isset($_POST['save'])){
					include_once 'scripts/Crawler/uploadURLs.php';
					header('Refresh:0');
				} elseif($_SERVER['REQUEST_METHOD'] === 'POST'
				&& isset($_POST['erase'])){
					include_once 'scripts/Crawler/functions.php';
					clear_cookies();
					header('Refresh:0');
				} elseif($_SERVER['REQUEST_METHOD'] === 'POST'
				&& isset($_COOKIE['user'])
				&& isset($_COOKIE['myUrls'])){
					include_once 'scripts/Crawler/uploadURLs.php';
					setcookie("index", 3, time()+30);
					header('Refresh:0');
				}
			?>
		</section>
		<!-- Termina contenedor del script de PHP -->
		<!-- Footer-->
		<footer class="bg-dark py-4 mt-auto">
			<div class="container px-5">
				<div class="row align-items-center justify-content-between flex-column flex-sm-row">
					<div class="col-auto"><div class="small m-0 text-white">Búsqueda y Recuperación de Información en la Web</div></div>
					<div class="col-auto">
						<a class="link-light small" href="about.php">Sobre Nosotros</a>
					</div>
				</div>
			</div>
		</footer>
		<!-- Bootstrap core JS-->
		<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
	</body>
	<!-- Termina script -->

	<!-- Script que sirve para evitar que el formulario se reenvíe al cargar la página -->
	<script type='text/javascript'>
		if (window.history.replaceState) {
			window.history.replaceState(null, null, window.location.href);
		}
	</script>
	<!--Termina Script-->

</html>