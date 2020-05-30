<?php
namespace WP_DG\Admin;

use WP_DG\Includes\Utile;

/**
 * Главная страница настроек в админке
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
			'settings-wp_dg',
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
				do_settings_sections( "settings-wp_dg" );
				submit_button();

		echo "	</form>
			  </div>";
	}

	/**
	 * Контент настроек
	 */
	public static function settingsPageContent() :void
	{
		// Заносим в $file_path_array список php файлов темы
		$file_path_array = array_filter(list_files(TEMPLATEPATH), function ($file_path) {
			return (stripos($file_path, '.php') !== false);
		});

		foreach ($file_path_array as $file_path) {

			$file = str_replace(TEMPLATEPATH, '', $file_path);
			$file_name = Utile::prepareFileName($file);

			$fields_data_array = [
				[
					'name' => 'Открывающее событие',
					'postfix' => '_opening_event',
					'type' => 'text',
					'desc'  => 'Введите открывающее событие.'
				],
				[
					'name' => 'Закрывающее событие',
					'postfix' => '_closing_event',
					'type' => 'text',
					'desc'  => 'Введите закрывающее событие.'
				],
				[
					'name' => 'Файл шапки для этого шаблона',
					'postfix' => '_header_file',
					'type' => 'text',
					'desc'  => 'Введите путь до файла шапки от корня темы.'
				],
				[
					'name' => 'Файл футера для этого шаблона',
					'postfix' => '_footer_file',
					'type' => 'text',
					'desc'  => 'Введите путь до файла футера от корня темы.'
				],
			];

			add_settings_section(
				$file_name.'_section',
				$file,
				'',
				'settings-wp_dg'
			);

			// Регистрация и вывод полей
			foreach ($fields_data_array as $field_data) {
				self::addingFields($file_name, $field_data);
			}
		}
	}

	/**
	 * Регистрация полей
	 *
	 * @param string $file_name
 	 * @param string $field_data
	 */
	private static function addingFields(string $file_name, array $field_data) :void
	{
		add_settings_field(
			$file_name . $field_data['postfix'],
			$field_data['name'],
			array( __CLASS__, 'displayFields' ),
			'settings-wp_dg',
			$file_name.'_section',
			[
				'name' => $file_name . $field_data['postfix'],
				'type' => $field_data['type'],
				'desc'  => $field_data['desc']
			]
		);

		register_setting( 'settings_wp_dg', $file_name . $field_data['postfix'] );
	}

	/**
	 * Вывод полей
	 *
	 * @param array $args
	 */
	public static function displayFields(array $args) :void
	{
		extract( $args );
	  $value = get_option($name);

	  switch ( $type ) {
			case 'text':
				echo "<input class='regular-text' type='text' id='{$name}' name='{$name}' value='{$value}' />";
			break;
	    case 'textarea':
	      echo "<textarea class='code large-text' rows='3' type='text' id='{$name}' name='{$name}'>{$value}</textarea>";
	    break;
	  }
	  // Если есть описание поля, то вывести его
	  echo ($desc != '') ? "<br /><span class='description'>$desc</span>" : "";
	}

}
