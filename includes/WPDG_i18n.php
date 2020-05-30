<?php
namespace WP_DG\Includes;

/**
 * Определите функциональность интернационализации.
 *
 * Загружает и определяет файлы интернационализации для этого плагина, чтобы он был готов к переводу.
 *
 * @since      1.0.0
 * @package    WP_DG
 * @subpackage WP_DG/Includes
 * @author     DE-GARD <info@de-gard.ru>
 */
class WPDG_i18n {


	/**
	 * Загрузите плагин текстового домена для перевода.
	 *
	 * @since    1.0.0
	 */
	public function loadPluginTextDomain() {

		load_plugin_textdomain(
			'wp-dg',
			false,
			dirname( dirname( plugin_basename( __FILE__ ) ) ) . '/languages/'
		);

	}



}
