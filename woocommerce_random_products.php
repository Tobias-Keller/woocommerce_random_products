<?php
/*
Plugin Name: Woocommerce random products
Version: 0.1.5
Requires at least: 5.5.0
Requires PHP: 7.0
Plugin URI: https://plugins.tobier.de
Author: Tobias Keller
Author URI: https://tobier.de
Description: create random products for woocommerce
Details URI: https://plugins.tobier.de/wp-content/uploads/2020/09/wrpReadme.txt
Icon1x: https://plugins.tobier.de/wp-content/uploads/2020/09/icon-128x128-1.png
Icon2x: https://plugins.tobier.de/wp-content/uploads/2020/09/icon-256x256-1.png
BannerHigh: https://plugins.tobier.de/wp-content/uploads/2020/09/banner-1544x500-1.png
BannerLow: https://plugins.tobier.de/wp-content/uploads/2020/09/banner-772x250-1.png
License:      GNU General Public License v2 or later
License URI:  http://www.gnu.org/licenses/gpl-2.0.html
*/
defined( 'ABSPATH' ) || die();

require_once plugin_dir_path( __FILE__ ) . 'lib/wp-package-updater/class-wp-package-updater.php';

$tobier_updater = new WP_Package_Updater(
 	'https://plugins.tobier.de',
 	wp_normalize_path( __FILE__ ),
	wp_normalize_path( plugin_dir_path( __FILE__ ) )
);

$createProducts = new woocommerce_random_products();

class woocommerce_random_products {
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'createAdminPage' ) );
	}

	public function createAdminPage(){
		add_menu_page(
			'Random Products',
			'Random Products',
			'manage_options',
			'random-products',
			array( $this, 'adminPageTemplate' )
		);
	}

	public function adminPageTemplate(){
		if ( isset( $_POST['tk_submit'] ) && wp_verify_nonce( $_POST['tk_random_products'], basename(__FILE__) ) ) {

			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}

			$count = intval( $_POST['number'] );
			$products = $this->createProducts( $count );
			echo $products;
		}
		?>
		<form method="post">
			<?php wp_nonce_field( basename( __FILE__ ), 'tk_random_products' ); ?>

			<h3>Create random woocommerce products</h3>
			<p>Add a number to the input and create random products for testing...</p>
			<table class="form-table">
				<tbody>
				<tr>
					<th scope="row">
						<label for="number">Count of Products</label>
					</th>
					<td>
						<input type="number" min="1" max="300"  id="number" name="number">
					</td>
				</tr>
				</tbody>
			</table>
				<p class="submit"><input type="submit" value="Create Products" class="button-primary" name="tk_submit"></p>
		</form>
		<?php
	}

	private function createProducts( $count ){

		if ( $count >= 300 ){
			$count = 300;
		}

		$userID = get_current_user_id();

		$rounds = 1;
		while ( $rounds <= $count ) {
			$post = array(
				'post_author'  => $userID,
				'post_content' => $this->content,
				'post_excerpt' => $this->excerpt,
				'post_status'  => "publish",
				'post_title'   => 'product_' . wp_generate_password( 8, true ),
				'post_parent'  => '',
				'post_type'    => "product",
			);

			$postID = wp_insert_post( $post );

			if ( $postID ){
				update_post_meta( $postID, '_visibility', 'visible' );
				update_post_meta( $postID, '_stock_status', 'instock');
				update_post_meta( $postID, '_sku', 'pro' . rand( 100, 99999 ) );
				update_post_meta( $postID, 'total_sales', rand( 0 , 99999 ) );
				update_post_meta( $postID, '_downloadable', 'no');
				update_post_meta( $postID, '_virtual', 'yes');
				$price = rand( 1, 999999 );
				update_post_meta( $postID, '_regular_price', $price );
				update_post_meta( $postID, '_price', $price );
				if ( rand( 0, 1 ) ) {
					update_post_meta( $postID, '_featured', "yes" );
				} else {
					update_post_meta( $postID, '_featured', "no" );
				}
				update_post_meta( $postID, '_manage_stock', "no" );
				update_post_meta( $postID, '_backorders', "no" );
				update_post_meta( $postID, '_stock', "" );

				$image = $this->getRandomImage();
				set_post_thumbnail( $postID, $image );

				$category = $this->getRandomCategory();
				wp_set_object_terms( $postID, $category, 'product_cat' );
			}
			$rounds++;
		}

		return 'Anything was happend, maybe products.';
	}

	private function getRandomImage(){
		$args = array(
			'post_type'      => 'attachment',
			'orderby'        => 'rand',
			'posts_per_page' => '1',
		);
		$attachments = get_posts( $args );
		return $attachments[0]->ID;
	}

	private function getRandomCategory(){
		$args = array(
			'taxonomy'     => 'product_cat',
			'orderby'      => 'rand',
			'posts_per_page' => '1',
			'show_count'   => 0,
			'pad_counts'   => 0,
			'hierarchical' => 1,
			'title_li'     => '',
			'hide_empty'   => 0
		);
		$category = get_categories( $args );
		$randNum = array_rand( $category, 1 );
		return $category[$randNum]->term_id;
	}

	public $excerpt  = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat';
	public $content = 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Aenean commodo ligula eget dolor. Aenean massa. Cum sociis natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Donec quam felis, ultricies nec, pellentesque eu, pretium quis, sem. Nulla consequat massa quis enim. Donec pede justo, fringilla vel, aliquet nec, vulputate eget, arcu. In enim justo, rhoncus ut, imperdiet a, venenatis vitae, justo. Nullam dictum felis eu pede mollis pretium. Integer tincidunt. Cras dapibus. Vivamus elementum semper nisi. Aenean vulputate eleifend tellus. Aenean leo ligula, porttitor eu, consequat vitae, eleifend ac, enim. Aliquam lorem ante, dapibus in, viverra quis, feugiat a, tellus. Phasellus viverra nulla ut metus varius laoreet. Quisque rutrum. Aenean imperdiet. Etiam ultricies nisi vel augue. Curabitur ullamcorper ultricies nisi. Nam eget dui. Etiam rhoncus. Maecenas tempus, tellus eget condimentum rhoncus, sem quam semper libero, sit amet adipiscing sem neque sed ipsum. Nam quam nunc, blandit vel, luctus pulvinar, hendrerit id, lorem. Maecenas nec odio et ante tincidunt tempus. Donec vitae sapien ut libero venenatis faucibus. Nullam quis ante. Etiam sit amet orci eget eros faucibus tincidunt. Duis leo. Sed fringilla mauris sit amet nibh. Donec sodales sagittis magna. Sed consequat, leo eget bibendum sodales, augue velit cursus nunc';
}