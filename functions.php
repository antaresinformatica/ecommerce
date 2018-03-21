<?php 

	function formatPrice(float $vlprice){
		
		// esta funcao obrigatoriamente recebe um parametro do tipo foat
		// e retorna o numero formatado para o padrao brasileiro
		return number_format($vlprice, 2 , ",", ".");
	}


 ?>