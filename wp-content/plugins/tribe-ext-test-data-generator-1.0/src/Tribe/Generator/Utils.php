<?php
namespace Tribe\Extensions\Test_Data_Generator\Generator;

use Exception;
use Faker\Factory;

class Utils {

	/**
	 * Upload random images into Media Gallery
	 *
	 * @since 1.0.0
	 * @param int $quantity
	 * @param array $args
	 * @return mixed
	 */
	public function upload( $quantity = 1, array $args = [] ) {
		for ( $i = 0; $i < $quantity; $i++ ) {
			$image_url = 'https://picsum.photos/640/360' . '#' . bin2hex(random_bytes(16));
			$uploads[] = tribe_upload_image($image_url);
		}
		return $uploads;
	}

	/**
	 * Clear Events-related data generated by this extension.
	 *
	 * @since 1.0.0
	 * @param $clear_flag
	 * @return boolean
	 */
	public function clear_generated( $clear_flag ) {
		if( $clear_flag == 'on' ) {
			while( tribe_venues()->by( 'meta_like', 'tribe_test_data_gen' )->found() ) {
				tribe_venues()->by( 'meta_like', 'tribe_test_data_gen' )->delete();
			}
			while( tribe_organizers()->by( 'meta_like', 'tribe_test_data_gen' )->found() ) {
				tribe_organizers()->by( 'meta_like', 'tribe_test_data_gen' )->delete();
			}
			while( tribe_events()->by( 'meta_like', 'tribe_test_data_gen' )->found() ) {
				tribe_events()->by( 'meta_like', 'tribe_test_data_gen' )->delete();
			}
		}
		return true;
	}

	/**
	 * Clear ALL Events-related data existing on the site.
	 *
	 * @since 1.0.0
	 * @param $clear_flag
	 * @return boolean
	 */
	public function clear_all( $clear_flag ) {
		if( $clear_flag == 'on' ) {
			while( tribe_venues()->found() ) {
				tribe_venues()->delete();
			}
			while( tribe_organizers()->found() ) {
				tribe_organizers()->delete();
			}
			while( tribe_events()->found() ) {
				tribe_events()->delete();
			}
		}
		return true;
	}
}
