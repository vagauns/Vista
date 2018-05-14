<?php

require('../../../../wp-load.php');
require('./wp-cron-post-house.php');

define( 'FOLDER', dirname(__FILE__) . "/" );

class Vehicleitens extends VehiclePost {


	public function createNewPost($product)
	{

		global $wpdb;
		$pf = $wpdb->prefix;

		$date = date('Y-m-d H:i:s');
		$prospectHeading = $product["Categoria"] .' '. $product["Bairro"] .' '. $product["Cidade"];

		if( !is_null($product["Codigo"]) && $product["Codigo"] > 0  ):
			
			$wpdb->insert(
				"{$pf}posts",
				array(
					'post_title'    	=> $prospectHeading,
					'post_content'  	=> '',
					'post_status'   	=> 'pending',
					'post_date'			=> $date,
					'post_date_gmt'		=> $date,
					'post_modified'		=> $date,
					'post_modified_gmt'	=> $date,
					'post_author'   	=> 1,
					'post_name'			=> sanitize_title( $prospectHeading ),
					'post_type'			=> 'estate_property',
				)
			);

			$id = $wpdb->insert_id;
			$allItens = $this->createPostMetaWithCars( $product, $id );
			$this->insertCategoriesIntoVehicles( $product, $id );
			return $id;

		endif;

	}

	public function updateOldPost($product, $id)
	{
		$allItens = $this->updateOldPostMetaWithCars( $product, $id );
		$this->insertCategoriesIntoVehicles( $product, $id );
		return $id;
	}

    //get json by api
	public function getAllProductsWithApi($fields, $pagination, $limit)
	{
		$key         =  '192ce7c5fa15767dec253f3edab087b2';
		$url         =  'portanov17962-rest.vistahost.com.br/imoveis/listar?key=' . $key . '&showtotal=1&pesquisa={"fields":['.$fields.'],"paginacao":{"pagina":'.$pagination.',"quantidade":'.$limit.'}}';

		$ch = curl_init($url);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER , array( 'Accept: application/json' ) );
		$result = curl_exec( $ch );

		return $result = json_decode( $result, true );
	}

	public function getEspecificProductsWithApi($fields, $homeId)
	{
		$key         =  '192ce7c5fa15767dec253f3edab087b2';
		$url         =  'portanov17962-rest.vistahost.com.br/imoveis/detalhes?key=' . $key . '&imovel='.$homeId.'&pesquisa={"fields":['.$fields.']}';

		$ch = curl_init($url);
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );
		curl_setopt( $ch, CURLOPT_HTTPHEADER , array( 'Accept: application/json' ) );
		$result = curl_exec( $ch );

		return $result = json_decode( $result, true );
	}

	//get json by file
	public function getAllProductsWithFile($file)
	{
		$string = file_get_contents($file);
		$json = json_decode($string, true);
		return $json;
	}


}
