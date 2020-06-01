<?php


namespace WP_DG\Includes;


class WPDG_Highlight {

	private $settings; // Все основные настройки
	private $eventsAndFiles = []; // Массив всех файлов и событий, который оборачивают содержимое этих файлов

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
	 * Задаются события и путь файла
	 */
	public function setEventsAndFiles() :void
	{
		$current_template = $GLOBALS['current_template'];

		if (!empty($current_template)) {

			$header_file = get_option(Utile::prepareFileName($current_template) . '_header_file');
			$this->eventsAndFiles['header']['path'] = ($header_file !== false && !empty($header_file)) ? $header_file : '/header.php';

			$this->setEvents(
				'header',
				$this->eventsAndFiles['header']['path'],
				'wp_body_open',
				'wpdg_middle'
			);

			$this->eventsAndFiles['middle']['path'] = '/' . trim($current_template, '/');

			$this->setEvents(
				'middle',
				$this->eventsAndFiles['middle']['path'],
				'wpdg_middle',
				'get_footer'
			);

			$footer_file = get_option(Utile::prepareFileName($current_template) . '_footer_file');
			$this->eventsAndFiles['footer']['path'] = ($footer_file !== false && !empty($footer_file)) ? $footer_file : '/footer.php';

			$this->setEvents(
				'footer',
				$this->eventsAndFiles['footer']['path'],
				'get_footer',
				'wp_footer'
			);
		}
	}

	/**
	 *  Задаём открывающее и закрывающее события
	 *
	 * @param string $file
	 * @param string $default_opening_event
	 * @param string $default_closing_event
	 */
	private function setEvents(string $key, string $file, string $default_opening_event, string $default_closing_event) :void
	{
		$prepared_file_name = Utile::prepareFileName($file);

		$opening_event = get_option($prepared_file_name . '_opening_event');
		$this->eventsAndFiles[$key]['opening_event'] = ($opening_event !== false && !empty($opening_event)) ? $opening_event : $default_opening_event;

		$closing_event = get_option($prepared_file_name . '_closing_event');
		$this->eventsAndFiles[$key]['closing_event'] = ($closing_event !== false && !empty($closing_event)) ? $closing_event : $default_closing_event;
	}

	/**
	 * Размежевание области редактирования
	 * Запуск всех событий заданных в $eventsAndFiles
	 */
	public function setRegions() :void
	{
		foreach ((array) $this->eventsAndFiles as $fileData) {

			// добавление открывающего комментария
			add_action( $fileData['opening_event'], function () use ($fileData){
				echo "\n <!-- path:{$fileData['path']} --> \n";
			} );

			// добавление закрывающего комментария
			add_action( $fileData['closing_event'], function () use ($fileData){
				echo "\n <!-- /path:{$fileData['path']} --> \n";
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
