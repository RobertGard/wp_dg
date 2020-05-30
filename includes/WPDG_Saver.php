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
		$php_input = self::jsonFromFileToArray('php://input');
		$saver = new static();

		foreach ($php_input["regions_list"] as $region) {

			// Выбираю первый массив
			$region_data = current($region);

			$saver->init($region_data);
			$saver->setGroupData();

			$document = new Document();
			$document->preserveWhiteSpace();
			$document->loadHtmlFile($saver->code_file_path, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

			foreach ($region_data["items"] as $item) {
				// Времено удаляю "html > body > "
				$selector = str_replace("html > body > ", "", $item["selector"]);
				$found_item = $document->find($selector)[0];
				$default_value = $found_item->text();
				$found_item->setValue("&lt;?= get_field('{$item["name"]}'); ?&gt;");

				$saver->addField($item, $default_value);
			}

			if(file_put_contents($saver->json_file_path, json_encode($saver->group_data)))
				file_put_contents($saver->code_file_path, htmlspecialchars_decode($document->format()->html(LIBXML_NOEMPTYTAG)));
		}

		wp_send_json("Ура!");
	}

	/**
	 * Инициализация свойств
	 * @param array $region_data
	 */
	private function init(array $region_data) :void
	{
		$this->code_file_path = get_stylesheet_directory() . $region_data["path"];

		$this->json_file_name = Utile::prepareFileName($region_data["path"]);

		$this->json_folder_path = get_stylesheet_directory() . "/acf-json/";

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
			$this->group_data = self::jsonFromFileToArray($this->json_file_path);
		} else {
			$this->group_data = self::jsonFromFileToArray(WP_PLUGIN_DIR . '/wp-dg/json-templates/fields-group.json');
			$this->group_data["key"] = $this->group_data["title"] = $this->json_file_name;
		}
		$this->group_data["modified"] = time();
	}

	/**
	 *  Добавление полей
	 *
	 * @param array $item
	 * @param string $default_value
	 */
	private function addField(array $item, string $default_value) :void
	{
		$path_json_tmp_field = WP_PLUGIN_DIR .'/wp-dg/json-templates/field-' . $item["type"] . '.json';

		if (file_exists($path_json_tmp_field)) {
			$field_data = self::jsonFromFileToArray($path_json_tmp_field);

			$field_data["key"] = $item["name"]. "_" . rand();
			$field_data["name"] = $field_data["label"] = $item["name"];
			$field_data["default_value"] = $default_value;

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
			file_put_contents($this->json_folder_path . "index.php", "<?php // Silence is golden.");
		}
	}

	/*
	 * Полачить json из файла и декодировать в массив
	 *
	 * @param string $file_path
	 *
	 * @return array
	 */
	private static function jsonFromFileToArray( string $file_path) :array
	{
		return json_decode(file_get_contents($file_path), true);
	}

	/**
	 *  Автоматическая синхронизация ACF
	 */
	public static function acfAutoSync()
	{
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
