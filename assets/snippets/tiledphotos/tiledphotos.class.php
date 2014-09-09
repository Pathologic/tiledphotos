<?php
class themePacific_Jetpack_Tiled_Gallery_Shape {
	static $shapes_used = array();
	protected $modx = null;
	public function __construct( $images, $modx ) {
		$this->images = $images;
		$this->images_left = count( $images );
		$this->modx = $modx;
	}
	public function wp_list_pluck( $list, $field, $index_key = null ) {
		if ( ! $index_key ) {
		/*
		 * This is simple. Could at some point wrap array_column()
		 * if we knew we had an array of arrays.
		 */
			foreach ( $list as $key => $value ) {
				if ( is_object( $value ) ) {
					$list[ $key ] = $value->$field;
				} else {
					$list[ $key ] = $value[ $field ];
				}
			}
			return $list;
		}
			/*
		 * When index_key is not set for a particular item, push the value
		 * to the end of the stack. This is how array_column() behaves.
		 */
			$newlist = array();
			foreach ( $list as $value ) {
				if ( is_object( $value ) ) {
					if ( isset( $value->$index_key ) ) {
						$newlist[ $value->$index_key ] = $value->$field;
					} else {
						$newlist[] = $value->$field;
					}
				} else {
					if ( isset( $value[ $index_key ] ) ) {
						$newlist[ $value[ $index_key ] ] = $value[ $field ];
					} else {
						$newlist[] = $value[ $field ];
					}
				}
			}

				return $newlist;
			}
		public static function get_rounded_constrained_array( $bound_array, $sum = false ) {
		// Convert associative arrays before working with them and convert them back before returning the values
		$keys        = array_keys( $bound_array );
		$bound_array = array_values( $bound_array );

		$bound_array_int = self::get_int_floor_array( $bound_array );
		
		$lower_sum = array_sum( self::wp_list_pluck( $bound_array_int, 'floor' ) );
		if ( ! $sum || ( $sum < $lower_sum ) ) {
			// If value of sum is not supplied or is invalid, calculate the sum that the returned array is constrained to match
			$sum = array_sum( $bound_array );
		}
		$diff_sum = $sum - $lower_sum;
		
		self::adjust_constrained_array( $bound_array_int, $diff_sum );

		$bound_array_fin = self::wp_list_pluck( $bound_array_int, 'floor' );
		return array_combine( $keys, $bound_array_fin );
		}

		private static function get_int_floor_array( $bound_array ) {
			$bound_array_int_floor = array();
			foreach ( $bound_array as $i => $value ){
				$bound_array_int_floor[$i] = array(
					'floor'    => (int) floor( $value ),
					'fraction' => $value - floor( $value ),
					'index'    => $i,
				);
			}

			return $bound_array_int_floor;
		}

		private static function adjust_constrained_array( &$bound_array_int, $adjustment ) {
			usort( $bound_array_int, array( 'self', 'cmp_desc_fraction' ) );

			$start = 0;
			$end = $adjustment - 1;
			$length = count( $bound_array_int );

			for ( $i = $start; $i <= $end; $i++ ) {
				$bound_array_int[ $i % $length ]['floor']++;
			}

			usort( $bound_array_int, array( 'self', 'cmp_asc_index' ) );
		}

		private static function cmp_desc_fraction( $a, $b ) {
			if ( $a['fraction'] == $b['fraction'] )
				return 0;
			return $a['fraction'] > $b['fraction'] ? -1 : 1;
		}

		private static function cmp_asc_index( $a, $b ) {
			if ( $a['index'] == $b['index'] )
				return 0;
			return $a['index'] < $b['index'] ? -1 : 1;
		}
	
	public function sum_ratios( $number_of_images = 3 ) {
		return array_sum( array_slice( $this->wp_list_pluck( $this->images, 'ratio' ), 0, $number_of_images ) );
	}

	public function next_images_are_symmetric() {
		return $this->images_left > 2 && $this->images[0]->ratio == $this->images[2]->ratio;
	}

	public function is_not_as_previous( $n = 1 ) {
		return ! in_array( get_class( $this ), array_slice( self::$shapes_used, -$n ) );
	}

	public function is_wide_theme() {
		$content_width = $this->modx->event->params['width'];
		return $content_width > 900;
	}

	public static function set_last_shape( $last_shape ) {
		self::$shapes_used[] = $last_shape;
	}

	public static function reset_last_shape() {
		self::$shapes_used = array();
	}
}

class themePacific_Jetpack_Tiled_Gallery_Three extends themePacific_Jetpack_Tiled_Gallery_Shape {
	public $shape = array( 1, 1, 1 );

	public function is_possible() {
		$ratio = $this->sum_ratios( 3 );
		return $this->images_left > 2 && $this->is_not_as_previous() &&
			( ( $ratio < 2.5 ) || ( $ratio < 5 && $this->next_images_are_symmetric() ) || $this->is_wide_theme() );
	}
}

class themePacific_Jetpack_Tiled_Gallery_Four extends themePacific_Jetpack_Tiled_Gallery_Shape {
	public $shape = array( 1, 1, 1, 1 );

	public function is_possible() {
		return $this->is_not_as_previous() && $this->sum_ratios( 4 ) < 3.5 &&
			( $this->images_left == 4 || ( $this->images_left != 8 && $this->images_left > 5 ) );
	}
}

class themePacific_Jetpack_Tiled_Gallery_Five extends themePacific_Jetpack_Tiled_Gallery_Shape {
	public $shape = array( 1, 1, 1, 1, 1 );

	public function is_possible() {
		return $this->is_wide_theme() && $this->is_not_as_previous() && $this->sum_ratios( 5 ) < 5 &&
			( $this->images_left == 5 || ( $this->images_left != 10 && $this->images_left > 6 ) );
	}
}

class themePacific_Jetpack_Tiled_Gallery_Two_One extends themePacific_Jetpack_Tiled_Gallery_Shape {
	public $shape = array( 2, 1 );

	public function is_possible() {
		return $this->is_not_as_previous( 3 ) && $this->images_left >= 2 &&
			$this->images[2]->ratio < 1.6 && $this->images[0]->ratio >=0.9 && $this->images[1]->ratio >= 0.9;
	}
}

class themePacific_Jetpack_Tiled_Gallery_One_Two extends themePacific_Jetpack_Tiled_Gallery_Shape {
	public $shape = array( 1, 2 );

	public function is_possible() {
		return $this->is_not_as_previous( 3 ) && $this->images_left >= 2 &&
			$this->images[0]->ratio < 1.6 && $this->images[1]->ratio >=0.9 && $this->images[2]->ratio >= 0.9;
	}
}

class themePacific_Jetpack_Tiled_Gallery_One_Three extends themePacific_Jetpack_Tiled_Gallery_Shape {
	public $shape = array( 1, 3 );

	public function is_possible() {
		return $this->is_not_as_previous() && $this->images_left >= 3 &&
			$this->images[0]->ratio < 0.8 && $this->images[1]->ratio >=0.9 && $this->images[2]->ratio >= 0.9 && $this->images[3]->ratio >= 0.9;
	}
}

class themePacific_Jetpack_Tiled_Gallery_Symmetric_Row extends themePacific_Jetpack_Tiled_Gallery_Shape {
	public $shape = array( 1, 2, 1 );

	public function is_possible() {
		return $this->is_not_as_previous() && $this->images_left >= 3 && $this->images_left != 5 &&
			$this->images[0]->ratio < 0.8 && $this->images[0]->ratio == $this->images[3]->ratio;
	}
}

class themePacific_Jetpack_Tiled_Gallery_Grouper {
	public $margin = 4;
	private $modx = null;
	public function __construct( $attachments, $modx ) {
		$content_width = isset($modx->event->params['width']) ? 5 + $modx->event->params['width'] : 500;
		$this->margin = isset($modx->event->params['margin']) ? $modx->event->params['margin'] : 4;
		$this->modx = $modx;
		$this->last_shape = '';
		$this->images = $this->get_images_with_sizes( $attachments );
		$this->grouped_images = $this->get_grouped_images();
		$this->apply_content_width( $content_width - 5 ); //reduce the margin hack to 5px. It will be further reduced when we fix more themes and the rounding error.
	}

	public function get_current_row_size() {
		$images_left = count( $this->images );
		if ( $images_left < 3 )
			return array_fill( 0, $images_left, 1 );

		foreach ( array( 'One_Three', 'One_Two', 'Five', 'Four', 'Three', 'Two_One', 'Symmetric_Row' ) as $shape_name ) {
			$class_name = "themePacific_Jetpack_Tiled_Gallery_$shape_name";
			$shape = new $class_name( $this->images, $this->modx );
			if ( $shape->is_possible() ) {
				themePacific_Jetpack_Tiled_Gallery_Shape::set_last_shape( $class_name );
				return $shape->shape;
			}
		}

		themePacific_Jetpack_Tiled_Gallery_Shape::set_last_shape( 'Two' );
		return array( 1, 1 );
	}

	public function wp_get_attachment_metadata( $image ) {
		$path = $this->modx->config['base_path'];
		$info = getimagesize($path.$image);
		$meta = array("width"=>$info[0],"height"=>$info[1]);
		return $meta;
	}

	public function get_images_with_sizes( $attachments ) {
		$images_with_sizes = array();

		foreach ( $attachments as $image ) {
			$temp = new stdClass();
			$meta  = $this->wp_get_attachment_metadata( $image[0] );
			$temp->post_title = $image[1];
			$temp->image_url = $image[0];
			$temp->width_orig = ( $meta['width'] > 0 )? $meta['width'] : 1;
			$temp->height_orig = ( $meta['height'] > 0 )? $meta['height'] : 1;
			$temp->ratio = $temp->width_orig / $temp->height_orig;
			$temp->ratio = $temp->ratio? $temp->ratio : 1;
			$images_with_sizes[] = $temp;
		}

		return $images_with_sizes;
	}

	public function read_row() {
		$vector = $this->get_current_row_size();

		$row = array();
		foreach ( $vector as $group_size ) {
			$row[] = new themePacific_Jetpack_Tiled_Gallery_Group( array_splice( $this->images, 0, $group_size ) );
		}

		return $row;
	}

	public function get_grouped_images() {
		$grouped_images = array();

		while( !empty( $this->images ) ) {
			$grouped_images[] = new themePacific_Jetpack_Tiled_Gallery_Row( $this->read_row() );
		}

		return $grouped_images;
	}

	// todo: split in functions
	// todo: do not stretch images
	public function apply_content_width( $width ) {
		foreach ( $this->grouped_images as $row ) {
			$row->width = $width;
			$row->raw_height = 1 / $row->ratio * ( $width - $this->margin * ( count( $row->groups ) - $row->weighted_ratio ) );
			$row->height = round( $row->raw_height );

			$this->calculate_group_sizes( $row );
		}
	}

	public function calculate_group_sizes( $row ) {
		// Storing the calculated group heights in an array for rounding them later while preserving their sum
		// This fixes the rounding error that can lead to a few ugly pixels sticking out in the gallery
		$group_widths_array = array();
		foreach ( $row->groups as $group ) {
			$group->height = $row->height;
			// Storing the raw calculations in a separate property to prevent rounding errors from cascading down and for diagnostics
			$group->raw_width = ( $row->raw_height - $this->margin * count( $group->images ) ) * $group->ratio + $this->margin;
			$group_widths_array[] = $group->raw_width;
		}
		$rounded_group_widths_array = themePacific_Jetpack_Tiled_Gallery_Shape::get_rounded_constrained_array( $group_widths_array, $row->width );

		foreach ( $row->groups as $group ) {
			$group->width = array_shift( $rounded_group_widths_array );
			$this->calculate_image_sizes( $group );
		}
	}

	public function calculate_image_sizes( $group ) {
		// Storing the calculated image heights in an array for rounding them later while preserving their sum
		// This fixes the rounding error that can lead to a few ugly pixels sticking out in the gallery
		$image_heights_array = array();
		foreach ( $group->images as $image ) {
			$image->width = $group->width - $this->margin;
			// Storing the raw calculations in a separate property for diagnostics
			$image->raw_height = ( $group->raw_width - $this->margin ) / $image->ratio;
			$image_heights_array[] = $image->raw_height;
		}

		$image_height_sum = $group->height - count( $image_heights_array ) * $this->margin;
		$rounded_image_heights_array = themePacific_Jetpack_Tiled_Gallery_Shape::get_rounded_constrained_array( $image_heights_array, $image_height_sum );

		foreach ( $group->images as $image ) {
			$image->height = array_shift( $rounded_image_heights_array );
		}
	}
}

class themePacific_Jetpack_Tiled_Gallery_Row {
	public function __construct( $groups ) {
		$this->groups = $groups;
		$this->ratio = $this->get_ratio();
		$this->weighted_ratio = $this->get_weighted_ratio();
	}

	public function get_ratio() {
		$ratio = 0;
		foreach ( $this->groups as $group ) {
			$ratio += $group->ratio;
		}
		return $ratio > 0? $ratio : 1;
	}

	public function get_weighted_ratio() {
		$weighted_ratio = 0;
		foreach ( $this->groups as $group ) {
			$weighted_ratio += $group->ratio * count( $group->images );
		}
		return $weighted_ratio > 0 ? $weighted_ratio : 1;
	}
}

class themePacific_Jetpack_Tiled_Gallery_Group {
	public function __construct( $images ) {
		$this->images = $images;
		$this->ratio = $this->get_ratio();
	}

	public function get_ratio() {
		$ratio = 0;
		foreach ( $this->images as $image ) {
			if ( $image->ratio )
				$ratio += 1/$image->ratio;
		}
		if ( !$ratio )
			return 1;

		return 1/$ratio;
	}
}