<?php


namespace WP_DG\Includes;


class WPDG_Highlight {

	private $settings; // Все основные настройки
	private $events_and_files = []; // Массив всех файлов и событий, который оборачивают содержимое этих файлов

	/**
	 * В конструкторе задаём название плагина и версию
	 *
	 * @since 1.1.0
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
		$current_template = $GLOBALS['current_template'] ?? '';

		if (!empty($current_template)) {

			// Задаём дефолный массив events_and_files
			$this->events_and_files = self::getDefaultArrayEventsAndFiles();
			$main_option = get_option('wp_dg_option');

			foreach ($this->events_and_files as $file_key => &$file_data) {

				// Получаем и задаём path файлов
				if (isset($file_data['path'])) {

					$file_path = ($main_option[
						Utile::prepareFileName($current_template) . '_' . $file_key . '_file'
					]) ?? '';
					if(!empty($file_path)) $file_data['path'] = $file_path;

				} else {
					$file_data['path'] = $current_template;
				}

				$file_name = Utile::prepareFileName($file_data['path']);

				// Задаём открывающее событие
				$opening_event = ($main_option[$file_name . '_opening_event']) ?? '';
				if(!empty($opening_event)) $file_data['opening_event'] = $file_path;

				// Задаём закрывающее событие
				$closing_event = ($main_option[$file_name . '_closing_event']) ?? '';
				if(!empty($closing_event)) $file_data['closing_event'] = $file_path;

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
    wp_enqueue_style('wp_dg__picker-style', plugins_url('wp_dg/assets/js/dg-lib/dg.css'), [], $this->settings['version']);
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
			              <option value=\"text\">Текст</option>
										<option value=\"image\">Изображение</option>
										<option value=\"textarea\">Область текста</option>
										<option value=\"wysiwyg\">Редактор WYSIWYG</option>
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
