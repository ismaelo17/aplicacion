<?php
include("php/conexion.php");
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="UTF-8">
		<title>App</title>
		<link rel="stylesheet" type="text/css" href="css/estilo.css">
		<script src="https://code.jquery.com/jquery-2.2.4.js" integrity="sha256-iT6Q9iMJYuQiMWNd9lDyBUStIq/8PuOW33aOqmvFpqI=" crossorigin="anonymous"></script>
		<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js" integrity="sha256-T0Vest3yCU7pafRw9r+settMBX6JkKN06dqBnpQ8d30=" crossorigin="anonymous"></script>
		<!--Utilización de jQuery y jQuery UI-->
		<script>
			$(function(){
				var opciones = [];
				<?php for ($i=0; $i<count($array_php); $i++) { ?>
					opciones.push("<?php echo $array_php[$i] ?>");
				<?php } ?>
				$("#buscador").autocomplete({
					source: opciones
				});
			});
		</script> 
		<script src="http://maps.google.com/maps/api/js?sensor=false"></script>
		<!--Utilización de JavaScript puro-->
		<script>
			(function(){
				google.maps.event.addDomListener(window,'load',inicio);

				function inicio() {
					document.getElementById("boton").addEventListener("click",guardar,false);
					document.getElementById("buscador").addEventListener("keypress",function(e){
						if (e.keyCode==13) guardar();
					},false);
					//Posición central y zoom iniciales para mostrar toda España (GoogleMaps)
					var map = new google.maps.Map(document.getElementById('mapa'), {
						zoom: 6,
						center: new google.maps.LatLng(40.3070700,-3.6810800),
						mapTypeId: google.maps.MapTypeId.ROADMAP
					});
				}

				function guardar() {
					var provincia = document.getElementById("buscador").value;
					var provincias = [];
					var provinciaEscrita = "";
					<?php
					for ($i=0; $i<count($array_php); $i++) { ?>
						provincias.push("<?php echo $array_php[$i] ?>");
					<?php } ?>
					provincias.forEach(function(prov){
						if (prov==provincia) provinciaEscrita+=prov;
					});
					<?php
					for ($i=0; $i<count($array_php); $i++) { ?>
						if (provinciaEscrita=="<?php echo $array_php[$i] ?>") {
							//Consulta de los 5 municipios más poblados de la provincia seleccionada
							<?php
								$conection1 = mysql_connect("localhost","root","root") or die ("No se puede conectar");
								mysql_select_db("app");
								mysql_set_charset("utf-8",$conection1);
								$sql1 = "select * from municipios where codigo_provincia=$i+1";
								$res1 = mysql_query($sql1);
								$array_php1 = array();
								if (mysql_num_rows($res1)==0) {
									array_push($array_php1,"No hay datos");
								} else {
									while ($municipios=mysql_fetch_array($res1)) {
										array_push($array_php1,$municipios["municipio"],$municipios["latitud"],$municipios["longitud"]);
									}
								}
							?>
							//Construcción del array de arrays marcadores con los datos de los 5 municipios más poblados de la provincia seleccionada
							var marcadores = [];
							<?php
							$numeroMarcadores = count($array_php1)/3;
							$index = 0;
							?>
							<?php for ($j=0; $j<$numeroMarcadores; $j++) { ?>
								var marcador = [];
								<?php for ($k=$index; $k<$index+3; $k++) { ?>
									marcador.push("<?php echo $array_php1[$k] ?>");
								<?php } ?>
								marcadores.push(marcador);
								<?php $index+=3; ?>
							<?php } ?>
							//Cálculo de latitud y longitud media para determinar la posición central
							<?php
								$latitudMinima = $longitudMinima = 99999;
								$latitudMaxima = $longitudMaxima = -99999;
								for ($k=1; $k<count($array_php1); $k+=3) {
									if ($array_php1[$k]<$latitudMinima) $latitudMinima=$array_php1[$k];
									if ($array_php1[$k]>$latitudMaxima) $latitudMaxima=$array_php1[$k];
								}
								for ($l=2; $l<count($array_php1); $l+=3) {
									if ($array_php1[$l]<$longitudMinima) $longitudMinima=$array_php1[$l];
									if ($array_php1[$l]>$longitudMaxima) $longitudMaxima=$array_php1[$l];
								}
								if (count($array_php1)==3) {
									$latitudMedia = $latitudMinima;
									$longitudMedia = $longitudMinima;
								} else {
									$latitudMedia = ($latitudMinima+$latitudMaxima)/2;
									$longitudMedia = ($longitudMinima+$longitudMaxima)/2;
								}
							?>
							//Posición central y zoom (GoogleMaps)
							var map = new google.maps.Map(document.getElementById('mapa'), {
								zoom: 8,
								center: new google.maps.LatLng("<?php echo $latitudMedia ?>","<?php echo $longitudMedia ?>"),
								mapTypeId: google.maps.MapTypeId.ROADMAP
							});
							//Marcadores de los municipios (GoogleMaps)
							var infowindow = new google.maps.InfoWindow();
							var marker;
							marcadores.forEach(function(marcador,index){
								marker = new google.maps.Marker({
									position: new google.maps.LatLng(marcador[1],marcador[2]),
									map: map
								});
								google.maps.event.addListener(marker,"click",(function(marker,index){
									return function() {
										infowindow.setContent(marcador[0]);
										infowindow.open(map,marker);
									}
								})(marker,index));
							});
						}
					<?php } ?>
				}
			}());
		</script>
	</head>
	<body>
		<div>
			<center>
				<h1>Buscador de provincias</h1>
				<p>
					<input type="text" id="buscador" name="provincia">
					<input type="button" id="boton" value="Buscar">
				</p>
			</center>
		</div>
		<div id="mapa"></div>
	</body>
</html>