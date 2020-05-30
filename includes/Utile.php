<?php


namespace WP_DG\Includes;


class Utile
{
	/**
	 *  Подготовка названия файла
	 *  удаление формата .php и замена слешей на подчёркивание
	 * @param string $region_path
	 *
	 * @return string
	 */
	public static function prepareFileName(string $path_file) :string
	{
		return str_replace(['/','.php'], ['_'], trim($path_file, '/'));
	}
}