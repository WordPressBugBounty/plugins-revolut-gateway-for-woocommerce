<?php
/**
 * Settings navigation template.
 *
 * @package    WooCommerce
 * @category   Payment Gateways
 * @author     Revolut
 * @since      1.0.0
 */

global $current_section;
$revolut_configuration_tabs = apply_filters( 'wc_revolut_settings_nav_tabs', array() );
$last                       = count( $revolut_configuration_tabs );
$idx                        = 0;
$tab_active                 = false;
?>

<div class="wc-revolut-settings-header">
	<img class="revolut-logo" src="<?php echo esc_url( 'https://assets.revolut.com/assets/banks/Revolut.svg' ); ?>" alt="Revolut Logo" />
	<span class="revolut-title">Revolut Payment Gateway Settings</span>
</div>

<div class="revolut-settings-nav">
	<?php
	foreach ( $revolut_configuration_tabs as $tab_id => $rev_tab ) :
		$idx ++;
		?>
		<a class="nav-tab 
		<?php
		if ( $current_section === $tab_id || ( ! $tab_active && $last === $idx ) ) {
			echo 'nav-tab-active';
			$tab_active = true;
		}
		?>
		" href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=checkout&section=' . $tab_id ) ); ?>"><?php echo esc_attr( $rev_tab ); ?></a>
	<?php endforeach; ?>
</div>
<div class="clear"></div>
