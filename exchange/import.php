<?php
if (!defined('ABSPATH')) exit;

require_once ABSPATH . "wp-admin/includes/media.php";
require_once ABSPATH . "wp-admin/includes/file.php";
require_once ABSPATH . "wp-admin/includes/image.php";

//разбираем import.xml
//обработчик начала элемента
function wpshop1c_import_start_element_handler($is_full, $names, $depth, $name, $attrs) {
  global $wpshop1c_groups, $wpshop1c_group_depth, $wpshop1c_group_order, $wpshop1c_property, $wpshop1c_property_order, $wpshop1c_requisite_properties, $wpshop1c_product;
	
  if (@$names[$depth - 1] == 'Классификатор' && $name == 'Группы') {
    $wpshop1c_groups = array();
    $wpshop1c_group_depth = -1;
    $wpshop1c_group_order = 1;
	if($is_full) {
		wpshop1c_clean_categs();
		wpshop1c_clean_offers();
	}
    /*обнуляем cat_avail и offer_avail*/
  }
  elseif (@$names[$depth - 1] == 'Группы' && $name == 'Группа') {
    $wpshop1c_group_depth++;
    $wpshop1c_groups[] = array('ИдРодителя' => @$wpshop1c_groups[$wpshop1c_group_depth - 1]['Ид']);
  }
  elseif (@$names[$depth - 1] == 'Группа' && $name == 'Группы') {
   
   $result = wpshop1c_save_categs($wpshop1c_groups[$wpshop1c_group_depth]);
   if ($result) $wpshop1c_group_order++;

    $wpshop1c_groups[$wpshop1c_group_depth]['Группы'] = true;
  }
  elseif (@$names[$depth - 1] == 'Классификатор' && $name == 'Свойства') {
	if($is_full) {
		wpshop1c_clean_attr();
	}
    $wpshop1c_property_order = 1;
    $wpshop1c_requisite_properties = array();
  }
  elseif (@$names[$depth - 1] == 'Свойства' && $name == 'Свойство') {
    $wpshop1c_property = array();
  }
  elseif (@$names[$depth - 1] == 'Свойство' && $name == 'ВариантыЗначений') {
    $wpshop1c_property['ВариантыЗначений'] = array();
  }
  elseif (@$names[$depth - 1] == 'ВариантыЗначений' && $name == 'Справочник') {
    $wpshop1c_property['ВариантыЗначений'][] = array();
  }
  elseif (@$names[$depth - 1] == 'Товары' && $name == 'Товар') {
    $wpshop1c_product = array(
      'ЗначенияСвойств' => array(),
      'ЗначенияРеквизитов' => array(),
    );
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'Группы') {
    $wpshop1c_product['Группы'] = array();
  }
  elseif (@$names[$depth - 1] == 'Группы' && $name == 'Ид') {
    $wpshop1c_product['Группы'][] = '';
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'Картинка') {
    if (!isset($wpshop1c_product['Картинка'])) $wpshop1c_product['Картинка'] = array();
    $wpshop1c_product['Картинка'][] = '';
  }
  elseif (@$names[$depth - 1] == 'Товар' && $name == 'Изготовитель') {
    $wpshop1c_product['Изготовитель'] = array();
  }
  elseif (@$names[$depth - 1] == 'ЗначенияСвойств' && $name == 'ЗначенияСвойства') {
    $wpshop1c_product['ЗначенияСвойств'][] = array();
  }
  elseif (@$names[$depth - 1] == 'ЗначенияСвойства' && $name == 'Значение') {
    $i = count($wpshop1c_product['ЗначенияСвойств']) - 1;
    if (!isset($wpshop1c_product['ЗначенияСвойств'][$i]['Значение'])) $wpshop1c_product['ЗначенияСвойств'][$i]['Значение'] = array();
    $wpshop1c_product['ЗначенияСвойств'][$i]['Значение'][] = '';
  }
  elseif (@$names[$depth - 1] == 'ЗначенияРеквизитов' && $name == 'ЗначениеРеквизита') {
    $wpshop1c_product['ЗначенияРеквизитов'][] = array();
  }
  elseif (@$names[$depth - 1] == 'ЗначениеРеквизита' && $name == 'Значение') {
    $i = count($wpshop1c_product['ЗначенияРеквизитов']) - 1;
    if (!isset($wpshop1c_product['ЗначенияРеквизитов'][$i]['Значение'])) $wpshop1c_product['ЗначенияРеквизитов'][$i]['Значение'] = array();
    $wpshop1c_product['ЗначенияРеквизитов'][$i]['Значение'][] = '';
  }

}
 
//обработчик строковых элементов
function wpshop1c_import_character_data_handler($is_full, $names, $depth, $name, $data) {
  global $wpshop1c_groups, $wpshop1c_group_depth, $wpshop1c_property, $wpshop1c_product;

  if (@$names[$depth - 2] == 'Группы' && @$names[$depth - 1] == 'Группа' && $name != 'Группы') {
    @$wpshop1c_groups[$wpshop1c_group_depth][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Свойства' && @$names[$depth - 1] == 'Свойство' && $name != 'ВариантыЗначений') {
    @$wpshop1c_property[$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'ВариантыЗначений' && @$names[$depth - 1] == 'Справочник') {
    $i = count($wpshop1c_property['ВариантыЗначений']) - 1;
    @$wpshop1c_property['ВариантыЗначений'][$i][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товары' && @$names[$depth - 1] == 'Товар' && !in_array($name, array('Группы', 'Картинка', 'Изготовитель', 'ЗначенияСвойств', 'СтавкиНалогов', 'ЗначенияРеквизитов'))) {
    @$wpshop1c_product[$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товар' && @$names[$depth - 1] == 'Группы' && $name == 'Ид') {
    $i = count($wpshop1c_product['Группы']) - 1;
    $wpshop1c_product['Группы'][$i] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товары' && @$names[$depth - 1] == 'Товар' && $name == 'Картинка') {
    $i = count($wpshop1c_product['Картинка']) - 1;
    $wpshop1c_product['Картинка'][$i] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Товар' && @$names[$depth - 1] == 'Изготовитель') {
    @$wpshop1c_product['Изготовитель'][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'ЗначенияСвойств' && @$names[$depth - 1] == 'ЗначенияСвойства') {
    $i = count($wpshop1c_product['ЗначенияСвойств']) - 1;
    if ($name != 'Значение') {
      @$wpshop1c_product['ЗначенияСвойств'][$i][$name] .= $data;
    }
    else {
      $j = count($wpshop1c_product['ЗначенияСвойств'][$i]['Значение']) - 1;
      $wpshop1c_product['ЗначенияСвойств'][$i]['Значение'][$j] .= $data;
    }
  }
  elseif (@$names[$depth - 2] == 'ЗначенияРеквизитов' && @$names[$depth - 1] == 'ЗначениеРеквизита') {
    $i = count($wpshop1c_product['ЗначенияРеквизитов']) - 1;
    if ($name != 'Значение') {
      @$wpshop1c_product['ЗначенияРеквизитов'][$i][$name] .= $data;
    }
    else {
      $j = count($wpshop1c_product['ЗначенияРеквизитов'][$i]['Значение']) - 1;
      $wpshop1c_product['ЗначенияРеквизитов'][$i]['Значение'][$j] .= $data;
    }
  }
}

//обработчик конца элемента
function wpshop1c_import_end_element_handler($is_full, $names, $depth, $name) {
  global $wpshop1c_groups, $wpshop1c_group_depth, $wpshop1c_group_order, $wpshop1c_property, $wpshop1c_property_order, $wpshop1c_requisite_properties, $wpshop1c_product;
	
	if (isset($names[$depth - 1])&&$names[$depth - 1] == 'Группы' && $name == 'Группа') {
    if (empty($wpshop1c_groups[$wpshop1c_group_depth]['Группы'])) {
      $result = wpshop1c_save_categs($wpshop1c_groups[$wpshop1c_group_depth]);
      if ($result) $wpshop1c_group_order++;
    }
  array_pop($wpshop1c_groups);
  $wpshop1c_group_depth--; 
  }
  if (isset($names[$depth - 1])&&$names[$depth - 1] == 'Классификатор' && $name == 'Группы') {
   /*  wpshop1c_clean_woocommerce_categories($is_full); */
  }
  elseif (isset($names[$depth - 1])&&$names[$depth - 1] == 'Свойства' && $name == 'Свойство') {
    //error_log(print_r($wpshop1c_property,true));
	wpshop1c_save_atributs($wpshop1c_property);
  }
  elseif (isset($names[$depth - 1])&&$names[$depth - 1] == 'Классификатор' && $name == 'Свойства') {
   /*  wpshop1c_clean_woocommerce_attributes($is_full);*/
  }
  elseif (isset($names[$depth - 1])&&$names[$depth - 1] == 'Товары' && $name == 'Товар') {
    if ($wpshop1c_requisite_properties) {
      foreach ($wpshop1c_product['ЗначенияСвойств'] as $product_property) {
        if (!array_key_exists($product_property['Ид'], $wpshop1c_requisite_properties)) continue;

        $property = $wpshop1c_requisite_properties[$product_property['Ид']];
        $wpshop1c_product['ЗначенияРеквизитов'][] = array(
          'Наименование' => $property['Наименование'],
          'Значение' => $product_property['Значение'],
        );
      }
    }
	wpshop1c_save_offer_import($wpshop1c_product);
  }
  elseif (@$names[$depth - 1] == 'Каталог' && $name == 'Товары') {

  }
}

function wpshop1c_save_categs($cat) {
  global $wpdb;
  $check_cat = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wpshop1c_categs WHERE cat_kode = '{$cat['Ид']}'");
  if($check_cat != null){
    $wpdb->update("{$wpdb->prefix}wpshop1c_categs",array('cat_avail' => 1),array('cat_kode' =>$cat['Ид']));
  }else {
    $wpdb->insert("{$wpdb->prefix}wpshop1c_categs",array(
      'cat_kode' => $cat['Ид'],
      'cat_avail' => 1,
      'cat_name' => $cat['Наименование'],
      'parent_cat_kode' => $cat['ИдРодителя'],
      'affiliate_id' => 0
    ),array('%s','%d','%s','%s','%d'));
  }
}

function wpshop1c_clean_categs() {
 	global $wpdb;
	$result = $wpdb->query("UPDATE `{$wpdb->prefix}wpshop1c_categs` SET `cat_avail` = 0 WHERE 1"); 
}

function wpshop1c_clean_offers() {
	global $wpdb;
	$result = $wpdb->query("UPDATE `{$wpdb->prefix}wpshop1c_offers` SET `offer_avail` = 0 WHERE 1"); 
}

function wpshop1c_clean_attr () {
  global $wpdb;
  $result = $wpdb->query("UPDATE `{$wpdb->prefix}wpshop1c_atributs` SET `attr_avail` = 0 WHERE 1"); 
}


function wpshop1c_save_offer_import($offer) {
  global $wpdb,$counter,$limit;
  if($counter <= $limit) { 
	  $artikul = '';
	  $cats = '';
	  $desc = '';
	  $pic = '';
	  $atribs = '';
	  $is_deleted='';
	 
	  if(array_key_exists('Артикул', $offer)) {
		$artikul = $offer['Артикул'];
	  }
	  if(array_key_exists('Описание', $offer)) {
		$desc = $offer['Описание'];
	  }
	  if(array_key_exists('Группы', $offer)) {
		$cats =  $offer['Группы'][0];
	  }
	  if(array_key_exists('Картинка', $offer)) {
		$pic =  json_encode($offer['Картинка']);
	  }
	  if(array_key_exists('ЗначенияСвойств', $offer)) {
		$atribs =  json_encode($offer['ЗначенияСвойств']);
	  }
	  
	  if(array_key_exists('Статус', $offer)) {
		if($offer['Статус']=='Удален') {
		  $is_deleted = 1;
		}
	  }
	  
	  $check_offer = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wpshop1c_offers WHERE offer_kode = '{$offer['Ид']}'");
	  if($check_offer != null){
		//если уже есть такой оффер
		if ($is_deleted==1){
		  $wpdb->update("{$wpdb->prefix}wpshop1c_offers",array('offer_avail' => 0),array('offer_kode' =>$offer['Ид']));
		}else {
		  $wpdb->update("{$wpdb->prefix}wpshop1c_offers",array('offer_avail' => 1,'offer_name' => $offer['Наименование'],'offer_art' => $artikul, 'offer_desc' => $desc,'offer_pic' => $pic),array('offer_kode' =>$offer['Ид']));
		}
	  }else {
		$wpdb->insert("{$wpdb->prefix}wpshop1c_offers",array(
		  'offer_kode' => $offer['Ид'],
		  'offer_avail' => 1,
		  'offer_name' => $offer['Наименование'],
		  'offer_art' => $artikul,
		  'offer_desc' => $desc,
		  'offer_cat' => $cats,
		  'offer_pic' => $pic, 
		  'offer_atribs' => $atribs,
		  'offer_time' => wpshop1c_TIMESTAMP,
		  'affiliate_id' => 0
		),array('%s','%d','%s','%s','%s','%s','%s','%s','%s','%d'));
	  }
 	  $counter++;
  }   
}

function wpshop1c_save_atributs($atribut) {
	global $wpdb;
	$attr_id_znach = '';
	$attr_name_znach = '';
	$attr_id_svoystv = '';
	$attr_name_svoystv = '';
	
	if(isset($atribut['Ид'])) {
		$attr_id_znach = $atribut['Ид'];
	}
	if(isset($atribut['Наименование'])) {
		$attr_name_znach = $atribut['Наименование'];
	}
  
	if(isset($atribut['ВариантыЗначений'])){
		$varianti_ar = $atribut['ВариантыЗначений'];
		if(is_array($varianti_ar)){
			foreach($varianti_ar as $variant){
				if(isset($variant['ИдЗначения'])) {
					$attr_id_svoystv = $variant['ИдЗначения'];
				}	
				if(isset($variant['Значение'])) {
					$attr_name_svoystv = $variant['Значение'];
				}
				
				if($attr_id_znach&&$attr_name_znach&&$attr_id_svoystv&&$attr_name_svoystv) {
					$check_attr = $wpdb->get_row("SELECT * FROM {$wpdb->prefix}wpshop1c_atributs WHERE attr_id_svoystv = '{$attr_id_svoystv}'");
					if($check_attr != null){
						$wpdb->update("{$wpdb->prefix}wpshop1c_atributs",array('attr_avail' => 1),array('attr_id_svoystv' =>$attr_id_svoystv));
					}else {
						$wpdb->insert("{$wpdb->prefix}wpshop1c_atributs",array(
						  'attr_id_znach' => $attr_id_znach,
						  'attr_name_znach' => $attr_name_znach,
						  'attr_id_svoystv' => $attr_id_svoystv,
						  'attr_name_svoystv' => $attr_name_svoystv,
						  'attr_avail' => 1,
						  
						),array('%s','%s','%s','%s','%d'));
					} 
				}
			}		
		}
	}
}