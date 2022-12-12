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
	<body class="d-flex flex-column h-100">
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
			<header class="bg-dark py-5">
				<div class="container px-5">
					<div class="row justify-content-center padding">
						<div class="col-md-8 ftco-animate fadeInUp ftco-animated">
							<div class="my-5 text-center">
								<h1 class="display-5 fw-bolder text-white mb-2">BUSCADOR EMPRESARIAL</h1>
							</div>
							<!--Empieza Busqueda-->
							<form id='buscador' autocomplete="off" action="" class="domain-form" method='POST' action='<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>'>
								<div class="form-group d-md-flex autocomplete">
									<input name='searchBox' type="text" class="form-control px-4" id="search" placeholder="Buscar...">
									<input name='submitButton' type="submit" class="search-domain btn btn-primary px-5" value="Buscar">
								</div>
							</form>
							<!--Termina Busqueda-->
						</div>
					</div>
				</div>
			</header>
			<!-- Este contenedor se genera a partir del script de PHP -->
			<section id='correct' class='container'>
				<?php
					if ($_SERVER['REQUEST_METHOD'] == 'POST') {
						include_once 'scripts/Solr/querySolrFunctions.php';
						$query = $_POST['searchBox'];
						$terms = get_spell_terms($query);
						if(!empty($terms)){
							echo
							"
								<div class='card-body'>
									<div class='badge bg-primary bg-gradient rounded-pill mb-2'>¿Quizás intentaste decir?</div>
									<div class='d-flex'>
										<div class='ms-3'>
							";
							
							echo "
							<form id='tmpForm' method='POST' onsubmit='submit()'>
								<input name='searchBox' type='hidden' value='{$terms}' class='form-control px-4' id='search' placeholder='Buscar...'>
								<input type='submit' class='fw-bold text-white' name='submitButton' value='{$terms}' style='background: transparent; border: none'>
							</form>
							";
							echo
							"
										</div>
									</div>
								</div>
							";
						}
					}
				?>
			</section>
			<section id='container' class='container'>
				<?php
					if ($_SERVER['REQUEST_METHOD'] == 'POST'
						&& isset($_POST['submitButton'])
						&& !empty($_POST['submitButton'])) {
							include_once 'scripts/queryScript.php';
					}
				?>
			</section>
			<section id='suggest' class='container'>
				<?php
					if ($_SERVER['REQUEST_METHOD'] == 'POST') {
						include_once 'scripts/Solr/querySolrFunctions.php';
						$query = $_POST['searchBox'];
						$arr_terms = get_suggest_terms($query);
						if(!empty($query) && !empty($arr_terms)){
							echo
							"
							<div class='card bg-light h-100'>
								<div class='card-body'>
									<div class='badge bg-primary bg-gradient rounded-pill mb-2'>Búsquedas relacionadas</div>
									<div class='d-flex'>
										<div class='ms-3'>
							";
							foreach ($arr_terms as $value) {
								echo
								"
								<form id='tmpForm' method='POST' onsubmit='submit()'>
									<input name='searchBox' type='hidden' value='{$value}' class='form-control px-4' id='search' placeholder='Buscar...'>
									<input type='submit' class='fw-bold' name='submitButton' value='{$value}' style='background: transparent; border: none'>
								</form>
								";
								include_once 'scripts/queryScript.php';
							}
							echo
							"
										</div>
									</div>
								</div>
							</div>
							";
						}
					}
				?>
			</section>
			<!-- Termina contenedor del script de PHP -->
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

	<!-- Script que sirve para evitar que el formulario se reenvíe al cargar la página -->
	<script type='text/javascript'>
		if (window.history.replaceState) {
		window.history.replaceState(null, null, window.location.href);
		}
	</script>
	<!--Termina Script-->

	<!-- Script para autocompletado -->
	<script>
		function autocomplete(inp, arr) {
		
		var currentFocus;
		
		inp.addEventListener("input", function(e) {
			var a, b, i, val = this.value;
			
			closeAllLists();
			if (!val) { return false;}
			currentFocus = -1;
			
			a = document.createElement("DIV");
			a.setAttribute("id", this.id + "autocomplete-list");
			a.setAttribute("class", "autocomplete-items");
			
			this.parentNode.appendChild(a);
			
			for (i = 0; i < arr.length; i++) {
				
				if (arr[i].substr(0, val.length).toUpperCase() == val.toUpperCase()) {
				
				b = document.createElement("DIV");
				
				b.innerHTML = "<strong>" + arr[i].substr(0, val.length) + "</strong>";
				b.innerHTML += arr[i].substr(val.length);
				
				b.innerHTML += "<input type='hidden' value='" + arr[i] + "'>";
				
				b.addEventListener("click", function(e) {
					
					inp.value = this.getElementsByTagName("input")[0].value;
					
					closeAllLists();
				});
				a.appendChild(b);
				}
			}
		});
		
		inp.addEventListener("keydown", function(e) {
			var x = document.getElementById(this.id + "autocomplete-list");
			if (x) x = x.getElementsByTagName("div");
			if (e.keyCode == 40) {
				
				currentFocus++;
				
				addActive(x);
			} else if (e.keyCode == 38) { //up
				
				currentFocus--;
				
				addActive(x);
			} else if (e.keyCode == 13) {
				
				e.preventDefault();
				if (currentFocus > -1) {
				
				if (x) x[currentFocus].click();
				}
			}
		});
		function addActive(x) {
			
			if (!x) return false;
			
			removeActive(x);
			if (currentFocus >= x.length) currentFocus = 0;
			if (currentFocus < 0) currentFocus = (x.length - 1);
			
			x[currentFocus].classList.add("autocomplete-active");
		}
		function removeActive(x) {
			
			for (var i = 0; i < x.length; i++) {
			x[i].classList.remove("autocomplete-active");
			}
		}
		function closeAllLists(elmnt) {
			
			var x = document.getElementsByClassName("autocomplete-items");
			for (var i = 0; i < x.length; i++) {
			if (elmnt != x[i] && elmnt != inp) {
				x[i].parentNode.removeChild(x[i]);
			}
			}
		}
		
		document.addEventListener("click", function (e) {
			closeAllLists(e.target);
		});
		}

		file = 'scripts/Solr/titles/autocomplete.txt';
		let allText;
		let rawFile = new XMLHttpRequest();
		rawFile.open("GET", file, false);
		rawFile.onreadystatechange = function ()
		{
			if(rawFile.readyState === 4)
			{
				if(rawFile.status === 200 || rawFile.status == 0)
				{
					allText = rawFile.responseText;
				}
			}
		}
		rawFile.send(null);

		autocomplete(document.getElementById("search"), allText.split('\n'));
	</script>
	<!--Termina Script-->
	<script type='text/javascript'>
		function submit(cadena){
			document.getElemenById("tmpForm").remove();
			document.getElementById("search").value = cadena;
		}
	</script>
	<?php
		include_once 'scripts/Solr/querySolrFunctions.php';
		file_title_documents();
	?>
</html>
