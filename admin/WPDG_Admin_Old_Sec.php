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
  private $opt_name;

	/**
	 * Инициализируйте класс и установите его свойства.
	 *
	 * @since    1.1.0
	 */
	public function __construct() {
    $this->opt_name = 'wp_dg_option';
  }

	/**
	 * Добавление страницы настроек
	 */
	public static function addSettingsPage() :void
	{
    $redux_path = dirname(plugin_dir_path( __FILE__ )) . '/vendor/redux-framework-4';

    if (!class_exists('ReduxFramework') && file_exists($redux_path . '/redux-core/framework.php')) {
        require_once $redux_path . '/redux-core/framework.php';
    }
    //require_once 'redux-framework-config.php';

    $admin = new static();
    $admin->setArgumentsSettingsPage();
    $admin->setHelpTab();
    $admin->setSectionMainSettings();

	}

  private function setArgumentsSettingsPage()
  {
    \Redux::set_args($this->opt_name, [
      'opt_name'                  => $this->opt_name,
    	'display_name'              => 'WP DG',
    	'display_version'           => '1.1.0',
    	'menu_type'                 => 'menu',
    	'allow_sub_menu'            => true,
    	'menu_title'                => esc_html__( 'Настройки WP DG', 'wp_dg' ),
    	'page_title'                => esc_html__( 'Настройки WP DG', 'wp_dg' ),
    	'async_typography'          => true,
    	'disable_google_fonts_link' => false,
    	'admin_bar'                 => true,
    	'admin_bar_icon'            => 'dashicons-portfolio',
    	'admin_bar_priority'        => 50,
    	'global_variable'           => '',
    	'dev_mode'                  => true,
    	'customizer'                => true,
    	'page_priority'             => null,
    	'page_parent'               => 'themes.php',
    	'page_permissions'          => 'manage_options',
    	'menu_icon'                 => '',
    	'last_tab'                  => '',
    	'page_icon'                 => 'icon-themes',
    	'page_slug'                 => 'wp_dg_options',
    	'save_defaults'             => true,
    	'default_show'              => false,
    	'default_mark'              => '',
    	'show_import_export'        => false,
    	'transient_time'            => 60 * MINUTE_IN_SECONDS,
    	'output'                    => true,
    	'output_tag'                => true,
    	'database'                  => '',
    	'use_cdn'                   => true,
    	'compiler'                  => true,
      'admin_bar_links'           => [
        [
          'id'    => 'wp_dg-docs',
        	'href'  => '//de-gard.ru/',
        	'title' => esc_html__( 'Документация', 'wp_dg' ),
        ],
        [
          'id'    => 'wp_dg-support',
          'href'  => '//de-gard.ru/',
          'title' => esc_html__( 'Помощь', 'wp_dg' ),
        ]
      ],
      'share_icons'               => [
        [
          'url'   => 'https://vk.com/de_gard',
          'title' => esc_html__( 'VKontakte', 'wp_dg' ),
          'icon'  => 'el el-vkontakte',
        ]
      ],
    	'hints'                     => [
    		'icon'          => 'el el-question-sign',
    		'icon_position' => 'right',
    		'icon_color'    => 'lightgray',
    		'icon_size'     => 'normal',
    		'tip_style'     => [
    			'color'   => 'light',
    			'shadow'  => true,
    			'rounded' => false,
    			'style'   => '',
    		],
    		'tip_position'  => [
    			'my' => 'top left',
    			'at' => 'bottom right',
    		],
    		'tip_effect'    => [
    			'show' => [
    				'effect'   => 'slide',
    				'duration' => '500',
    				'event'    => 'mouseover',
    			],
    			'hide' => [
    				'effect'   => 'slide',
    				'duration' => '500',
    				'event'    => 'click mouseleave',
    			],
    		],
    	],
    ]);
  }

  private function setHelpTab()
  {
    \Redux::set_help_tab($this->opt_name, [
      [
        'id'      => 'redux-help-tab-1',
        'title'   => esc_html__('Вот тут первый вопрос!', 'wp_dg'),
        'content' => '<p>' . esc_html__('Вот тут ответ на первый вопрос!', 'wp_dg') . '</p>',
      ],
      [
        'id'      => 'redux-help-tab-2',
        'title'   => esc_html__('Вот тут второй вопрос!', 'wp_dg'),
        'content' => '<p>' . esc_html__('Вот тут ответ на второй вопрос!', 'wp_dg') . '</p>',
      ]
    ]);
  }

  private function setSectionMainSettings()
  {
    \Redux::set_section($this->opt_name, [
      'title'  => esc_html__('Главные настройки', 'wp_dg'),
      'id'     => 'main_settings',
      'desc'   => esc_html__('Тут задаются главне настройки.', 'wp_dg'),
      'icon'   => 'el el-home',
      'fields' => $this->getFields(),
    ]);
  }

  private function getFields()
  {
    $group_fields = [];

    // Заносим в $file_path_array список php файлов темы
    $file_path_array = glob(TEMPLATEPATH . '/*.php');

		foreach ($file_path_array as $file_path) {

			$file = str_replace(TEMPLATEPATH, '', $file_path);
			$file_name = Utile::prepareFileName($file);

      $group_fields = array_merge($group_fields, [
        [
          'id'      => $file_name . '_info',
          'type'    => 'info',
          'title'   => __($file , 'wp_dg'),
          'desc'    => __('Все настройки ниже относятся к этому файлу.' , 'wp_dg'),
          'style'   => 'info'
        ],
        [
          'id'       => $file_name . '_opening_event',
          'type'     => 'text',
          'title'    => esc_html__( 'Открывающее событие', 'wp_dg' ),
          'desc'     => esc_html__( 'Введите открывающее событие.', 'wp_dg' ),
        ],
        [
          'id'       => $file_name . '_closing_event',
          'type'     => 'text',
          'title'    => esc_html__( 'Закрывающее событие', 'wp_dg' ),
          'desc'     => esc_html__( 'Введите закрывающее событие.', 'wp_dg' ),
        ],
        [
          'id'       => $file_name . '_header_file',
          'type'     => 'text',
          'title'    => esc_html__( 'Файл шапки для этого шаблона', 'wp_dg' ),
          'desc'     => esc_html__( 'Введите путь до файла шапки от корня темы.', 'wp_dg' ),
        ],
        [
          'id'       => $file_name . '_footer_file',
          'type'     => 'text',
          'title'    => esc_html__( 'Файл футера для этого шаблона', 'wp_dg' ),
          'desc'     => esc_html__( 'Введите путь до файла футера от корня темы.', 'wp_dg' ),
        ],
      ]);
		}

    return $group_fields;
  }

}
