<?php
namespace WP_DG\Includes;

/**
 * Зарегистрируйте все действия и фильтры для плагина.
 *
 * Вести список всех хуков, зарегистрированных в плагине,
 * и зарегистрировать их с помощью API WordPress.
 * Вызовите функцию запуска, чтобы выполнить список действий и фильтров.
 *
 * @package    WP_DG
 * @subpackage WP_DG/Includes
 * @author     DE-GARD <info@de-gard.ru>
 */
class WPDG_Loader {

	/**
	 * Массив действий, зарегистрированных в WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $actions    The actions registered with WordPress to fire when the plugin loads.
	 */
	protected $actions = [];

	/**
	 * Массив фильтров, зарегистрированных в WordPress.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      array    $filters    The filters registered with WordPress to fire when the plugin loads.
	 */
	protected $filters = [];

	/**
	 * Инициализируйте коллекции, используемые для поддержки действий и фильтров.
	 *
	 * @since    1.0.0
	 *
	 * @param array $settings
	 */
	public function __construct(array $settings)
	{
		$this->initPropertiesFromFile($settings['initial_actions_file'], 'actions');

		$this->initPropertiesFromFile($settings['initial_filters_file'], 'filters');

		/**
		 * Выполняем хуки, которые важны в первую очередь
		 */
		$this->run();
	}

	/**
	 * Инициализация свойст массивом из файла
	 *
	 * @param string $file_path
	 * @param string $property_name
	 */
	public function initPropertiesFromFile(string $file_path, string $property_name)
	{
		if (file_exists($file_path))
			$initial_array = include $file_path;
			if(is_array($initial_array))
				$this->{$property_name} = $initial_array;

	}

	/**
	 * Добавьте новое действие/actions в коллекцию для регистрации в WordPress.
	 *
	 * @param $hook
	 * @param $component
	 * @param $callback
	 * @param int $priority
	 * @param int $accepted_args
	 *
	 * @return $this
	 */
	public function addAction( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) :self
	{
		$this->actions = $this->add( $this->actions, $hook, $component, $callback, $priority, $accepted_args );
		return $this;
	}

	/**
	 * Добавьте новый фильтр в коллекцию для регистрации в WordPress.
	 *
	 * @param $hook
	 * @param $component
	 * @param $callback
	 * @param int $priority
	 * @param int $accepted_args
	 *
	 * @return $this
	 */
	public function addFilter( $hook, $component, $callback, $priority = 10, $accepted_args = 1 ) :self
	{
		$this->filters = $this->add( $this->filters, $hook, $component, $callback, $priority, $accepted_args );
		return $this;
	}

	/**
	 * Служебная функция, которая используется, чтобы регистрировать действия и крючки в одну коллекцию.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @param    array  $hooks Коллекция Крючков, который был зарегистрирован (то есть, действия или фильтрами).
	 * @param    string $hook Имя фильтра WordPress, который регистрируется.
	 * @param    object $component Ссылка на экземпляр объекта, на котором определен фильтр.
	 * @param    string $callback Имя функции вызываемой объектом $component.
	 * @param    int    $priority Приоритет выполнения функций для одного и тоже фильтра или действия.
	 * @param    int    $accepted_args Количество аргументов, которые должны быть переданы функции.
	 * @return   array Коллекция фильтров, зарегистрированных с WordPress.
	 */
	private function add( $hooks, $hook, $component, $callback, $priority, $accepted_args )
	{
		$hooks[] = [
			'hook'          => $hook,
			'component'     => $component,
			'callback'      => $callback,
			'priority'      => $priority,
			'accepted_args' => $accepted_args
		];

		return $hooks;
	}

	/**
	 * Зарегистрировать фильтры и действия с WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run()
	{
		if(!empty($this->filters))
			foreach ( $this->filters as $hook ) {
				add_filter( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
			}

		if(!empty($this->actions))
			foreach ( $this->actions as $hook ) {
				add_action( $hook['hook'], [ $hook['component'], $hook['callback'] ], $hook['priority'], $hook['accepted_args'] );
			}

		// Задаём пустое значение
		$this->actions = $this->filters = [];
	}

}
