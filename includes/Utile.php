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

	/**
	 * Полачить json из файла и декодировать в массив
	 *
	 * @param string $file_path
	 *
	 * @return array
	 */
	public static function jsonFromFileToArray( string $file_path) :array
	{
		return json_decode(file_get_contents($file_path), true);
	}

	/**
	 *  Получение option (настроек в админке) и переопредение элемента
	 *
	 * @param string $file_path
	 * @param string $option_postfix
	 * @param string $item
	 */
	public static function getOptionAndOverrideItem(string $file_path, string $option_postfix, string &$item) :void
	{
		$option = get_option(Utile::prepareFileName($file_path) . '_' . $option_postfix);

		if ($option !== false && !empty($option)) {
			$item = $option;
		}
	}

	/**
	 *  Получить все файлы и папки внутри переданной
	 *
	 * @param string $path
	 * @param string $pattern
	 * @param int $depth
 	 * @param string $flags
	 *
 	 * @return array
	 */
	public static function bfglob($path, $pattern = '*', $depth = 1, $flags = GLOB_NOSORT) :array
  {
	  $matches = [];
	  $folders = [rtrim($path, '/')];

	  while ($folder = array_shift($folders)) {

			if ($depth !== 1) { // Если первая вложенность, то не заходим сюда

				if ($depth !== 0) { // Если нужно вывести все подходящие файлы, то сюда не зайдёт
					$path_from_root_theme = str_replace($path, '', $folder);
					$nesting_level = count(explode('/', $path_from_root_theme));
					if ($nesting_level > $depth) break;
				}

				$moreFolders = glob($folder . '/*', GLOB_ONLYDIR);
				$folders = array_merge($folders, $moreFolders);

			}

		    $matches = array_merge($matches, glob($folder . '/' . $pattern, $flags));

    }
	  return $matches;
  }
}
