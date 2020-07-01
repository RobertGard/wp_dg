<?php
namespace WP_DG\Admin;

use WP_DG\Includes\Utile;
use MakeitWorkPress\WP_Custom_Fields\Framework;

/**
 * Главная страница настроек в админке
 *
 * @package    WP_DG
 * @subpackage WP_DG/Admin
 * @author     DE-GARD <info@de-gard.ru>
 */
class WPDG_Admin
{
  private $opt_name;
  private $settings;

	/**
	 * Инициализируйте класс и установите его свойства.
	 *
	 * @since    1.1.0
	 */
	public function __construct($settings) {
    $this->opt_name = 'wp_dg_option';
    $this->settings = $settings;
  }

	/**
	 * Добавление страницы настроек
	 */
	public function launch() :void
	{
    $fields = Framework::instance();

    $args = [
        'class'         => 'tabs-left',
        'id'            => $this->opt_name,
        'title'         => __('Настройки WP DG', 'wp-custom-fields'),
        'capability'    => 'manage_options',
        'menu_title'    => __('Настройки WP DG', 'wp-custom-fields'),
        'menu_icon'     => 'dashicons-admin-generic',
        'menu_position' => 99,
        'sections'      => [ $this->setSectionMainSettings() ]
    ];

    $fields->add('options', $args);
	}

  private function setSectionMainSettings()
  {
    return [
        'id'        => 'second_section',
        'title'     => __('Section Two', 'wp-custom-fields'),
        'icon'      => 'camera_enhance',
        'fields'    => $this->getFields(),
    ];
  }

  private function getFields()
  {
    $group_fields = [];

    // Заносим в $file_path_array список php файлов темы
    $file_path_array = Utile::bfglob(get_stylesheet_directory(), '*.php', 0);


		foreach ($file_path_array as $file_path) {

			$file = str_replace(get_stylesheet_directory(), '', $file_path);
			$file_name = Utile::prepareFileName($file);

      $group_fields = array_merge($group_fields, [
        [
          'id'              => $file_name . '_heading',
          'type'            => 'heading',
          'title'           => __($file , 'wp_dg')
        ],
        [
          'id'              => $file_name . '_opening_event',
          'type'            => 'input',
          'title'           => esc_html__('Открывающее событие', 'wp_dg'),
          'description'     => esc_html__('Введите открывающее событие.', 'wp_dg'),
        ],
        [
          'id'              => $file_name . '_closing_event',
          'type'            => 'input',
          'title'           => esc_html__('Закрывающее событие', 'wp_dg'),
          'description'     => esc_html__('Введите закрывающее событие.', 'wp_dg'),
        ],
        [
          'id'              => $file_name . '_header_file',
          'type'            => 'input',
          'title'           => esc_html__('Файл шапки для этого шаблона', 'wp_dg'),
          'description'     => esc_html__('Введите путь до файла шапки от корня темы.', 'wp_dg'),
        ],
        [
          'id'              => $file_name . '_footer_file',
          'type'            => 'input',
          'title'           => esc_html__('Файл футера для этого шаблона', 'wp_dg'),
          'description'     => esc_html__('Введите путь до файла футера от корня темы.', 'wp_dg'),
        ],
        [
          'id'            => $file_name . '_field_repeatable',
          'title'         => __('Условие отображения', 'wp_dg'),
          'type'          => 'repeatable',
          'fields'        => [
                [
                  'id'            => 'location_rule_param',
                  'title'         => __('Отображать группу полей, если', 'wp_dg'),
                  'type'          => 'select',
                  'description'   => esc_html__('Выберите при каких условия будут отображаться ваши поля.', 'wp_dg'),
                  'options'       => [
                      'post_type'         => __('Тип записи', 'wp_dg'),
                      'post_template'     => __('Шаблон записи', 'wp_dg'),
                      'post'              => __('Запись', 'wp_dg'),
                      'page_type'         => __('Тип страницы', 'wp_dg'),
                      'page_template'     => __('Шаблон страницы', 'wp_dg'),
                      'page'              => __('Страница', 'wp_dg'),
                  ]
                ],
                [
                  'id'            => 'location_rule_operator',
                  'title'         => '',
                  'type'          => 'select',
                  'options'       => [
                    '=='          => __('равно', 'wp_dg'),
                      '!='        => __('не равно', 'wp_dg'),
                  ]
                ],
                [
                  'id'            => 'location_rule_value',
                  'title'         => __('Значение', 'wp_dg'),
                  'type'          => 'input',
                  'subtype'       => 'url'
                ],
          ]
        ]
      ]);
		}

    return $group_fields;
  }


}
