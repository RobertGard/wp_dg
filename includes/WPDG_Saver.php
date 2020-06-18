<?php

namespace WP_DG\Includes;

use DiDom\Document;

class WPDG_Saver {

	private $code_file_path;
	private $json_file_path;
	private $json_file_name;
	private $json_folder_path;
	private $group_data;

	/**
	 *  Получаем регионы и сохраняем
	 * @throws \DiDom\Exceptions\InvalidSelectorException
	 */
	public static function saveRegions()
	{
		$php_input = Utile::jsonFromFileToArray('php://input');
		$saver = new static();

		foreach ($php_input['regions_list'] as $region) {

			// Выбираю первый массив
			$region_data = current($region);

			$saver->init($region_data['path']);
			$saver->setGroupData();

			$document = new Document();

      $html = self::getCodeWithClosingTag($saver->code_file_path);

			$document->loadHtml($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

			foreach ($region_data['items'] as $item) {

				// Времено удаляю "html > body > "
				$selector = str_replace('html > body > ', '', $item['selector']);

				$element = $document->find($selector)[0];

				$saver->addField($item, $element);
			}

			if(file_put_contents($saver->json_file_path, json_encode($saver->group_data))){
			  file_put_contents($saver->code_file_path, urldecode(htmlspecialchars_decode($document->html())));
      }
		}

		wp_send_json('Ура!');
	}

  /**
   * Проверка и добавления закрывающего тега PHP
   * @param $file_path
   * @return string
   */
	private static function getCodeWithClosingTag(string $file_path) :string
  {
    $html = file_get_contents($file_path);
    $opening_tags = substr_count($html, '<?');
    $closing_tags = substr_count($html, '?>');

    if ($closing_tags < $opening_tags) {
      $html .= '?>';
    }
    return $html;
  }

	/**
	 * Инициализация свойств
	 * @param string $region_path
	 */
	private function init(string $region_path) :void
	{
		$this->code_file_path = get_stylesheet_directory() . $region_path;

		$this->json_file_name = Utile::prepareFileName($region_path);

		$this->json_folder_path = get_stylesheet_directory() . '/acf-json/';

		$this->json_file_path = $this->json_folder_path . $this->json_file_name .'.json';

		$this->createJsonFolder();
	}

	/**
	 * Открываем файл json если он есть
	 * или создаём файл если его нет
	 */
	private function setGroupData() :void
	{
		if (file_exists($this->json_file_path)) {
			$this->group_data = Utile::jsonFromFileToArray($this->json_file_path);
		} else {
			$this->group_data = Utile::jsonFromFileToArray(WP_PLUGIN_DIR . '/wp_dg/json-templates/fields-group.json');
			$this->group_data['key'] = $this->group_data['title'] = $this->json_file_name;

			$this->setLocation();

		}
		$this->group_data['modified'] = time();
	}

	/**
	 * Задаём location
	 */
	private function setLocation() :void
	{
		Utile::getOptionAndOverrideItem($this->json_file_name, 'location_rule_param', $this->group_data['location'][0][0]['param']);
		Utile::getOptionAndOverrideItem($this->json_file_name, 'location_rule_operator', $this->group_data['location'][0][0]['operator']);
		Utile::getOptionAndOverrideItem($this->json_file_name, 'location_rule_value', $this->group_data['location'][0][0]['value']);
	}

	/**
	 *  Добавление полей
	 *
	 * @param array $item
	 * @param \DiDom\Element $element
	 */
	private function addField(array $item, \DiDom\Element &$element) :void
	{
		$path_json_tmp_field = WP_PLUGIN_DIR . "/wp_dg/json-templates/field-{$item['type']}.json";

		if (file_exists($path_json_tmp_field)) {
			$field_data = Utile::jsonFromFileToArray($path_json_tmp_field);

			$field_data['key'] = $item['name'] . '_' . rand();
			$field_data['name'] = $field_data['label'] = $item['name'];

			$field_data['default_value'] = $element->text();

			$element->setValue("&lt;?= wpdg_get_field('{$field_data['key']}'); ?&gt;");

			$this->group_data['fields'][] = $field_data;
		}
	}

	/**
	 * Создание папки, в которой будет храниться json
	 */
	private function createJsonFolder() :void
	{
		if (!is_dir($this->json_folder_path)) {
			mkdir($this->json_folder_path, 0755, true);
			file_put_contents($this->json_folder_path . 'index.php', '<?php // Silence is golden.');
		}
	}

	/**
	 *  Автоматическая синхронизация ACF
	 */
	public static function acfAutoSync()
	{
		if (!is_plugin_active('advanced-custom-fields/acf.php')) return;

		$groups = acf_get_field_groups();
		if (empty($groups)) {
			return;
		}

		// find JSON field groups which have not yet been imported
		$sync 	= array();
		foreach ($groups as $group) {
			$local 		= acf_maybe_get($group, 'local', false);
			$modified 	= acf_maybe_get($group, 'modified', 0);
			$private 	= acf_maybe_get($group, 'private', false);

			// ignore DB / PHP / private field groups
			if ($local !== 'json' || $private) {
				// do nothing
			} elseif (! $group['ID']) {
				$sync[$group['key']] = $group;
			} elseif ($modified && $modified > get_post_modified_time('U', true, $group['ID'], true)) {
				$sync[$group['key']]  = $group;
			}
		}

		if (empty($sync)) {
			return;
		}
		foreach ($sync as $key => $group) {
			// append fields
			if (acf_have_local_fields($key)) {
				$group['fields'] = acf_get_fields($key);
			}

			// import
			$field_group = acf_import_field_group($group);
		}
	}

}
