<?php 

namespace Hcode\Model;
use \Hcode\DB\Sql;
use \Hcode\Model;
use \Hcode\Mailer;


class Category extends Model {

	public static function listAll(){
		$sql = new Sql();
		$result = $sql->select("SELECT * from tb_categories order by descategory");	
		return $result ;		
	}

	public function save()
	{
		$sql = new Sql();
		$results = $sql->select("CALL sp_categories_save(:idcategory, :descategory)", array(
			":idcategory"=>$this->getidcategory(),
			":descategory"=>$this->getdescategory()
			));
		$this->setData($results[0]);
		Category::updateFile(); // se excluir vamos chamar essa rotina que vai atualizar no rodape da pagina a relação de categorias		
	}
	public function get($idcategory)
	{
		$sql = new Sql();
		$results = $sql->select("SELECT * from tb_categories where idcategory = :idcategory", [
			':idcategory'=>$idcategory]);
		// coloca o objeto (depois vamos excluir, alterar etc)
		$this->setData($results[0]);

	}
	public function delete(){
		// o objeto ja esta carregado
		$sql = new Sql();
		$sql->query("DELETE from tb_categories where idcategory = :idcategory", [
			':idcategory'=>$this->getidcategory()
			]);
		Category::updateFile(); // se excluir vamos chamar essa rotina que vai atualizar no rodape da pagina a relação de categorias
	}
	public static function updateFile(){
		// esta função serve para atualizar uma parte do codigo html do rodape da pagina principal, onde mostra uma lista com as categorias, vai ficar um misto de dinamico com statico, pois cada vez que cria ou altera uma categoria, vai incluir num html, e esse html vai ser incluido autommaticamente no principal
		$categories = Category::listAll(); // traz todas as categorias
		$html = []; // cria variavel do tipo array
		foreach ($categories as $row) {
			// cada registro do banco de dados esta na variavel $row
			array_push($html, '<li><a href="/categories/'.$row['idcategory'].'"">'.$row['descategory'].'</a></li>');
		}
		// vamos criar o arquivo categories-menu.html ( la tem os itens da tabela categoria e tambel um link para a categoria)
		file_put_contents($_SERVER['DOCUMENT_ROOT'].DIRECTORY_SEPARATOR."views".DIRECTORY_SEPARATOR."categories-menu.html", implode('', $html));
		// implode('', $html) = converte o array html em uma string

	}
    // traz todos os produtos relacionados a categoria
    // ou traz os nao relacionados
	public function getProducts($related = true){
		$sql = new Sql();
		if ($related === true){
			return $sql->select("SELECT * from tb_products where idproduct  IN(
					SELECT a.idproduct  FROM tb_products a
					inner join tb_productscategories b on a.idproduct = b.idproduct
					where b.idcategory = :idcategory); ",
					[':idcategory'=>$this->getidcategory()]);
		} else {
			return $sql->select("SELECT * from tb_products where idproduct  not IN(
					SELECT a.idproduct  FROM tb_products a
					inner join tb_productscategories b on a.idproduct = b.idproduct
					where b.idcategory = :idcategory); ",
					[':idcategory'=>$this->getidcategory()]);


		}
	}



	public function getProductsPage($page = 1, $itemsPerPage = 3){
		$start = ($page - 1) * $itemsPerPage;
		$sql = new Sql();
		$results = $sql->select("
				SELECT SQL_CALC_FOUND_ROWS  * FROM tb_products a
				inner join tb_productscategories b on a.idproduct = b.idproduct
			    inner join tb_categories c on c.idcategory = b.idcategory
				where C.idcategory = :idcategory
			    limit $start , $itemsPerPage;", [
                   ':idcategory'=>$this->getidcategory()
			    ]);  // 

                                       
		$resultTotal = $sql->select("select found_rows() as nrtotal;;");
//$resultTotal = $sql->select("SELECT count(*)  FROM tb_products a
//				inner join tb_productscategories b on a.idproduct = b.idproduct
//			    inner join tb_categories c on c.idcategory = b.idcategory
//				where C.idcategory = 5;");


		return [
			'data'=>Product::checkList($results),
			'total'=>(int)$resultTotal[0]["nrtotal"],
			'pages'=>ceil($resultTotal[0]["nrtotal"]/$itemsPerPage)];
	}
	public function addProduct(Product $product){
		$sql = new Sql();
		$sql->query("INSERT INTO tb_productscategories (idcategory, idproduct) Values (:idcategory, :idproduct)", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
			]);

	}

	public function removeProduct(Product $product){
		$sql = new Sql();
		$sql->query("DELETE FROM tb_productscategories where idcategory = :idcategory and idproduct = :idproduct", [
			':idcategory'=>$this->getidcategory(),
			':idproduct'=>$product->getidproduct()
			]);

	}

}

?>