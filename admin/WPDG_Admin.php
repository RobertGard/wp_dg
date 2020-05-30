<?php
namespace WP_DG\Admin;

use WP_DG\Includes\Utile;

/**
 * Специфичная для администратора функциональность плагина.
 *
 * Определяет имя подключаемого модуля, версию
 * и два примера хуков для постановки в очередь
 * специфичной для администратора таблицы стилей и JavaScript.
 *
 * @package    WP_DG
 * @subpackage WP_DG/Admin
 * @author     DE-GARD <info@de-gard.ru>
 */
class WPDG_Admin
{

	/**
	 * Инициализируйте класс и установите его свойства.
	 *
	 * @since    1.0.0
	 */
	public function __construct()
	{

	}

	/**
	 * Добавление страницы настроек
	 */
	public static function addSettingsPage() :void
	{
		add_menu_page(
			'Настройки WP DG',
			'Настройки WP DG',
			'manage_options',
			'settings-wp-dg',
			array( __CLASS__, 'settingsPageWrapper' ),
			'dashicons-admin-generic',
			61
		);
	}

	/**
	 * Обёртка для контента настроек
	 */
	public static function settingsPageWrapper() :void
	{
		echo "<div class=\"wrap\">
				<h2>Настройки плагина WP DG</h2>
				<form method=\"post\" enctype=\"multipart/form-data\" action=\"options.php\">";

				settings_fields( 'settings_wp_dg' );
				do_settings_sections( "settings-wp-dg" );
				submit_button();

		echo "	</form>
			  </div>";
	}

	/**
	 * Контент настроек
	 */
	public static function settingsPageContent() :void
	{
		$array_path_file = array_filter(list_files(TEMPLATEPATH), function ($path_file) {
			return (stripos($path_file, ".php") !== false) ?? true;
		});

		foreach ($array_path_file as $path_file) {

			$file = str_replace(TEMPLATEPATH, '', $path_file);
			$name_file = Utile::prepareFileName($file);


			add_settings_section(
				$name_file.'_section',
				$file,
				'',
				'settings-wp-dg'
			);

			self::addingEventFields($name_file);

			self::addingHeaderAndFooterFields($name_file);
		}
	}

	/**
	 * Добавление полей событий
	 *
	 * @param string $name_file
	 */
	private static function addingEventFields(string $name_file) :void
	{
		add_settings_field(
			$name_file.'_opening_event',
			'Открывающее событие',
			array( __CLASS__, 'displayFields' ),
			'settings-wp-dg',
			$name_file.'_section',
			[
				'name' => $name_file.'_opening_event',
				'type' => 'text',
				'desc'  => 'Введите открывающее событие.',
			]
		);

		add_settings_field(
			$name_file.'_closing_event',
			'Закрывающее событие',
			array( __CLASS__, 'displayFields' ),
			'settings-wp-dg',
			$name_file.'_section',
			[
				'name' => $name_file.'_closing_event',
				'type' => 'text',
				'desc'  => 'Введите закрывающее событие.',
			]
		);

		register_setting( 'settings_wp_dg', $name_file.'_opening_event' );
		register_setting( 'settings_wp_dg', $name_file.'_closing_event' );
	}

	/**
	 * Добавление полей для header и footer
	 *
	 * @param string $name_file
	 */
	private static function addingHeaderAndFooterFields(string $name_file) :void
	{
		add_settings_field(
			$name_file.'_header_file',
			'Файл шапки для этого шаблона',
			array( __CLASS__, 'displayFields' ),
			'settings-wp-dg',
			$name_file.'_section',
			[
				'name' => $name_file.'_header_file',
				'type' => 'text',
				'desc'  => 'Введите путь до файла шапки от корня темы.',
			]
		);

		add_settings_field(
			$name_file.'_footer_file',
			'Файл футера для этого шаблона',
			array( __CLASS__, 'displayFields' ),
			'settings-wp-dg',
			$name_file.'_section',
			[
				'name' => $name_file.'_footer_file',
				'type' => 'text',
				'desc'  => 'Введите путь до файла футера от корня темы.',
			]
		);

		register_setting( 'settings_wp_dg', $name_file.'_header_file' );
		register_setting( 'settings_wp_dg', $name_file.'_footer_file' );
	}


	public static function displayFields($args){
		$value = get_option($args['name']);

		echo "<input class='regular-text' type='text' id='{$args['name']}' name='{$args['name']}' value='{$value}' />";
	}

}
