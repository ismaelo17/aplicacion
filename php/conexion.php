<?php
	$conection = mysql_connect("localhost","root","root") or die ("No se puede conectar");
	mysql_select_db("app");
	mysql_set_charset("utf-8",$conection);
	$sql = "select provincia from provincias order by codigo_provincia";
	$res = mysql_query($sql);
	$array_php = array();
	if (mysql_num_rows($res)==0) {
		array_push($array_php,"No hay datos");
	} else {
		while ($provincias=mysql_fetch_array($res)) {
			array_push($array_php,$provincias["provincia"]);
		}
	}
?>