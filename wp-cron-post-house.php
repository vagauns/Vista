<?php

require('../../../../wp-load.php');
require('./wp-cron-categories.php');

class VehiclePost extends VehicleCategories {

	public function insertCategoriesIntoVehicles($product, $postId)
	{
		$this->initQTCategory($product, $postId);
	}

	public function createPostMetaWithCars( $product, $postId )
	{
		$preco = ( $product['Finalidade']=='Venda' ) ? $product['ValorVenda'] : $product["ValorLocacao"];
		$label = ( $product['Finalidade']=='Venda' ) ? " à combinar" : " por mês";

		add_post_meta( $postId, "DataHoraAtualizacao", strtotime($product["DataHoraAtualizacao"]) );
		add_post_meta( $postId, "HouseCodigo", $product["Codigo"] );
		add_post_meta( $postId, "property_price", $preco );
		add_post_meta( $postId, "property_label", $label );
		add_post_meta( $postId, "property_country", "Brazil" );
		add_post_meta( $postId, "property_size", $product["AreaPrivativa"] );
		add_post_meta( $postId, "property_lot_size", $product["AreaTotal"] );
		add_post_meta( $postId, "property_rooms", "1" );
		add_post_meta( $postId, "property_bedrooms", $product["Dormitorios"] );
		add_post_meta( $postId, "property_bathrooms", "" );
		add_post_meta( $postId, "energy_index", "" );
		add_post_meta( $postId, "owner_notes", "" );
		add_post_meta( $postId, "energy_class", "" );
		add_post_meta( $postId, "property_status", "" );
		add_post_meta( $postId, "prop_featured", "" );
		add_post_meta( $postId, "property_theme_slider", "" );
		add_post_meta( $postId, "image_to_attach", "" );
		add_post_meta( $postId, "embed_video_id", "" );
		add_post_meta( $postId, "embed_virtual_tour", "" );
		add_post_meta( $postId, "property-year", "" );
		add_post_meta( $postId, "property-garage", $product["Vagas"] );
		add_post_meta( $postId, "property-garage-size", "" );
		add_post_meta( $postId, "property-date", "" );
		add_post_meta( $postId, "property-basement", "" );
		add_post_meta( $postId, "property-external-construction", "" );
		add_post_meta( $postId, "property-roofing", "" );
		add_post_meta( $postId, "property_latitude", $product["Latitude"] );
		add_post_meta( $postId, "property_longitude", $product["Longitude"] );
		add_post_meta( $postId, "page_custom_zoom", 15 );
		add_post_meta( $postId, "property_google_view", 1 );
		add_post_meta( $postId, "google_camera_angle", 1 );	

		$this->createAmenities( $product['Caracteristicas'], $postId );
		$this->createAmenities( $product['InfraEstrutura'], $postId );
	}

	/*

	*/



	public function updateOldPostMetaWithCars( $product, $postId )
	{

		$preco = ( $product['Finalidade']=='Venda' ) ? $product['ValorVenda'] : $product["ValorLocacao"];
		$label = ( $product['Finalidade']=='Venda' ) ? " à combinar" : " por mês";

		update_post_meta( $postId, "DataHoraAtualizacao", strtotime($product["DataHoraAtualizacao"]) );
		update_post_meta( $postId, "property_price", $preco );
		update_post_meta( $postId, "property_label", $label );
		update_post_meta( $postId, "property_country", "Brazil" );
		update_post_meta( $postId, "property_size", $product["AreaPrivativa"] );
		update_post_meta( $postId, "property_lot_size", $product["AreaTotal"] );
		update_post_meta( $postId, "property_rooms", "1" );
		update_post_meta( $postId, "property_bedrooms", $product["Dormitorios"] );
		update_post_meta( $postId, "property_bathrooms", "" );
		update_post_meta( $postId, "energy_index", "" );
		update_post_meta( $postId, "owner_notes", "" );
		update_post_meta( $postId, "energy_class", "" );
		update_post_meta( $postId, "property_status", "" );
		update_post_meta( $postId, "prop_featured", "" );
		update_post_meta( $postId, "property_theme_slider", "" );
		update_post_meta( $postId, "image_to_attach", "" );
		update_post_meta( $postId, "embed_video_id", "" );
		update_post_meta( $postId, "embed_virtual_tour", "" );
		update_post_meta( $postId, "property-year", "" );
		update_post_meta( $postId, "property-garage", $product["Vagas"] );
		update_post_meta( $postId, "property-garage-size", "" );
		update_post_meta( $postId, "property-date", "" );
		update_post_meta( $postId, "property-basement", "" );
		update_post_meta( $postId, "property-external-construction", "" );
		update_post_meta( $postId, "property-roofing", "" );
		update_post_meta( $postId, "property_latitude", $product["Latitude"] );
		update_post_meta( $postId, "property_longitude", $product["Longitude"] );
		update_post_meta( $postId, "page_custom_zoom", 15 );
		update_post_meta( $postId, "property_google_view", 1 );
		update_post_meta( $postId, "google_camera_angle", 1 );	

		$this->updateAmenities( $product['Caracteristicas'], $postId );
		$this->updateAmenities( $product['InfraEstrutura'], $postId );

	}

	public function createAmenities( $amenities, $postId )
	{
		foreach ($amenities as $key => $value) 
		{
			if( $value == 'Sim' ) {
				add_post_meta( $postId, $key, 1 );	
			}
		}
	}

	public function updateAmenities( $amenities, $postId )
	{
		foreach ($amenities as $key => $value) 
		{
			if( $value == 'Sim' ) {
				update_post_meta( $postId, $key, 1 );	
			}
		}
	}


}
