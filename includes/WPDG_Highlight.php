<?php


namespace WP_DG\Includes;


class WPDG_Highlight {

	private $settings; // Все основные настройки
	private $events_and_files = []; // Массив всех файлов и событий, который оборачивают содержимое этих файлов

	/**
	 * В конструкторе задаём название плагина и версию
	 *
	 * @since 1.0.0
	 * @param string $settings The settings of this plugin.
	 */
	public function __construct( $settings )
	{
		$this->settings = $settings;
		$this->setEventsAndFiles();
	}

	/**
	 * Получение дефолтнога массива events_and_files
	 *
	 * @return array
	 */
	private static function getDefaultArrayEventsAndFiles() :array
	{
		return [
			'header' => [
				'path' => '/header.php',
				'opening_event' => 'wp_body_open',
				'closing_event' => 'wpdg_middle'
			],
			'middle' => [
				'opening_event' => 'wpdg_middle',
				'closing_event' => 'get_footer'
			],
			'footer' => [
				'path' => '/footer.php',
				'opening_event' => 'get_footer',
				'closing_event' => 'wp_footer'
			]
		];
	}

	/**
	 * Задаются события и путь файла
	 */
	public function setEventsAndFiles() :void
	{
		$current_template = $GLOBALS['current_template'];

		if (!empty($current_template)) {

			// Задаём дефолный массив events_and_files
			$this->events_and_files = self::getDefaultArrayEventsAndFiles();

			foreach ($this->events_and_files as $file_key => &$file_data) {

				// Получаем и задаём path файлов
				if (isset($file_data['path'])) {

					Utile::getOptionAndOverrideItem(
						$current_template,
						$file_key . '_file',
						$file_data['path']
					);

				} else {
					$file_data['path'] = $current_template;
				}

				// Задаём открывающее событие
				Utile::getOptionAndOverrideItem(
					$file_data['path'],
					'opening_event',
					$file_data['opening_event']
				);

				// Задаём закрывающее событие
				Utile::getOptionAndOverrideItem(
					$file_data['path'],
					'closing_event',
					$file_data['closing_event']
				);

			}
		}
	}

	/**
	 * Размежевание области редактирования
	 * Запуск всех событий заданных в $events_and_files
	 */
	public function setRegions() :void
	{
		foreach ((array) $this->events_and_files as $file_data) {

			$file_path = '/' . trim($file_data['path'], '/');

			// добавление открывающего комментария
			add_action( $file_data['opening_event'], function () use ($file_path){
				echo "\n <!-- path:{$file_path} --> \n";
			} );

			// добавление закрывающего комментария
			add_action( $file_data['closing_event'], function () use ($file_path){
				echo "\n <!-- /path:{$file_path} --> \n";
			} );
		}
	}

	/**
	 * Подключение стилей и скритов js
	 */
	public function enqueueScriptsAndStyles() :void
	{
    // JS
    wp_enqueue_script( 'wp_dg__picker', plugins_url('wp_dg/assets/js/dg-lib/element-picker.js'), [], $this->settings['version'], true);
    wp_enqueue_script( 'wp_dg__wrapper-picker', plugins_url('wp_dg/assets/js/dg-lib/index.js'), ['wp_dg__picker'], $this->settings['version'], true);

    // CSS
    wp_enqueue_style('wp_dg__picker-css', plugins_url('wp_dg/assets/js/dg-lib/dg.css'), [], $this->settings['version']);
	}

	/**
	 * В глобальный массив передать название файла текущего шаблона,
	 * который используется для отображения страницы
	 *
	 * @param string $template
	 *
	 * @return string
	 */
	public static function filterTemplateInclude( string $template ) :string
	{
		$GLOBALS['current_template'] = str_ireplace(TEMPLATEPATH, '', $template);
		return $template;
	}

	/**
	 *  обавление контекстного меню
	 */
	public function addContextMenuAndPopUp() :void
	{
		echo "<div class=\"dg-menu\"><ul></ul></div>\n
				<div class=\"dg-modal\" id=\"region-modal\">
			      <div class=\"dg-modal-body\">
			        <button type=\"button\" class=\"dg-modal-close\">&times;</button>
			        <div class=\"dg-modal-title\">
			          Задайте название и тип региона
			        </div>
			        <form class=\"dg-modal-form\">
			          <div class=\"dg-modal-form-group\">
			            <label for=\"region-name\">Название</label>
			            <input type=\"text\" name=\"name\" id=\"region-name\">
			          </div>
			          <div class=\"dg-modal-form-group\">
			            <label for=\"region-type\">Тип</label>
			            <select name=\"type\" id=\"region-type\">
			              <option value=\"text\">text</option>
			            </select>
			          </div>
			          <div class=\"dg-modal-form-btn-wrap\">
			            <button type=\"submit\">Сохранить</button>
			          </div>
			        </form>
			      </div>
			    </div>";
	}

	/**
	 * Добавление type="module" для JS
	 *
	 * @param string $tag
	 * @param string $handle
	 * @param string $src
	 *
	 * @return string
	 */
	public function addModuleTypeForJS(string $tag, string $handle, string $src) :string
	{
		if ( 'wp_dg__wrapper-picker' === $handle ) {
			$tag = '<script type="module" src="' . esc_url( $src ) . '"></script>';
		}
		return $tag;
	}
}
