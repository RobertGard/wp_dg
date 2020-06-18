<?php

/**
 * @link              https://de-gard.ru
 * @since             1.1.0
 * @package           WP_DG
 *
 * @wordpress-plugin
 * Plugin Name:       WP DG
 * Plugin URI:        https://de-gard.ru
 * Description:       Select the desired areas and make them editable.
 * Version:           1.1.0
 * Author:            DE-GARD
 * Author URI:        https://de-gard.ru
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       wp_dg
 * Domain Path:       /languages
 */

	/** Added Сomposer autoload **/
	require_once plugin_dir_path( __FILE__ ) . 'vendor/autoload.php';
	/**  Указываем зависимости от плагинов **/
	require_once plugin_dir_path( __FILE__ ) . 'tgm-plugin-dependency.php';
	/**  Список функций которые используются для вывода данных в тему **/
	require_once plugin_dir_path( __FILE__ ) . 'theme-insertions.php';

	// Если этот файл называется напрямую, отменить.
	if ( ! defined( 'WPINC' ) ) {
		die;
	}

	/**
	 * Код, который выполняется при активации плагина.
	 * Это действие описано в файле includes/WPDG_Activator.php
	 */
	register_activation_hook( __FILE__, function () {
		WP_DG\Includes\WPDG_Activator::activate();
	} );

	/**
	 * Код, который выполняется при деактивации плагина.
	 * Это действие описано в файле in includes/WPDG_Deactivator.php
	 */
	register_deactivation_hook( __FILE__, function () {
		WP_DG\Includes\WPDG_Deactivator::deactivate();
	} );

	/*** Проверка, подключен ли Advanced Custom Fields ***/
	if (in_array('advanced-custom-fields/acf.php', (array) get_option('active_plugins', array()))) {

		/**
		 *  Инициализация самого главного объекта
		 */
		$wp_dg = WP_DG\Includes\WPDG::getInstance();

		/**
		 * Замена/обёртка функции get_header()
		 *
		 * @param string $currentFile - текущий файл, в котором вызывается функция
		 * @param array $eventsAndFiles - Файлы и события, в к оторых они вызываются
		 */
		add_action('wp_head', function () use ($wp_dg) {

			$wp_dg->run();

		});

	}
