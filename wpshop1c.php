<?php 
/*
 Plugin Name: WP-Shop_1c_exchange
 Plugin URI: http://www.wp-shop.ru
 Description: Интеграция с 1C:Предприятие для плагина WP-Shop.
 Author: www.wp-shop.ru
 Version: 0.2
 Author URI: http://www.wp-shop.ru
 */


//error_reporting(E_ALL);
//ini_set("display_errors", 1);


$upload_dir = wp_upload_dir();
define('wpshop1c_DATA_DIR', "{$upload_dir['basedir']}/wpshop1c/");
define('wpshop1c_UPLOAD_URL', "{$upload_dir['url']}/wpshop1c/");
define( 'wpshop1c_PLUGIN_DIR' , dirname(realpath(__FILE__))."/");
define( 'WPSHOP_VER', '3.4.3.17');

if (!is_dir(wpshop1c_DATA_DIR)) mkdir(wpshop1c_DATA_DIR);
file_put_contents(wpshop1c_DATA_DIR . ".htaccess", "Deny from all");
require_once wpshop1c_PLUGIN_DIR . "/exchange.php";

function wpshop_plugin_activate_1c() {
	$installer = new Wpshop_Installer_1c();
}
register_activation_hook( __FILE__, 'wpshop_plugin_activate_1c' );

class Wpshop_Installer_1c
{
	private $wpdb;
	private $tables = array(
							'wpshop1c_categs'=> array('columns' => array(
												array('Field'=>'cat_id'),
												array('Field'=>'cat_kode'),
												array('Field'=>'cat_avail'),
												array('Field'=>'cat_name'),
												array('Field'=>'parent_cat_kode'),
												array('Field'=>'affiliate_id')
											)
								),
							'wpshop1c_offers'=> array('columns' => array(
												array('Field'=>'offer_id'),
												array('Field'=>'offer_kode'),
												array('Field'=>'offer_avail'),
												array('Field'=>'offer_name'),
												array('Field'=>'offer_art'),
												array('Field'=>'offer_desc'),
												array('Field'=>'offer_cat'),
												array('Field'=>'offer_pic'),
												array('Field'=>'offer_atribs'),
												array('Field'=>'offer_common_price'),
												array('Field'=>'offer_common_sklad'),
												array('Field'=>'offer_common_koef'),
												array('Field'=>'offer_prices'),
												array('Field'=>'offer_time'),
												array('Field'=>'affiliate_id')
											)
							),
							'wpshop1c_atributs'=> array('columns' => array(
												array('Field'=>'attr_id'),
												array('Field'=>'attr_id_znach'),
												array('Field'=>'attr_name_znach'),
												array('Field'=>'attr_id_svoystv'),
												array('Field'=>'attr_name_svoystv'),
                        array('Field'=>'attr_avail')
											)
							)
							
	);
	public function __construct()
	{
		global $wpdb;
		$this->wpdb = $wpdb;
		$this->createTables_1c_wpshop();
  }

	private function checkTable_1c_wpshop($tableName)
	{
		$actualColumns = $this->wpdb->get_results("SHOW COLUMNS FROM `{$this->wpdb->prefix}{$tableName}`;");
		foreach($this->tables[$tableName]['columns'] as $neededColumn)
		{
			$find = false;
			foreach($actualColumns as $column)
			{
				if ($neededColumn['Field'] == $column->Field)
				{
					$find = true;
					break;
				}
			}
			if (!$find)
			{
				return false;
			}
		}
		return true;
	}


	private function dropTable_1c_wpshop($tableName)
	{
		$this->wpdb->query("DROP TABLE `{$this->wpdb->prefix}{$tableName}`;");
		//echo mysql_error();
	}

	/**
	 * Создает таблицы для сохранения заказов
	 */
	private function createTables_1c_wpshop()
	{
    if (!$this->checkTable_1c_wpshop('wpshop1c_categs'))
		{
			$this->dropTable_1c_wpshop('wpshop1c_categs');
			$sql = "CREATE TABLE `{$this->wpdb->prefix}wpshop1c_categs`
				(
					`cat_id` bigint(20) NOT NULL auto_increment,
					`cat_kode` varchar(255) NULL,
					`cat_avail` int(11)  NULL,
					`cat_name` varchar(255) NULL,
					`parent_cat_kode` varchar(255) NULL,
					`affiliate_id` int(11)  NULL,
					PRIMARY KEY ( `cat_id` )
				) ENGINE = INNODB DEFAULT CHARSET=utf8;";
			$this->wpdb->query($sql);
		}
    
    if (!$this->checkTable_1c_wpshop('wpshop1c_offers'))
		{
			$this->dropTable_1c_wpshop('wpshop1c_offers');
			$sql = "CREATE TABLE `{$this->wpdb->prefix}wpshop1c_offers`
				(
				  `offer_id` bigint(20) NOT NULL auto_increment,
				  `offer_kode` varchar(255)  NULL,
				  `offer_avail` int(11)  NULL,
				  `offer_name` varchar(255)  NULL,
				  `offer_art` varchar(255)  NULL,
				  `offer_desc` longtext  NULL,
				  `offer_cat` varchar(255)  NULL,
				  `offer_pic` varchar(255)  NULL,
				  `offer_atribs` varchar(500)  NULL,
				  `offer_common_price` bigint(20)  NULL,
				  `offer_common_sklad` bigint(20)  NULL,
				  `offer_common_koef` bigint(20)  NULL,
				  `offer_prices` varchar(1500)  NULL,
				  `offer_time` varchar(255)  NULL,
				  `affiliate_id` int(11)  NULL,
				  PRIMARY KEY ( `offer_id` )
				) ENGINE = INNODB DEFAULT CHARSET=utf8;";

			$this->wpdb->query($sql);
		}
		
	if (!$this->checkTable_1c_wpshop('wpshop1c_atributs'))
		{
			$this->dropTable_1c_wpshop('wpshop1c_atributs');
			$sql = "CREATE TABLE `{$this->wpdb->prefix}wpshop1c_atributs`
				(
					`attr_id` bigint(20) NOT NULL auto_increment,
					`attr_id_znach` varchar(255)  NULL,
					`attr_name_znach` varchar(255)  NULL,
					`attr_id_svoystv` varchar(255)  NULL,
					`attr_name_svoystv` varchar(255)  NULL,
					`attr_avail` int(11)  NULL,
					PRIMARY KEY ( `attr_id` )
				) ENGINE = INNODB DEFAULT CHARSET=utf8;";

			$this->wpdb->query($sql);
		}
  }       
}