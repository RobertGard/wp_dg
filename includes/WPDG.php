<?php
namespace WP_DG\Includes;

use WP_DG\Admin\WPDG_Admin;

/**
 * Файл, который определяет основной класс плагина
 *
 * Определение класса, которое включает атрибуты и функции,
 * используемые как на общедоступной стороне сайта, так и в административной области.
 *
 * @since      1.1.0
 * @package    WP_DG
 * @subpackage WP_DG/Includes
 * @author     DE-GARD <info@de-gard.ru>
 */
 
class WPDG {

	private static $instances = null; // Объект приложения
	private $currentRegime; // Хранится название режима
	private $settings; // Все основные настройки

	private $loader; //Объект загрузчика, который отвечает за поддержку и регистрацию всех хуков, которые питают плагин.

	/**
	 * Определите основные функциональные возможности плагина.
	 *
	 * Установите имя плагина и версию плагина, которые могут быть использованы во всем плагине.
	 * Загрузите зависимости, определите локаль и установите хуки
	 * для административной области и общедоступной стороны сайта.
	 *
	 * @since    1.1.0
	 */
	protected function __construct()
	{
		$this->setSettings();

		// Загрузите необходимые зависимости для этого плагина.
		$this->loader = new WPDG_Loader($this->getSettings());
		$this->setLocale();
	}

	protected function __clone() {}

	public function __wakeup()
	{
		throw new \Exception("Cannot unserialize a singleton.");
	}

	/**
	 * Создание синглтона
	 *
	 * @return WPDG
	 */
	public static function getInstance(): WPDG
	{
		if (self::$instances === null) {
			self::$instances = new static();
		}

		return self::$instances;
	}

	/**
	 *  Инициализация настроек
	 */
	private function setSettings() :void
	{
		// Define constants.
		$this->define( 'WP_DG_PATH', dirname(plugin_dir_path( __FILE__ ) ));
		$this->define( 'WP_DG_SLUG', basename(WP_DG_PATH) );
		$this->define( 'WP_DG_VERSION', '1.1.0' );

		// Define settings.
		$this->settings = array(
			'name'						=> __('WP DG', 'wp_dg'),
			'slug'						=> WP_DG_SLUG,
			'version'					=> WP_DG_VERSION,
			'basename'					=> 'WP_DG',
			'path'						=> WP_DG_PATH,
			'url'						=> plugins_url(WP_DG_SLUG),
			'initial_actions_file'		=> WP_DG_PATH . '/initial-actions_array.php',
			'initial_filters_file'		=> WP_DG_PATH . '/initial-filters_array.php'
		);
	}

	/**
	 *  Тут задаётся режим
	 *  Пока этот метод пуст и задаётся режим selection_regions,
	 *  так как в панели админки нет переключателя плагина
	 */
	private function setRegime() :void
	{
		$regime = $_GET['wpdg_regime'] ?? '';

		if ($regime === 'selection_regions' || $regime === 'editing_regions') {
			$this->currentRegime = $regime;
		}
	}

	/**
	 * Определите локаль для этого плагина для интернационализации.
	 *
	 * Использует класс WPDG_i18n, чтобы установить домен и зарегистрировать хук в WordPress.
	 *
	 * @since    1.1.0
	 * @access   private
	 */
	private function setLocale() :void
	{
		$plugin_i18n = new WPDG_i18n();

		$this->loader->addAction( 'plugins_loaded', $plugin_i18n, 'loadPluginTextDomain' );
	}

	/**
	 * Запуск разметки областей
	 * (в каждом файле добавляется комментарий, указывающий путь до файла)
	 */
	public function run() :void
	{
		$this->setRegime();

		// Если текущий пользователь администратор и включен режим выделения областей
		if (current_user_can('manage_options') && $this->currentRegime === 'selection_regions') {

			$highlight = new WPDG_Highlight( $this->getSettings() );
			$highlight->setRegions();
			$this->loader->addAction( 'wp_footer', $highlight, 'enqueueScriptsAndStyles' )
			->addAction( 'script_loader_tag', $highlight, 'addModuleTypeForJS', 10, 3 )
			->addAction( 'wp_footer', $highlight, 'addContextMenuAndPopUp' );

		}

		$this->loader->run();
	}

	/**
	 * Ссылка на класс, который управляет хуками с плагином.
	 *
	 * @since     1.1.0
	 * @return    WPDG_Loader    Orchestrates the hooks of the plugin.
	 */
	public function getLoader() :WPDG_Loader
	{
		return $this->loader;
	}

	/**
	 * Получить номер версии плагина.
	 *
	 * @since     1.1.0
	 * @return    array    The settings number of the plugin.
	 */
	public function getSettings() :array
	{
		return $this->settings;
	}

	/**
	 * Определяет константу, если она еще не существует.
	 *
	 * @date	25/4/20
	 * @since	1.1.0
	 *
	 * @param	string $name The constant name.
	 * @param	mixed $value The constant value.
	 * @return	void
	 */
	function define( $name, $value = true ) :void
	{
		if( !defined($name) ) {
			define( $name, $value );
		}
	}

	/**
	 *  Добавление функционала для управления режимами в админ бар
	 *
	 * @param $wp_admin_bar
	 */
	public static function toolsAdminBar($wp_admin_bar) :void
	{
		if(isset($_GET['wpdg_regime'])) {

			// Добавление пункта "Отключение режима"
			$wp_admin_bar->add_menu( array(
				'id'     => 'wpdg_disable_regime',
				'title'  => 'Отключение режима',
				'href'   => esc_url(remove_query_arg('wpdg_regime'))
			) );

			// Добавление пункта "Отправить изменения"
			$wp_admin_bar->add_menu( array(
				'id'     => 'wpdg_send_changes',
				'title'  => 'Отправить изменения',
				'meta'   => [
					'class' => 'dg-send'
				]
			) );
		}
		else {

			//  Добавление главного пункта "Выбор режима DG" в верхнюю панель
			$wp_admin_bar->add_menu( array(
				'id'    => 'wpdg_select_regime',
				'title' => 'Выбор режима WP DG',
			) );

			// Добавление подпункта "Выделение областей"
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpdg_select_regime',
				'id'     => 'wpdg_selection_regions',
				'title'  => 'Выделение областей',
				'href'   => esc_url( add_query_arg( 'wpdg_regime', 'selection_regions' ) )
			) );

			// Добавление подпункта "Редактирование областей"
			$wp_admin_bar->add_menu( array(
				'parent' => 'wpdg_select_regime',
				'id'     => 'wpdg_editing_regions',
				'title'  => 'Редактирование областей',
				'href'   => esc_url( add_query_arg( 'wpdg_regime', 'editing_regions' ) )
			) );
		}
	}
}
