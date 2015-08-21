=== wpshop exchange for 1C:Enterprise ===
Contributors: wpshop, shurupp
Donate link: http://wp-shop.ru/donate/
Tags: shop, webmoney, robokassa, wallet one, russian, ukrainian, affiliate, authorize, cart, checkout, commerce, coupons, e-commerce, ecommerce, gifts, online, online shop, online store, paypal, paypal advanced, paypal express, paypal pro, physical, ready!, reports, sales, sell, shipping, shop, shopping, stock, stock control, store, tax, virtual, weights, widgets, wordpress ecommerce, wp e-commerce,1C
Requires at least: 3.7
Tested up to: 4.2.2
Stable tag: 0.2
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Provides data exchange between eCommerce plugin WP-Shop and business application "1C:Enterprise 8. Trade Management".

== Description ==

<p>Provides data exchange between eCommerce plugin WP-Shop and business application "1C:Enterprise 8. Trade Management".</p>

<h3>Key features : </h3>
<ul>
<li>Product exchange: group (categories), attributes and values, product list and product variations, images, properties, requisites, prices, remains for products.</li>
<li>Partial and full syncronization.</li>
<li>Effective usage of RAM on server.</li>
<li>Support for compressed data exchange.</li>
<li>Transactions and strict error checking: DB updates on successfull data exchange only.</li>
</ul>

<p>Предоставляет обмен данными между плагином для электронной коммерции WP-Shop и приложением для бизнеса "1C:Предприятие 8. Управление торговлей" (и совместимыми).</p>

<h3>Особенности: </h3>
<ul>
<li>Выгрузка товаров: группы (категории), свойства и значения, список товаров и вариантов, изображения, свойства, реквизиты, цены, остатки товаров.</li>
<li>Полная и частичная синхронизация.</li>
<li>Экономичное использование оперативной памяти сервера.</li>
<li>Поддержка передачи данных в сжатом виде.</li>
<li>Транзакционность и строгая проверка на ошибки: данные обновляются в БД только в случае успешного обмена.</li>
</ul>

== Installation ==
<p><strong>Подробные инструкции по установке и работе с этим плагином смотрите в <a href="http://wp-shop.ru/1c/">документации на сайте wp-shop.ru</a></strong></p>
<p>Необходимо учесть, что для обмена большими объемами данных может понадобиться произвести дополнительную настройку веб-сервера. На недорогих shared-хостингах часто такой возможности нет, а настроены они под крайне консервативный режим работы. Поэтому рекомендуется использовать VPS/VDS-хостинги. Поэтому рекомендуется использовать проверенные хостинги, предоставляющие большую нагрузку за меньшую плату. Например этот - <a href="http://wp-shop.ru/hosting.php">ХостЛенд</a></p>
<h4>Настройка</h4>

<p>Вначале вам необходимо установить и активировать плагин WP-Shop, т.к. этот плагин зависит от него. Для этого зайдите в панель управления WordPress, выберите "Плагины" → "Добавить новый". В поисковом поле введите название плагина (или часть) и кликните "Искать плагины". Установите найденный плагин, кликнув "Установить сейчас".</p>

<p>В 1С в качестве адреса в настройках обмена с сайтом необходимо один из адресов вида:</p>

<ul>
<li><a href="http://example.com/wp-content/plugins/wpshop1c/exchange.php" rel="nofollow">http://example.com/wp-content/plugins/wpshop1c/exchange.php</a></li>
<li>или <a href="http://example.com/wpshop1c/exchange" rel="nofollow">http://example.com/wpshop1c/exchange</a>, если на сайте включены постоянные ссылки ("Настройки" → "Постоянные ссылки")</li>
</ul>

<p>где example.com – доменное имя сайта интернет-магазина.</p>

<p>В качестве имени пользователя и пароля в 1С следует указать действующие на сайте имя и пароль активного пользователя с ролью "Shop Manager" или Администратор.</p>


<h4>Технические рекомендации</h4>

<p>Рекомендуется изменить тип хранилища всех таблиц базы данных сайта на InnoDB. Это добавит транзакционность в процесс обмена данными: изменения в базе данных сайта будут применяться только в случае успешного завершения процесса обмена.</p>

<p>Выполнение PHP на сервере необходимо настроить так, чтобы не было лимитов на время исполнения скриптов плагина. В случае использования связки Apache + mod_php (рекомендуется как наиболее простая связка) при дефолтных настройках лимита не будет. В случае использования FastCGI и/или nginx может потребоваться дополнительная их настройка для снятия лимитов на время исполнения (например, изменение FcgidConnectTimeout для mod_fcgid; request_terminate_timeout, fastcgi_read_timeout для nginx).</p>

<p>1С закачивает на сервер выгрузку с помощью POST-запроса. Возможно, понадобится увеличить лимит объема данных, отправляемых по POST. В php.ini за это отвечает значение post_max_size. В случае использования FastCGI и/или nginx может понадобится увеличить этот лимит также в их настройках (например, FcgidMaxRequestLen для mod_fcgid; client_max_body_size, send_timeout для nginx).</p>

<p>Если PHP выполняется в режиме FastCGI, а 1С при проверке соединения с сервером просит проверить имя пользователя и пароль, хотя они указаны верно, то необходимо в файл .htaccess после строки <code>RewriteEngine On</code> вставить строку <code>RewriteRule . - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]</code>, а также попробовать оба варианта адреса обмена (полный и короткий). Необходимо учесть, что изменения в .htaccess перезатираются при сохранении настроек постоянных ссылок и некоторых плагинов из админки WordPress.</p></div>

== Frequently asked questions ==

= A question that someone might have =

Visit the site wp-shop.ru for help.

== Screenshots ==

1. Настройка обмена с сайтом 
2. Создание новой настройки
3. Настройка соединения
4. Включение выгрузки картинок
5. Настройка выгрузки цен
6. Проверка соединения
7. Выполнение обмена
8. Вывод параметров обмена

== Changelog ==
Version: 0.1
-initial relese

Version: 0.2
-description of goods
== Upgrade notice ==

== Arbitrary section 1 ==