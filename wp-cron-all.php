<?php

require('../../../../wp-load.php');
require('./wp-cron-itens.php');
//require('./wp-import-images.php');

define( 'FOLDER', dirname(__FILE__) . "/" );

class Vehicless extends Vehicleitens {


	private $newItens = array();

	private $updateItens = array();

	private $limit = 4;

	private $pagination = 1;

	private $fields = '"Codigo","DataHoraAtualizacao","Categoria","Finalidade","FotoDestaque","FotoDestaquePequena","Status","Caracteristicas","InfraEstrutura","Moeda","Bairro","Cidade","Dormitorios","Suites","Vagas","AreaTotal","AreaPrivativa","ValorVenda","ValorLocacao","Latitude","Longitude"';


	private $file = FOLDER . "api.json";

	private $issetProd = true;

	//init all necessary methods
	public function init()
	{
		//first party
		$this->getAllProducts();
	}




	/* ==========================================================================================
		First party
	========================================================================================== */

	//function init search by products
	public function getAllProducts()
	{
		$i = 0;
		while( $this->issetProd == true )
		{
			$itens = $this->getAllProductsWithApi( $this->fields, $this->pagination, $this->limit );
			// $this->loopingItens($itens);
			// $this->issetProd = false;
			if( empty($itens) || count($itens) == 4 ) {
				$this->issetProd = false;
			} else {
				$this->pagination = $this->pagination + 1;
				$this->loopingItens($itens);
			}

			if( $this->pagination == 11 ){

				die();
			}
		}
	}

	//lopping
	public function loopingItens($itens)
	{
		//comeca o looping
		$this->unsetValues($itens);
		foreach ( $itens as $key => $product ) {
			//function to care itens
			$this->careItens($product);
		}
	}

	public function unsetValues($itens)
	{
		unset($product['total']);
		unset($product['paginas']);
		unset($product['pagina']);
		unset($product['quantidade']);
	}

	//function to care all itens
	public function careItens($product)
	{
		//insert all data if unatualized and unexistent
		$homeId 		= $product['Codigo'];
		$modified 	= strtotime($product['DataHoraAtualizacao']);

		if( !is_null($homeId) ):
			$this->careUpdate($homeId, $modified);
			$this->careInsert($homeId);
		endif;
		
	}

	public function careUpdate($homeId, $modified)
	{
		global $wpdb;
		$pf = $wpdb->prefix;

		$total = $wpdb->get_row("
			SELECT COUNT(`id`) AS count 
				FROM {$pf}cron_insert_auto 
				WHERE 
					`homeId` = '{$homeId}' 
					AND `modified` != '{$modified}' 
		"); 

		if( $total->count > 0 ) :
			$wpdb->update(
				"{$pf}cron_insert_auto",
				array( 
					'status'		=> 1
				),
				array(
					'homeId' 		=> $homeId
				)
			);
		endif;
	}

	public function careInsert($homeId)
	{
		global $wpdb;
		$pf = $wpdb->prefix;

		$total = $wpdb->get_row("SELECT COUNT(`id`) AS count FROM {$pf}cron_insert_auto WHERE `homeId` = '{$homeId}'");


		if( $total->count == 0 ) :
			$wpdb->insert(
				"{$pf}cron_insert_auto",
				array( 
					'homeId' 		=> $homeId, 
					'postId' 		=> 0,
					'totalImages' 	=> 0,
					'modified'		=> null,
					'status'		=> 0
				)
			);

		endif;
	}
	








	/* ==========================================================================================
		General Functions
	========================================================================================== */

	//function dump
	function dd()
	{
		array_map(function($x) { var_dump($x); }, func_get_args()); die;
	}


}

$goo = new Vehicless;
$goo->init();
