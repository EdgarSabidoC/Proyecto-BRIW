<!DOCTYPE html>
<html lang="es-MX">
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
	<body class="d-flex flex-column">
		<main class="flex-shrink-0">
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
			<!-- Header-->
			<header class="py-5">
				<div class="container px-5">
					<div class="row justify-content-center">
						<div class="col-lg-8 col-xxl-6">
							<div class="my-5 text-white">
								<h1 class="fw-bolder text-center mb-3">Sistema de búsqueda y recuperación</h1>
								<p class="lead text-center fw-normal mb-4">
									Sistema de búsqueda y recuperación en español a partir de Solr.
								</p>
								<ul class="lead fw-normal mb-4">
									<li>Crawling de páginas HTML</li>
									<li>Preprocesamiento de texto en español</li>
									<li>Búsqueda booleana</li>
									<li>Relevancia ponderada</li>
									<li>Sugerencias en el llenado</li>
									<li>Sugerencias de corrección</li>
									<li>Resultados con snipets</li>
									<li>Expansión semántica en español</li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</header>
			<!-- Team members section-->
			<section class="py-5 bg-light">
				<div class="container px-5 my-5">
					<div class="text-center">
						<h2 class="fw-bolder">Integrantes del equipo</h2>
					</div>
					<div class="row gx-5 row-cols-1 row-cols-sm-2 row-cols-xl-4 justify-content-center">
						<div class="col mb-5 mb-5 mb-xl-0">
							<div class="text-center">
								<img class="img-fluid rounded-circle mb-4 px-4" src="assets/About/Member_1.png" alt="..." />
								<h5 class="fw-bolder">Edgar Sabido Cortés</h5>
								<div class="fst-italic text-muted"><a href="https://github.com/EdgarSabidoC">@EdgarSabidoC</a></div>
							</div>
						</div>
						<div class="col mb-5 mb-5 mb-xl-0">
							<div class="text-center">
								<img class="img-fluid rounded-circle mb-4 px-4" src="assets/About/Member_2.png" alt="..." />
								<h5 class="fw-bolder">Carlos A. Ruiz Domínguez</h5>
								<div class="fst-italic text-muted"><a href="https://github.com/carlosruiz01">@carlosruiz01</a></div>
							</div>
						</div>
						<div class="col mb-5 mb-5 mb-sm-0">
							<div class="text-center">
								<img class="img-fluid rounded-circle mb-4 px-4" src="assets/About/Member_3.png" alt="..." />
								<h5 class="fw-bolder">Alexis Rosaldo Pacheco</h5>
								<div class="fst-italic text-muted"><a href="https://github.com/Aler011">@Aler011</a></div>
							</div>
						</div>
					</div>
				</div>
			</section>
		</main>
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
</html>
