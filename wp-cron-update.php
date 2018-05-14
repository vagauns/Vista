<?php

require('../../../../wp-load.php');
require('./wp-cron-itens.php');

class Cronupdate extends Vehicleitens {


	private $itens = array();

	private $fields = '"Codigo","DataHoraAtualizacao","Categoria","Finalidade","FotoDestaque","FotoDestaquePequena","Status","Caracteristicas","InfraEstrutura","Moeda","Bairro","Cidade","Dormitorios","Suites","Vagas","AreaTotal","AreaPrivativa","ValorVenda","ValorLocacao","Latitude","Longitude",{"Foto":["ImagemCodigo","Foto","FotoPequena","Destaque","Ordem","Tipo"]}';

	public function __construct() 
	{ 
		$this->generateAllUpdatePosts();
	}

	//check for actualizations `tr_cron_insert_auto`
	public function generateUpdatePosts()
	{
		global $wpdb;
		$pf = $wpdb->prefix;

		return $wpdb->get_results("
			SELECT * FROM `{$pf}cron_insert_auto` WHERE `status` = 1 LIMIT 0, 5;
		");

	}

	//create new itens
	public function getItensForUpdate()
	{
		$query = $this->generateUpdatePosts();

		if( count( $query ) > 0 ) :
			foreach ( $query as $key => $car ):
				
				$item 		= $this->getEspecificProductsWithApi($this->fields, $car->homeId );

				if( is_array( $item ) && !empty( $item ) ) :

					$postId 	= $this->updateOldPost( $item[0], $car->postId ); //->
					$this->importUpdatedImages($item['Foto'], $car->postId, $car->homeId);

					$this->itens[$key]['postId'] 	= $car->postId;
					$this->itens[$key]['homeId'] 	= $car->homeId;
					$this->itens[$key]['images'] 	= count($images);
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
	public function generateAllUpdatePosts()
	{
		$this->getItensForUpdate();

		global $wpdb;
		$pf = $wpdb->prefix;

		if( count( $this->itens ) > 0 ):
			foreach ( $this->itens as $key => $post ) :

				$wpdb->update(
					"{$pf}cron_insert_auto",
					array(
						'modified'		=> strtotime( $post['item']['DataHoraAtualizacao'] ),
						'status' 		=> 2,
						'totalimages'	=> $post['images']
					),
					array(
						'homeId' 		=> $post['homeId'] 
					)
				);

			endforeach;
		endif;
	}

	public function importUpdatedImages($images, $postId, $homeId)
	{
		if( is_array($images) )
		{
			global $wpdb;
			$pf = $wpdb->prefix;

			foreach( $images as $key => $image ):

				$imageId 	= $image['ImagemCodigo'];

				$imgex = $wpdb->get_row("
					SELECT COUNT(id) AS total
					FROM `{$pf}cron_insert_image` 
					WHERE 
						`imageId` = '{$imageId}'
						AND `homeId` = '{$homeId}'
						AND `postId` = '{$postId}'
				");

				if ($imgex->total == 0 ) :

					if( !is_null($homeId) ):

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
					
					$wpdb->update(
						"{$pf}cron_insert_image",
						array( 'status' => 1 ),
						array( 'postId' => $postId )
					);


				endif;

			endforeach;
		}
	}

}

new Cronupdate;