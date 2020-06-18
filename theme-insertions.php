<?php
/**
 * Список функций которые используются для вывода данных в тему
 *
 * Под данными могут понимаются значения полей ACF, содержимое файлов parts.
 *
 * @since      1.1.0
 * @package    WP_DG
 * @subpackage WP_DG/Includes
 * @author     DE-GARD <info@de-gard.ru>
 */

 /**
  *  Если поле не имеет value то выводим дефолтное значение
  *
  * @param string $field_key
  *
  * @return string
  */
function wpdg_get_field(string $field_key) :string
{
  $field_data = get_field_object($field_key);

  if ($field_data['value'] === $field_data['default_value'] || empty($field_data['value'])) {
    return $field_data['default_value'];
  }
    return $field_data['value'];
}
