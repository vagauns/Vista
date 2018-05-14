<?php

require('../../../../wp-load.php');
require('./wp-cron-itens.php');

class Cronposts extends Vehicleitens {

	private $itens = array();

	private $fields = '"Codigo","DataHoraAtualizacao","Categoria","Finalidade","FotoDestaque","FotoDestaquePequena","Status","Caracteristicas","InfraEstrutura","Moeda","Bairro","Cidade","Dormitorios","Suites","Vagas","AreaTotal","AreaPrivativa","ValorVenda","ValorLocacao","Latitude","Longitude",{"Foto":["ImagemCodigo","Foto","FotoPequena","Destaque","Ordem","Tipo"]}';

	public function __construct() 
	{ 
		$this->generateTfAllPosts();
	}

	//check for actualizations `tr_cron_insert_auto`
	public function generateNewPosts()
	{
		global $wpdb;
		$pf = $wpdb->prefix;

		return $wpdb->get_results("
			SELECT * FROM `{$pf}cron_insert_auto`
			WHERE 
				( `modified` IS NULL AND `status` = 0 ) 
				OR ( `modified` = 0 AND `postId` = 0 AND `status` = 2 )
			LIMIT 0, 3;
		");
 		
	}

	//create new itens
	public function getItensForCreate()
	{
		$query = $this->generateNewPosts();

		if( count( $query ) > 0 ) :
			foreach ( $query as $key => $car ):


				$item 		= $this->getEspecificProductsWithApi($this->fields, $car->homeId );

				if( is_array( $item ) && !empty( $item ) ) :

					$postId 	= $this->createNewPost( $item );

					$this->importNewImages($item['Foto'], $postId, $car->homeId);

					$this->itens[$key]['postId'] 	= $postId;
					$this->itens[$key]['images'] 	= count($item['Foto']);
					$this->itens[$key]['homeId'] 	= $car->homeId;
					$this->itens[$key]['item'] 		= $item;

				else:

					global $wpdb;
					$pf = $wpdb->prefix;

					$wpdb->delete(
						"{$pf}cron_insert_auto",
						array(
							'homeId' => $car->homeId
						)
					);

				endif;
			endforeach;
		endif;
	}

	//update `tr_cron_insert_auto`
	public function generateTfAllPosts()
	{
		$this->getItensForCreate();

		global $wpdb;
		$pf = $wpdb->prefix;

		if( count( $this->itens ) > 0 ):
			foreach ( $this->itens as $key => $post ) :

				$wpdb->update(
					"{$pf}cron_insert_auto",
					array(
						'postId' 		=> $post['postId'],
						'totalImages' 	=> $post['images'],
						'modified'		=> strtotime( $post['item']['DataHoraAtualizacao'] ),
						'status' 		=> 2
					),
					array(
						'homeId' 		=> $post['homeId'] 
					)
				);

			endforeach;
		endif;
	}


	public function importNewImages($images, $postId, $homeId)
	{
		if( is_array($images) )
		{
			global $wpdb;
			$pf = $wpdb->prefix;

			foreach( $images as $key => $image ):

				var_dump($image);

				if( !is_null($image['Codigo']) ):
					$wpdb->insert(
						"{$pf}cron_insert_image",
						array(
							'homeId'  	=> $homeId,
							'postId'	=> $postId,
							'imageId' 	=> $image['ImagemCodigo'],
							'type' 	 	=> $image['Tipo'],
							'sortOrder'	=> $image['Ordem'],
							'oldUrl'	=> $image['Foto'],
							'destaque'	=> ($image['Destaque']=="Sim")? 1: 0,
							'status' 	=> 0
						)
					);
				endif;

			endforeach;
		}
	}

}

new Cronposts;