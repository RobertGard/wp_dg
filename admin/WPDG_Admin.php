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
	 * @since    1.1.0
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
				[
					'name' => 'Отображать группу полей, если',
					'postfix' => '_location_rule_param',
					'type' => 'select',
					'select_options' => [
						[
							'option_group' => 'Запись'
						],
						[
							'option_value' => 'post_type',
							'option_text' => 'Тип записи'
						],
						[
							'option_value' => 'post_template',
							'option_text' => 'Шаблон записи'
						],
						[
							'option_value' => 'post',
							'option_text' => 'Запись'
						],
						[
							'option_group' => 'Страница'
						],
						[
							'option_value' => 'page_type',
							'option_text' => 'Тип страницы'
						],
						[
							'option_value' => 'page_template',
							'option_text' => 'Шаблон страницы'
						],
						[
							'option_value' => 'page',
							'option_text' => 'Страница'
						]
					],
					'desc'  => 'Выберите при каких условия будут отображаться ваши поля.'
				],
				[
					'name' => '',
					'postfix' => '_location_rule_operator',
					'type' => 'select',
					'select_options' => [
						[
							'option_value' => '==',
							'option_text' => 'равно'
						],
						[
							'option_value' => '!=',
							'option_text' => 'не равно'
						]
					]
				],
				[
					'name' => 'Значение',
					'postfix' => '_location_rule_value',
					'type' => 'text'
				]
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
 	 * @param array $field_data
	 */
	private static function addingFields(string $file_name, array $field_data) :void
	{
		$field_data['code'] = $file_name . $field_data['postfix'];

		add_settings_field(
			$field_data['code'],
			$field_data['name'],
			array( __CLASS__, 'displayFields' ),
			'settings-wp_dg',
			$file_name.'_section',
			$field_data
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
		/**
		 * @var string $name
		 * @var string $code
		 * @var string $type
		 * @var string $desc
		 * @var string $postfix
		 * @var array $select_options
		 */
		extract( $args );
		$value = get_option($code);

		switch ( $type ) {
			case 'text':
				echo "<input class='regular-text' type='text' id='{$code}' name='{$code}' value='{$value}' />";
			break;
			case 'textarea':
			  echo "<textarea class='code large-text' rows='3' type='text' id='{$code}' name='{$code}'>{$value}</textarea>";
			break;
			case 'select':
				echo "<select id=\"{$code}\" name=\"{$code}\">";
				if (isset($select_options)) {
					$option_group_closing = false;
					foreach ( $select_options as $select_option ) {
						// Открытие и закрытие <optgroup>
						if ( isset( $select_option['option_group'] ) ) {
							if ($option_group_closing) {
								echo '</optgroup>';
								$option_group_closing = false;
							}
							echo "<optgroup label=\"{$select_option['option_group']}\">";
							$option_group_closing = true;
						} else {
							$selected = ($value === $select_option['option_value']) ? 'selected="selected"' : '';
							echo "<option value=\"{$select_option['option_value']}\" {$selected}>
										{$select_option['option_text']}
								  </option>";
						}
					}
					if ($option_group_closing) echo '</optgroup>';
				}
				echo '</select>';
			break;
		}
		// Если есть описание поля, то вывести его
		if (isset($desc) && $desc != '') echo "<br><span class='description'>$desc</span>";
	}

  /**
   * Подключение стилей и скритов js
   */
  public static function enqueueScriptsAndStyles() :void
  {
    // CSS
    wp_enqueue_style('wp_dg__admin-style', plugins_url('wp_dg/admin/css/wp_dg__admin-style.css'), [], null, 'all');
    // JS
    wp_enqueue_script('wp_dg__admin-script', plugins_url('wp_dg/admin/js/wp_dg__admin-script.js'), [], null, true);
  }


}
