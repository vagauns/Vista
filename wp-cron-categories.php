<?php

require('../../../../wp-load.php');

class VehicleCategories {

	//estate_property
	//property_category

	public function initQTCategory($apiData, $postId)
	{
		$this->property_category($apiData, $postId);

		$this->property_action_category($apiData, $postId);

		$this->property_city($apiData, $postId);

		$this->property_area($apiData, $postId);

		//$this->property_county_state($apiData, $postId);

	}

	public function setQtCategori( $taxonomy, $postId, $term, $tag=false )
	{
		$termId = ( is_array($term) ) ? $term : $this->getTheRealTerm( $taxonomy, $term, null, $tag );
		wp_set_post_terms( $postId, $termId, $taxonomy );
	}

	public function getTheRealTerm( $taxonomy, $term, $parent=null, $tag=false )
	{
		if( $tag == true ){
			return array($term);
		} else {

			$termId = term_exists( $term, $taxonomy );

			if( is_null($termId) || !is_array($termId) )
			{
				$a = wp_insert_term( $term, $taxonomy, $parent );
				$termId = term_exists( $term, $taxonomy );
			}

			return array( $termId['term_id'] );
			
		}
	}

	public function property_category($apiData, $postId)
	{
		$termin = ( array_key_exists('Finalidade', $apiData ) ) ? $apiData['Finalidade'] : "";
		$this->setQtCategori( 'property_category', $postId, $termin );
	}

	public function property_action_category($apiData, $postId)
	{
		$termin = ( array_key_exists('Categoria', $apiData ) ) ? $apiData['Categoria'] : "";
		$this->setQtCategori( 'property_action_category', $postId, $termin );
	}

	public function property_city($apiData, $postId)
	{
		$termin = ( array_key_exists('Cidade', $apiData ) ) ? $apiData['Cidade'] : "";
		$this->setQtCategori( 'property_city', $postId, $termin );
	}

	public function property_area($apiData, $postId)
	{
		$termin = ( array_key_exists('Bairro', $apiData ) ) ? $apiData['Bairro'] : "";
		$this->setQtCategori( 'property_area', $postId, $termin );
	}

	public function property_county_state($apiData, $postId)
	{
		$termin = ( array_key_exists('DriveWheel', $apiData ) ) ? $apiData['DriveWheel'] : "";
		$this->setQtCategori( 'property_county_stat', $postId, $termin );
	}


}

