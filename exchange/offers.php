<?php
if (!defined('ABSPATH')) exit;

function wpshop1c_offers_start_element_handler($is_full, $names, $depth, $name, $attrs) {
  global $wpshop1c_price_types, $wpshop1c_offer, $wpshop1c_price;

  if (@$names[$depth - 1] == 'ПакетПредложений' && $name == 'ТипыЦен') {
    $wpshop1c_price_types = array();
  }
  elseif (@$names[$depth - 1] == 'ТипыЦен' && $name == 'ТипЦены') {
    $wpshop1c_price_types[] = array();
  }
  elseif (@$names[$depth - 1] == 'Предложения' && $name == 'Предложение') {
    $wpshop1c_offer = array();
  }
  elseif (@$names[$depth - 1] == 'Предложение' && $name == 'ХарактеристикиТовара') {
    $wpshop1c_offer['ХарактеристикиТовара'] = array();
  }
  elseif (@$names[$depth - 1] == 'ХарактеристикиТовара' && $name == 'ХарактеристикаТовара') {
    $wpshop1c_offer['ХарактеристикиТовара'][] = array();
  }
  elseif (@$names[$depth - 1] == 'Цены' && $name == 'Цена') {
    $wpshop1c_price = array();
  }
}

function wpshop1c_offers_character_data_handler($is_full, $names, $depth, $name, $data) {
  global $wpshop1c_price_types, $wpshop1c_offer, $wpshop1c_price;

  if (@$names[$depth - 2] == 'ТипыЦен' && @$names[$depth - 1] == 'ТипЦены' && $name != 'Налог') {
    $i = count($wpshop1c_price_types) - 1;
    @$wpshop1c_price_types[$i][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Предложения' && @$names[$depth - 1] == 'Предложение' && !in_array($name, array('ХарактеристикиТовара', 'Цены'))) {
    @$wpshop1c_offer[$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'ХарактеристикиТовара' && @$names[$depth - 1] == 'ХарактеристикаТовара') {
    $i = count($wpshop1c_offer['ХарактеристикиТовара']) - 1;
    @$wpshop1c_offer['ХарактеристикиТовара'][$i][$name] .= $data;
  }
  elseif (@$names[$depth - 2] == 'Цены' && @$names[$depth - 1] == 'Цена') {
    @$wpshop1c_price[$name] .= $data;
  }
}

function wpshop1c_offers_end_element_handler($is_full, $names, $depth, $name) {
  global $wpshop1c_price_types, $wpshop1c_price_type, $wpshop1c_price_type, $wpshop1c_offer, $wpshop1c_suboffers, $wpshop1c_price;

  if (@$names[$depth - 1] == 'ПакетПредложений' && $name == 'ТипыЦен') {
    if (!defined('wpshop1c_PRICE_TYPE')) {
      $wpshop1c_price_type = $wpshop1c_price_types[0];
    }
    else {
      foreach ($wpshop1c_price_types as $price_type) {
        if ($price_type['Ид'] != wpshop1c_PRICE_TYPE && $price_type['Наименование'] != wpshop1c_PRICE_TYPE) continue;

        $wpshop1c_price_type = $price_type;
        break;
      }
      if (!isset($wpshop1c_price_type)) wpshop1c_error("Failed to match price type");
    }

    
  }
  elseif (@$names[$depth - 1] == 'Цены' && $name == 'Цена') {
    if (!isset($wpshop1c_offer['Цена']) && (!isset($wpshop1c_price['ИдТипаЦены']) || $wpshop1c_price['ИдТипаЦены'] == $wpshop1c_price_type['Ид'])) $wpshop1c_offer['Цена'] = $wpshop1c_price;
  }
  elseif (@$names[$depth - 1] == 'ХарактеристикаТовара' && $name == 'Наименование') {
    $i = count($wpshop1c_offer['ХарактеристикиТовара']) - 1;
    $wpshop1c_offer['ХарактеристикиТовара'][$i]['Наименование'] = preg_replace("/\s+\(.*\)$/", '', $wpshop1c_offer['ХарактеристикиТовара'][$i]['Наименование']);
  }
  elseif (@$names[$depth - 1] == 'Предложения' && $name == 'Предложение') {
    if (strpos($wpshop1c_offer['Ид'], '#') === false) {
      wpshop1c_replace_offer($wpshop1c_offer['Ид'], @$wpshop1c_offer['Цена']['ЦенаЗаЕдиницу'], @$wpshop1c_offer['Количество'], @$wpshop1c_offer['Цена']['Коэффициент']); 
    }
    else {
      $guid = $wpshop1c_offer['Ид'];
      list($offer_guid, ) = explode('#', $guid, 2);

      $wpshop1c_suboffers[$offer_guid][] = array(
        'price' => @$wpshop1c_offer['Цена']['ЦенаЗаЕдиницу'],
		'name' => @$wpshop1c_offer['Наименование'],
        'quantity' => @$wpshop1c_offer['Количество'],
        'coefficient' => @$wpshop1c_offer['Цена']['Коэффициент'],
        'characteristics' => isset($wpshop1c_offer['ХарактеристикиТовара']) ? $wpshop1c_offer['ХарактеристикиТовара'] : array(),
      );
	  
      
    }
  }
  elseif (@$names[$depth - 1] == 'ПакетПредложений' && $name == 'Предложения') {
   if($wpshop1c_suboffers) wpshop1c_replace_suboffers($wpshop1c_suboffers);
   generateCategories(); 
   generatePosts(); 
   clear_unavailable_offers();
  }
}

function wpshop1c_replace_offer($id,$price,$sklad,$koeficient) {
  global $wpdb;
  
  $wpdb->update("{$wpdb->prefix}wpshop1c_offers",
  array(
    'offer_common_price' => $price,
    'offer_common_sklad' => $sklad,
    'offer_common_koef' => $koeficient
  ),
  array('offer_kode' =>$id));
}

function wpshop1c_replace_suboffers($wpshop1c_suboffers) {
  global $wpdb;
 
  foreach ($wpshop1c_suboffers as $id=>$offer){
    $prices =  json_encode($offer);
	
    $wpdb->update("{$wpdb->prefix}wpshop1c_offers",
    array(
      'offer_prices' => $prices
    ),
    array('offer_kode' =>$id));
  }
}

function clear_unavailable_offers(){
  global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpshop1c_offers` where `offer_avail` = 0 and `affiliate_id` > 0 ");
  if($results&&is_array($results)) {
    foreach($results as $item) {
      $post = array( 'ID' => $item->affiliate_id, 'post_status' => 'draft' );
      wp_update_post($post);
    }
  }
}

function setParents(){
	global $wpdb;
	foreach(getCategories() as $category){
		if ($category->parent_cat_kode != ''){
			$wp_parentID = getWpID($category->parent_cat_kode);
			$wpdb->query("update `{$wpdb->prefix}term_taxonomy` set `parent` = '{$wp_parentID}' where `term_id` = '{$category->affiliate_id}'");
		}
	}	
}

function getWpID($xmlID){
	global $wpdb;
	return $wpdb->get_var("SELECT `affiliate_id` FROM `{$wpdb->prefix}wpshop1c_categs` WHERE `cat_kode` = '{$xmlID}'");
}

function getCategories()	{
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpshop1c_categs` where `cat_avail`=1");
	return $results;
}

function getOffers() {
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpshop1c_offers` where `offer_avail` = 1");
	return $results;
}

function generateCategories(){
	global $wpdb;
	foreach(getCategories() as $category)
	{
		if ($category->affiliate_id == 0)
		{
			// Создаем новую term
			$wpdb->insert("{$wpdb->prefix}terms",
			array(
					'name' => $category->cat_name,
					'slug' => translit($category->cat_name)
			),
			array('%s','%s',));
			// Запоминаем ID нового термса
			$termID = $wpdb->insert_id;
			// Указываем WordPress, что это категория
			$wpdb->insert("{$wpdb->prefix}term_taxonomy",
        array('term_id' => $termID,'taxonomy' => "category"),
        array('%d','%s')
			);
			// Обновляем информацию о связке в таблицу категорий проекта
			$wpdb->update(
				"{$wpdb->prefix}wpshop1c_categs",
				array('affiliate_id' => $termID),
				array('cat_kode' => $category->cat_kode),
				array('%d'),
				array('%s')
			);
    }
	}
	setParents();
}

function generatePosts() {
		global $wpdb;
		foreach(getOffers() as $offer)
		{
			// Генерируем новую запись
			$postID = generatePost($offer); 
    }	
}

function generatePost($data) {
		global $wpdb;
    
		foreach(getCategories() as $category) {
			if ($data->offer_cat == $category->cat_kode) {
				$data->offer_term = $category->affiliate_id;
				break;
			}
		}
		
		if (!isset($data->offer_term)) {
			/** @todo Код, если категория для данного поста не найдена */
			$data->offer_term = 0;
		}
		
    $post = array(
				'post_category' => array($data->offer_term),
				'post_content' => '',
				'post_name' => translit($data->offer_name),
				'post_status' => 'publish',
				'post_title' => $data->offer_name,
				'post_type' => 'post'
		);

		// Если такой товар уже связан с каким-то постом, то делаем обновление поста
		if ($data->affiliate_id != 0) {
			$post['ID'] = $data->affiliate_id;
		}
		
		$postID = wp_insert_post($post);
		set_post_format($postID, 'gallery' );

		// Связываем товары с постами
		$wpdb->update(
				"{$wpdb->prefix}wpshop1c_offers",
				array('affiliate_id' => $postID),
				array('offer_kode' => $data->offer_kode ),
				array('%d'),
				array('%s')
		);
    $result_price = '';
	
    if($data->offer_prices) {
		$prices =  json_decode($data->offer_prices);
		
		foreach($prices as $key=>$item) {
			$result_prices = '';
			error_log(print_r($prices,true));
			$i = $key+2;
			if(isset($item->price)&&isset($item->coefficient)) {
				$result_prices =$item->price*$item->coefficient;
				update_post_meta($postID, 'cost_'.$i, $result_prices);
			}
			if(isset($item->name)) {
				update_post_meta($postID, 'name_'.$i,$item->name);
			}
			if(isset($item->quantity)) {
				update_post_meta($postID, 'sklad_'.$i,floor($item->quantity*1));
			}
		}
    }
	
    if($data->offer_common_koef) {
        $koef_price = $data->offer_common_koef;
      }else {
        $koef_price = 1;
    }
      
    if($data->offer_common_price) {
        $result_price = $data->offer_common_price * $koef_price;
        update_post_meta($postID, 'cost_1', $result_price);
    }
      
    if($data->offer_common_sklad) {
        update_post_meta($postID, 'sklad_1', $data->offer_common_sklad);
    }
   
    
    if($data->offer_pic) {
      wpshop1c_replace_post_img($postID, $data->offer_pic);
    } 
	
	if($data->offer_art) {
      update_post_meta($postID, 'artikul', $data->offer_art);
    } 
	
	if($data->offer_atribs) {
		$offer_atribs_ar = json_decode($data->offer_atribs);
		foreach($offer_atribs_ar as $attr){
			if(isset($attr->Значение)){
				$znachen = $attr->Значение;
				$svoystvo = wpshop1c_get_attr_by_id($znachen[0]);
				if($svoystvo) {
					update_post_meta($postID, $svoystvo[0]->attr_name_znach, $svoystvo[0]->attr_name_svoystv);
				}
			}
		}
	} 
	return $postID;
}

function wpshop1c_get_attr_by_id($attr) {
	global $wpdb;
	$results = $wpdb->get_results("SELECT * FROM `{$wpdb->prefix}wpshop1c_atributs` where `attr_id_svoystv` = '{$attr}'");
	return $results;
}

function wpshop1c_replace_post_img($postID, $pic) {
  $pics_ar = json_decode($pic);
  $data_dir = wpshop1c_DATA_DIR . "catalog";
  $url = wpshop1c_UPLOAD_URL . "catalog"; 
  
  foreach($pics_ar as $key=>$adr){
  $attachment_path = '';
    $attachment_path_to_file = $adr;
    $attachment_path = "$data_dir/$attachment_path_to_file";
    $attachment_url = "$url/$attachment_path_to_file";
    $meta_name = "Thumbnail".$key;
    $wp_filetype = wp_check_filetype(basename($attachment_path), null );
			$attachment = array(
				'guid'           => $attachment_url, 
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => preg_replace('/\.[^.]+$/', '', basename($attachment_path)),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment( $attachment, $attachment_path ,$postID);
			update_post_meta($postID, $meta_name, $attachment_url);
      if ( !defined('ABSPATH') ){
      define('ABSPATH', dirname(__FILE__) . '/');}
      if(file_exists(ABSPATH . 'wp-admin/includes/image.php')){ 
      require_once(ABSPATH . 'wp-admin/includes/image.php');}
        $attach_data = wp_generate_attachment_metadata( $attach_id, $attachment_path );
        wp_update_attachment_metadata( $attach_id, $attach_data );
      if($key==0){
        set_post_thumbnail( $postID, $attach_id );
			}
  }
} 