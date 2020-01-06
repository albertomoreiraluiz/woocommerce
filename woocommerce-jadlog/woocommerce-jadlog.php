<?php
/**
 * Plugin Name: WooCommerce Jadlog
 * Plugin URI: https://www.jadlog.com.br/
 * Description: Jadlog Shipping Module for WooCommerce 3
 * Version: 0.0.1
 * Author: Jadlog Logística
 * Author URI: https://www.jadlog.com.br/
 * License: Open Software License (OSL 3.0) - http://opensource.org/licenses/osl-3.0.php
 * Text Domain: woocommerce-jadlog
 */

session_start();

/* Exit if accessed directly */
if (!defined('ABSPATH'))
    exit;

class WooCommerceJadlog
{
    function Jadlog_main()
    {
        include_once(ABSPATH.'wp-admin/includes/plugin.php');
        if (is_plugin_active('woocommerce/woocommerce.php'))
            add_action('plugins_loaded', array($this, 'init'), 8);
    }

    function init()
    {
        define('JADLOG_FILE_PATH', plugin_dir_path(__FILE__));
        define('JADLOG_ROOT_URL', plugins_url('', __FILE__));

        /* Add DPD France Tools and Settings tabs */
        add_action('admin_menu', array($this, 'add_export_tab'));
        add_filter('woocommerce_settings_tabs_array', array( $this, 'add_settings_tab'), 50);
        add_action('woocommerce_settings_tabs_jadlog_shipping', array($this, 'settings_tab'));
        add_action('woocommerce_update_options_jadlog_shipping', array($this, 'update_settings'));

        /* Install DPD Relais */
        require_once(JADLOG_FILE_PATH . '/classes/jadlogShippingInit.php');

        /*Showing Map*/
        function woocommerce_jadlog_show_map_pudos()
        {

            wp_enqueue_script( 'jadlog_custom_script',JADLOG_ROOT_URL.'/assets/js/front/relais/dpdfrance_relais.js', array('jquery'), '0.3' );
            wp_enqueue_script( 'jadlog_custom_map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyChTnVGxD1-K4LLL5VKgDKFtsoqApnxITI', '');
            wp_enqueue_style( 'jadlog_custom_css',JADLOG_ROOT_URL.'/assets/css/front/dpdfrance_shipping.css');

            ?>
            <link rel="stylesheet" href="https://openlayers.org/en/v4.4.2/css/ol.css" type="text/css">
            <!-- The line below is only needed for old environments like Internet Explorer and Android 4.x -->
            <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL"></script>
            <script src="https://openlayers.org/en/v4.4.2/build/ol.js"></script>
            <script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>

            <script>
                function create_map(latitude, longitude) {
                    var layer;
                    var pos;
                    var marker;
                    var long;
                    var lat;
                    var map;

                    lat = latitude;
                    long = longitude;

                    marker = new ol.Overlay({
                        position: ol.proj.fromLonLat([parseFloat(long), parseFloat(lat)]),
                        positioning: 'center-center',
                        element: document.getElementById('marker'),
                        stopEvent: false
                    });

                    $('#map').html('');

                    map = new ol.Map({
                        layers: [
                            new ol.layer.Tile({
                                source: new ol.source.OSM()
                            })
                        ],
                        target: 'map',
                        view: new ol.View({
                            center: ol.proj.transform([parseFloat(lat), parseFloat(long)], 'EPSG:3857', 'EPSG:4326'),
                            zoom: 10
                        })
                    });

                    map.addOverlay(marker);

                    map.getView().setCenter(ol.proj.transform([parseFloat(long), parseFloat(lat)], 'EPSG:4326', 'EPSG:3857'));

                }

                jQuery(document.body).on('update_checkout', function(e){

                    var metodo = $('input[name="shipping_method[0]"]:checked').val();
                    if (metodo !== undefined) {
                        if (metodo.indexOf("BR") != -1) {
                            $.ajax({
                                type : "POST",
                                url : "<?= JADLOG_ROOT_URL; ?>/ajax/return_pudo.php",
                                data : {pudo_id: metodo},
                                success: function(response) {
                                    address = JSON.parse(response).address;
                                    latitude = JSON.parse(response).latitude;
                                    longitude = JSON.parse(response).longitude;

                                    var seg = 0;
                                    var ter = 0;
                                    var qua = 0;
                                    var qui = 0;
                                    var sex = 0;
                                    var sab = 0;
                                    var dom = 0;
                                    JSON.parse(response).time.OPENING_HOURS_ITEM.forEach (function ShowResults(value, index, ar) {
                                        if (value.DAY_ID == 1) {
                                            if (seg == 0) {
                                                document.getElementById('seg_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('seg_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            seg++;
                                        }
                                        if (value.DAY_ID == 2) {
                                            if (ter == 0) {
                                                document.getElementById('ter_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('ter_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            ter++;
                                        }
                                        if (value.DAY_ID == 3) {
                                            if (qua == 0) {
                                                document.getElementById('qua_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('qua_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            qua++;
                                        }
                                        if (value.DAY_ID == 4) {
                                            if (qui == 0) {
                                                document.getElementById('qui_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('qui_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            qui++;
                                        }
                                        if (value.DAY_ID == 5) {
                                            if (sex == 0) {
                                                document.getElementById('sex_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('sex_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            sex++;
                                        }
                                        if (value.DAY_ID == 6) {
                                            if (sab == 0) {
                                                document.getElementById('sab_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('sab_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            sab++;
                                        }
                                        if (value.DAY_ID == 7) {
                                            if (dom == 0) {
                                                document.getElementById('dom_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('dom_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            dom++;
                                        }
                                    });

                                    $('.maske').show();
                                    $('.modales').show();

                                    document.getElementById('address').innerHTML = address;

                                    create_map(latitude, longitude);
                                },
                                error: function (e) {
                                    console.log(e);
                                }
                            });

                            e.stopPropagation();
                        }
                    }


                });

                $(document).ready(function () {

                    $('.maske').hide();
                    $('.modales').hide();

                    $('.maske').click(function () {
                        $('.maske').hide();
                        $('.modales').hide();
                    });

                });
            </script>

            <style>
                .maske{
                    position: fixed;
                    width: 100vw;
                    height: 100vh;
                    z-index: 9999;
                    background-color: rgba(0,0,10,0.3);
                    left: 0;
                    top: 0;
                    display: flex;
                }

                .modales{
                    width: 800px;
                    height: 70%;
                    z-index: 199;
                    padding-left: 15px;
                    padding-right: 15px;
                    order: 1;
                    border-radius: 5px;
                    margin: auto    ;
                    vertical-align: middle;
                    horiz-align: center;
                    background: rgba(255,255,255,1);
                    top: 1%;
                    overflow: auto;
                }

                table{
                    font-size: 14px;
                    text-align: center;
                    max-width: 100%;
                }

                .map {
                    height: 50%;
                    width: 100%;
                }

                #marker {
                    width: 20px;
                    height: 20px;
                    border: 1px solid #088;
                    border-radius: 10px;
                    background-color: #0FF;
                    opacity: 0.5;
                }

                .marker {
                    display: none;
                }

                #address {
                    text-decoration: none;
                    color: #333333;
                    font-size: 12pt;
                    font-weight: normal;
                }

                #time {
                    text-decoration: none;
                    color: #333333;
                    font-size: 11pt;
                    font-weight: normal;
                }

                .information {
                    height: 30%;
                    width: 100%;
                }

                .information table * {
                    list-style: none;
                    font-size: 11px;
                    text-align: center;
                    max-width: 100%;
                }
            </style>

            <div class="maske">
                <div class="modales">
                    <h3>Veja seu ponto de coleta</h3>
                    <div id="map" class="map"></div>
                    <div class="information">
                        <h4>Veja o endereço e horário de funcionamento:</h4>
                        <p id="address">enderço</p>
                        <p id="time">
                            Funcionamento:
                            <table>
                                <tr>
                                    <td>Segunda:</td>
                                    <td>Terça:</td>
                                    <td>Quarta:</td>
                                    <td>Quinta:</td>
                                    <td>Sexta:</td>
                                    <td>Sábado:</td>
                                    <td>Domingo:</td>
                                </tr>
                                <tr>
                                    <td id="seg_1"></td>
                                    <td id="ter_1"></td>
                                    <td id="qua_1"></td>
                                    <td id="qui_1"></td>
                                    <td id="sex_1"></td>
                                    <td id="sab_1"></td>
                                    <td id="dom_1"></td>
                                </tr>
                                <tr>
                                    <td id="seg_2"></td>
                                    <td id="ter_2"></td>
                                    <td id="qua_2"></td>
                                    <td id="qui_2"></td>
                                    <td id="sex_2"></td>
                                    <td id="sab_2"></td>
                                    <td id="dom_2"></td>
                                </tr>
                            </table>
                        </p>
                    </div>
                </div>
            </div>

            <div id="marker" title="Marker"></div>

            <?php
        }

        // [10/12/2019] Do not show maps for now
        // add_action('woocommerce_checkout_shipping', 'woocommerce_jadlog_show_map_pudos');

        function test( ) {
            wp_enqueue_script( 'jadlog_custom_script',JADLOG_ROOT_URL.'/assets/js/front/relais/dpdfrance_relais.js', array('jquery'), '0.3' );
            wp_enqueue_script( 'jadlog_custom_map', 'https://maps.googleapis.com/maps/api/js?key=AIzaSyChTnVGxD1-K4LLL5VKgDKFtsoqApnxITI', '');
            wp_enqueue_style( 'jadlog_custom_css',JADLOG_ROOT_URL.'/assets/css/front/dpdfrance_shipping.css');
            ?>
            </table>
            <link rel="stylesheet" href="https://openlayers.org/en/v4.4.2/css/ol.css" type="text/css">
            <!-- The line below is only needed for old environments like Internet Explorer and Android 4.x -->
            <script src="https://cdn.polyfill.io/v2/polyfill.min.js?features=requestAnimationFrame,Element.prototype.classList,URL"></script>
            <script src="https://openlayers.org/en/v4.4.2/build/ol.js"></script>
            <script src="https://code.jquery.com/jquery-2.2.3.min.js"></script>
            <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
            <script>
                function create_map(latitude, longitude) {
                    var layer;
                    var pos;
                    var marker;
                    var long;
                    var lat;
                    var map;

                    lat = latitude;
                    long = longitude;

                    marker = new ol.Overlay({
                        position: ol.proj.fromLonLat([parseFloat(long), parseFloat(lat)]),
                        positioning: 'center-center',
                        element: document.getElementById('marker'),
                        stopEvent: false
                    });

                    $('#map').html('');

                    map = new ol.Map({
                        layers: [
                            new ol.layer.Tile({
                                source: new ol.source.OSM()
                            })
                        ],
                        target: 'map',
                        view: new ol.View({
                            center: ol.proj.transform([parseFloat(lat), parseFloat(long)], 'EPSG:3857', 'EPSG:4326'),
                            zoom: 10
                        })
                    });

                    map.addOverlay(marker);

                    map.getView().setCenter(ol.proj.transform([parseFloat(long), parseFloat(lat)], 'EPSG:4326', 'EPSG:3857'));

                }

                $(document).ready(function () {
                    var metodo = $('input[name="shipping_method[0]"]:checked').val();
                    if (metodo !== undefined) {
                        if (metodo.indexOf("BR") != -1) {
                            $.ajax({
                                type : "POST",
                                url : "<?= JADLOG_ROOT_URL; ?>/ajax/return_pudo.php",
                                data : {pudo_id: metodo},
                                success: function(response) {
                                    address = JSON.parse(response).address;
                                    latitude = JSON.parse(response).latitude;
                                    longitude = JSON.parse(response).longitude;

                                    var seg = 0;
                                    var ter = 0;
                                    var qua = 0;
                                    var qui = 0;
                                    var sex = 0;
                                    var sab = 0;
                                    var dom = 0;
                                    JSON.parse(response).time.OPENING_HOURS_ITEM.forEach (function ShowResults(value, index, ar) {
                                        if (value.DAY_ID == 1) {
                                            if (seg == 0) {
                                                document.getElementById('seg_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('seg_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            seg++;
                                        }
                                        if (value.DAY_ID == 2) {
                                            if (ter == 0) {
                                                document.getElementById('ter_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('ter_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            ter++;
                                        }
                                        if (value.DAY_ID == 3) {
                                            if (qua == 0) {
                                                document.getElementById('qua_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('qua_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            qua++;
                                        }
                                        if (value.DAY_ID == 4) {
                                            if (qui == 0) {
                                                document.getElementById('qui_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('qui_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            qui++;
                                        }
                                        if (value.DAY_ID == 5) {
                                            if (sex == 0) {
                                                document.getElementById('sex_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('sex_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            sex++;
                                        }
                                        if (value.DAY_ID == 6) {
                                            if (sab == 0) {
                                                document.getElementById('sab_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('sab_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            sab++;
                                        }
                                        if (value.DAY_ID == 7) {
                                            if (dom == 0) {
                                                document.getElementById('dom_1').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            } else {
                                                document.getElementById('dom_2').innerHTML = value.START_TM + ' - ' + value.END_TM;
                                            }
                                            dom++;
                                        }
                                    });

                                    $('.maske').show();
                                    $('.maske').removeClass("hidden");
                                    $('.modales').show();
                                    $('.marker').show();

                                    document.getElementById('address').innerHTML = address;

                                    create_map(latitude, longitude);
                                },
                                error: function (e) {
                                    console.log(e);
                                }
                            });
                        } else {
                            $('.maske').hide();
                            $('.maske').addClass("hidden");
                            $('.modales').hide();
                            $('.marker').hide();
                        }
                    } else {
                        $('.maske').hide();
                        $('.maske').addClass("hidden");
                        $('.modales').hide();
                        $('.marker').hide();
                    }

                    $('.maske').click(function () {
                        $('.maske').hide();
                        $('.maske').addClass("hidden");
                        $('.modales').hide();
                        $('.marker').hide();
                    });
                });
            </script>

            <style>
                .maske{
                    position: fixed;
                    width: 100vw;
                    height: 100vh;
                    z-index: 9999;
                    background-color: rgba(0,0,10,0.3);
                    left: 0;
                    top: 0;
                    display: flex;
                }

                .maske.hidden{
                    visibility: hidden;
                }

                .modales{
                    width: 800px;
                    height: 70%;
                    z-index: 199;
                    padding-left: 15px;
                    padding-right: 15px;
                    order: 1;
                    border-radius: 5px;
                    margin: auto    ;
                    vertical-align: middle;
                    horiz-align: center;
                    background: rgba(255,255,255,1);
                    top: 1%;
                    overflow: auto;
                }

                table{
                    font-size: 14px;
                    text-align: center;
                    max-width: 100%;
                }

                .map {
                    height: 50%;
                    width: 100%;
                }

                #marker {
                    width: 20px;
                    height: 20px;
                    border: 1px solid #088;
                    border-radius: 10px;
                    background-color: #0FF;
                    opacity: 0.5;
                }

                .marker {
                    display: none;
                }

                #address {
                    text-decoration: none;
                    color: #333333;
                    font-size: 12pt;
                    font-weight: normal;
                }

                #time {
                    text-decoration: none;
                    color: #333333;
                    font-size: 11pt;
                    font-weight: normal;
                }

                .information {
                    height: 30%;
                    width: 100%;
                }

                .information table * {
                    list-style: none;
                    font-size: 11px;
                    text-align: center;
                    max-width: 100%;
                }
            </style>

            <div class="maske hidden">
                <div class="modales">
                    <h3>Veja seu ponto de coleta</h3>
                    <div id="map" class="map"></div>
                    <div class="information">
                        <h4>Veja o endereço e horário de funcionamento:</h4>
                        <p id="address">enderço</p>
                        <p id="time">
                            Funcionamento:
                            <table>
                                <tr>
                                    <td>Segunda:</td>
                                    <td>Terça:</td>
                                    <td>Quarta:</td>
                                    <td>Quinta:</td>
                                    <td>Sexta:</td>
                                    <td>Sábado:</td>
                                    <td>Domingo:</td>
                                </tr>
                                <tr>
                                    <td id="seg_1"></td>
                                    <td id="ter_1"></td>
                                    <td id="qua_1"></td>
                                    <td id="qui_1"></td>
                                    <td id="sex_1"></td>
                                    <td id="sab_1"></td>
                                    <td id="dom_1"></td>
                                </tr>
                                <tr>
                                    <td id="seg_2"></td>
                                    <td id="ter_2"></td>
                                    <td id="qua_2"></td>
                                    <td id="qui_2"></td>
                                    <td id="sex_2"></td>
                                    <td id="sab_2"></td>
                                    <td id="dom_2"></td>
                                </tr>
                            </table>
                        </p>
                    </div>
                </div>
            </div>

            <div id="marker" title="Marker" class="marker"></div>
            <?php
        };

        // [10/12/2019] Do not show maps for now
        // add_action('woocommerce_cart_totals_after_shipping', 'test');
    }

    /* Installs plugin */
    function install()
    {
        global $wp_version;

        if (!is_plugin_active('woocommerce/woocommerce.php'))
        {
            deactivate_plugins(plugin_basename(__FILE__)); /* Deactivate plugin */
            wp_die(__('You must run WooCommerce 3.x to install WooCommerce Jadlog plugin', 'jadlog'), __('WC not activated', 'jadlog'), array('back_link' => true));
            return;
        }

        if (!is_plugin_active('woocommerce-extra-checkout-fields-for-brazil/woocommerce-extra-checkout-fields-for-brazil.php'))
        {
            deactivate_plugins(plugin_basename(__FILE__)); /* Deactivate plugin */
            wp_die(__('You must run Brazilian Market on WooCommerce 3.7.x to install WooCommerce Jadlog plugin', 'jadlog'), __('Brazilian Market on WooCommerce not activated', 'jadlog'), array('back_link' => true));
            return;
        }

        if ((float)$wp_version < 3.5)
        {
            deactivate_plugins(plugin_basename(__FILE__)); /* Deactivate plugin */
            wp_die(__('You must run at least WordPress version 3.5 to install WooCommerce Jadlog plugin', 'jadlog'), __('WP not compatible', 'jadlog'), array('back_link' => true));
            return;
        }

        define('JADLOG_FILE_PATH', dirname(__FILE__));

        /* Install DB tables */
        include_once('controllers/admin/jadlog-shipping-install-table.php');
        install_table();
    }

    /* Add Jadlog tab under Tools */
    function add_export_tab()
    {
        add_submenu_page('woocommerce', __('Jadlog', 'jadlog'), __('Jadlog', 'jadlog'), 'manage_woocommerce', 'jadlog', array($this, 'display_export_page'), 8);
    }

    /* Add Jadlog settings tab */
    public function add_settings_tab( $settings_tabs )
    {
        $settings_tabs['jadlog_shipping'] = __( 'Jadlog', 'jadlog' );
        return $settings_tabs;
    }

    /* Builds Jadlog settings tab display */
    public function settings_tab()
    {
        echo "<style media=\"screen\" type=\"text/css\">
            #mainform label {
                display: block;
                font-weight: bold;
                padding: 10px 0 0 0;
            }
            </style>
            <div class=\"updated woocommerce-message\">
                <p><strong>".__('Por favor, faça a configuração do plugin Jadlog.', 'jadlog')."</strong></p>
            </div>";
        echo "<h3>".__('Configuração gerais', 'jadlog')."</h3>";
        woocommerce_admin_fields( $this->get_shipments_settings() );
        echo "<h3>".__('Modalidade Jadlog Expresso', 'jadlog')."</h3>";
        woocommerce_admin_fields( $this->get_expresso_settings() );
        echo "<h3>".__('Modalidade Jadlog Pickup', 'jadlog')."</h3>";
        woocommerce_admin_fields( $this->get_pickup_settings() );
        echo "<h3>".__('Dados do remetente', 'jadlog')."</h3>";
        woocommerce_admin_fields( $this->get_shipperdata_settings() );
    }

    /* Save settings in tab */
    public function update_settings()
    {
        woocommerce_update_options( $this->get_shipments_settings() );
        woocommerce_update_options( $this->get_expresso_settings() );
        woocommerce_update_options( $this->get_pickup_settings() );
        woocommerce_update_options( $this->get_shipperdata_settings() );
    }

    public function get_shipments_settings()
    {
        include_once('classes/Modalidade.php');
        include_once('classes/TipoColeta.php');
        include_once('classes/TipoFrete.php');
        include_once('classes/TipoServico.php');

        $settings = array(
            'JADLOG_URL_EMBARCADOR_SIMULADOR_FRETE' => array(
                'name'     => __('URL da API de simulação de frete (Embarcador)', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:500px;',
                'desc'     => '',
                'default'  => 'http://www.jadlog.com.br/embarcador/api/frete/valor',
                'id'       => 'wc_settings_tab_jadlog_url_simulador_frete'
            ),
            'JADLOG_URL_EMBARCADOR_INCLUSAO_PEDIDOS' => array(
                'name'     => __('URL da API de inclusão de pedidos (Embarcador)', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:500px;',
                'desc'     => '',
                'default'  => 'http://www.jadlog.com.br/embarcador/api/pedido/incluir',
                'id'       => 'wc_settings_tab_jadlog_url_inclusao_pedidos'
            ),
            'JADLOG_URL_EMBARCADOR_CANCELAMENTO_PEDIDOS' => array(
                'name'     => __('URL da API de cancelamento de pedidos (Embarcador)', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:500px;',
                'desc'     => '',
                'default'  => 'http://www.jadlog.com.br/embarcador/api/pedido/cancelar',
                'id'       => 'wc_settings_tab_jadlog_url_cancelamento_pedidos'
            ),
            'JADLOG_URL_EMBARCADOR_CONSULTA_PEDIDOS' => array(
                'name'     => __('URL da API de consulta de pedidos (Embarcador)', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:500px;',
                'desc'     => '',
                'default'  => 'http://www.jadlog.com.br/embarcador/api/tracking/consultar',
                'id'       => 'wc_settings_tab_jadlog_url_consulta_pedidos'
            ),
            'JADLOG_KEY_EMBARCADOR' => array(
                'name'     => __('Chave de acesso ao Embarcador', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:750px;',
                'desc'     => 'Deve começar com a palavra "Bearer"',
                'id'       => 'wc_settings_tab_jadlog_key_embarcador'
            ),
            'JADLOG_CODIGO_CLIENTE' => array(
                'name'     => __('Código do Cliente Jadlog', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_codigo_cliente'
            ),
            'JADLOG_CONTA_CORRENTE' => array(
                'name'     => __('Conta Corrente Jadlog', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_conta_corrente'
            ),
            'JADLOG_SENHA' => array(
                'name'     => __('Senha Jadlog', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_senha'
            ),
            'JADLOG_TIPO_COLETA' => array(
                'name'     => __('Tipo de Coleta Jadlog', 'jadlog'),
                'type'     => 'select',
                'options'  => TipoColeta::TODOS,
                'default'  => TipoColeta::COD_REMETENTE,
                'id'       => 'wc_settings_tab_jadlog_tipo_coleta'
            ),
            'JADLOG_TIPO_FRETE' => array(
                'name'     => __('Tipo de Frete Jadlog', 'jadlog'),
                'type'     => 'select',
                'options'  => TipoFrete::TODOS,
                'default'  => TipoFrete::COD_NORMAL,
                'id'       => 'wc_settings_tab_jadlog_tipo_frete'
            ),
            'JADLOG_UNIDADE_ORIGEM' => array(
                'name'     => __('Código da Unidade de Origem Jadlog', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_unidade_origem'
            ),
            'JADLOG_CONTRATO' => array(
                'name'     => __('Número do Contrato Jadlog', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'id'       => 'wc_settings_tab_jadlog_contrato'
            ),
            'JADLOG_SERVICO' => array(
                'name'     => __('Tipo do Serviço Jadlog', 'jadlog'),
                'type'     => 'select',
                'options'  => TipoServico::TODOS,
                'default'  => TipoServico::COD_COM_PIN,
                'id'       => 'wc_settings_tab_jadlog_servico'
            ),
        );
        return $settings;
    }

    public function get_expresso_settings() {
        include_once('classes/TipoEntrega.php');
        include_once('classes/TipoSeguro.php');

        $settings = array(
            'JADLOG_MODALIDADE_EXPRESSO' => array(
                'name'     => '',
                'desc'     => __('Ativar a modalidade de transporte Jadlog Expresso', 'jadlog'),
                'desc_tip' => __('Marque esta opção se deseja utilizar a modalidade de transporte Jadlog Expresso', 'jadlog'),
                'type'     => 'checkbox',
                'default'  => 'no',
                'id'       => 'wc_settings_tab_jadlog_modalidade_expresso'
            ),
            'JADLOG_FRAP_EXPRESSO' => array(
                'name'     => '',
                'desc'     => __('FRAP', 'jadlog'),
                'desc_tip' => __('Marque esta opção se deseja que a cobrança de frete seja feita no destino na modalidade Expresso', 'jadlog'),
                'type'     => 'checkbox',
                'default'  => 'no',
                'id'       => 'wc_settings_tab_jadlog_frap_expresso'
            ),
            'JADLOG_VALOR_COLETA_EXPRESSO' => array(
                'name'     => __('Valor de coleta', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'desc'     => __('Valor de coleta negociado com a Jadlog na modalidade Expresso', 'jadlog'),
                'id'       => 'wc_settings_tab_jadlog_valor_coleta_expresso'
            ),
            'JADLOG_TIPO_ENTREGA_EXPRESSO' => array(
                'name'     => __('Tipo de entrega', 'jadlog'),
                'type'     => 'select',
                'desc_tip' => 'Na modalidade Expresso',
                'options'  => TipoEntrega::TODOS,
                'default'  => TipoEntrega::COD_DOMICILIO,
                'id'       => 'wc_settings_tab_jadlog_tipo_entrega_expresso'
            ),
            'JADLOG_TIPO_SEGURO_EXPRESSO' => array(
                'name'     => __('Tipo do seguro', 'jadlog'),
                'type'     => 'select',
                'desc_tip' => 'Na modalidade Expresso',
                'options'  => TipoSeguro::TODOS,
                'default'  => TipoSeguro::COD_NORMAL,
                'id'       => 'wc_settings_tab_jadlog_tipo_seguro_expresso'
            ),
            'JADLOG_CALCULAR_PESOS_CUBADOS_EXPRESSO' => array(
                'name'     => __('Calcular pesos cubados na modalidade Expresso', 'jadlog'),
                'type'     => 'select',
                'options'  => array(
                    'PADRAO'                     => __('Usar fator de cubagem padrão: ', 'jadlog').__(Modalidade::modal(Modalidade::COD_EXPRESSO), 'jadlog'),
                    Modalidade::MODAL_RODOVIARIO => 'Usar fator de cubagem rodoviário',
                    'NAO_CALCULAR'               => 'Não calcular cubagem'
                ),
                'default'  => 'PADRAO',
                'desc' => 
                    '<br/>'.__('Os pesos cubados são utilizados no cálculo do frete e dependem do modal contratado (aéreo ou rodoviário).', 'jadlog').
                    '<br/>'.__('Caso selecione um modal, cadastre os pesos reais dos produtos e suas dimensões.', 'jadlog').
                    '<br/>'.__('Caso selecione a opção para não calcular cubagem, no cadastro de produtos informe no campo peso o maior valor entre o peso real e o peso cubado do respectivo produto.', 'jadlog'),
                'id' => 'wc_settings_tab_jadlog_calcular_pesos_cubados_expresso'
            )
        );
        return $settings;
    }

    public function get_pickup_settings() {
        include_once('classes/TipoSeguro.php');

        $settings = array(
            'JADLOG_MODALIDADE_PICKUP' => array(
                'name'     => '',
                'desc'     => __('Ativar a modalidade de transporte Jadlog Pickup', 'jadlog'),
                'desc_tip' => __('Marque esta opção se deseja utilizar a modalidade de transporte Jadlog Pickup', 'jadlog'),
                'type'     => 'checkbox',
                'default'  => 'no',
                'id'       => 'wc_settings_tab_jadlog_modalidade_pickup'
            ),
            'JADLOG_MY_PUDO' => array(
                'name'     => __('URL da API de consulta de pontos Pickup', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:500px;',
                'desc'     => '',
                'default'  => 'http://mypudo.pickup-services.com/mypudo/mypudo.asmx/GetPudoList',
                'id'       => 'wc_settings_tab_jadlog_my_pudo'
            ),
            'JADLOG_KEY_PUDO' => array(
                'name'     => __('Chave de acesso Pickup', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:400px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_key_pudo'
            ),
            'JADLOG_QTD_PONTOS_PICKUP' => array(
                'name'     => __('Qtd de pontos pickup a mostrar', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'desc'     => __('Quantidade de pontos pickup a serem mostrados no carrinho de compras', 'jadlog'),
                'default'  => 5,
                'id'       => 'wc_settings_tab_jadlog_qtd_pontos_pickup'
            ),
            'JADLOG_FRAP_PICKUP' => array(
                'name'     => '',
                'desc'     => __('FRAP', 'jadlog'),
                'desc_tip' => __('Marque esta opção se deseja que a cobrança de frete seja feita no destino na modalidade Pickup', 'jadlog'),
                'type'     => 'checkbox',
                'default'  => 'no',
                'id'       => 'wc_settings_tab_jadlog_frap_pickup'
            ),
            'JADLOG_VALOR_COLETA_PICKUP' => array(
                'name'     => __('Valor de coleta', 'jadlog'),
                'type'     => 'text',
                'css'      => 'width:200px;',
                'desc'     => __('Valor de coleta negociado com a Jadlog na modalidade Pickup', 'jadlog'),
                'id'       => 'wc_settings_tab_jadlog_valor_coleta_pickup'
            ),
            'JADLOG_TIPO_ENTREGA_PICKUP' => array(
                'name'     => __('Tipo de entrega', 'jadlog'),
                'type'     => 'select',
                'desc_tip' => 'Na modalidade Pickup',
                'options'  => TipoEntrega::TODOS,
                'default'  => TipoEntrega::COD_DOMICILIO,
                'id'       => 'wc_settings_tab_jadlog_tipo_entrega_pickup'
            ),
            'JADLOG_TIPO_SEGURO_PICKUP' => array(
                'name'     => __('Tipo do seguro', 'jadlog'),
                'type'     => 'select',
                'desc_tip' => 'Na modalidade Pickup',
                'options'  => TipoSeguro::TODOS,
                'default'  => TipoSeguro::COD_NORMAL,
                'id'       => 'wc_settings_tab_jadlog_tipo_seguro_pickup'
            ),
            'JADLOG_CALCULAR_PESOS_CUBADOS_PICKUP' => array(
                'name'     => __('Calcular pesos cubados na modalidade Pickup', 'jadlog'),
                'type'     => 'select',
                'options'  => array(
                    'PADRAO'                     => __('Usar fator de cubagem padrão: ', 'jadlog').__(Modalidade::modal(Modalidade::COD_PICKUP), 'jadlog'),
                    Modalidade::MODAL_RODOVIARIO => 'Usar fator de cubagem rodoviário',
                    'NAO_CALCULAR'               => 'Não calcular cubagem'
                ),
                'default'  => 'PADRAO',
                'desc' => 
                    '<br/>'.__('Os pesos cubados são utilizados no cálculo do frete e dependem do modal contratado (aéreo ou rodoviário).', 'jadlog').
                    '<br/>'.__('Caso selecione um modal, cadastre os pesos reais dos produtos e suas dimensões.', 'jadlog').
                    '<br/>'.__('Caso selecione a opção para não calcular cubagem, no cadastro de produtos informe no campo peso o maior valor entre o peso real e o peso cubado do respectivo produto.', 'jadlog'),
                'id' => 'wc_settings_tab_jadlog_calcular_pesos_cubados_pickup'
            )
        );
        return $settings;
    }

    public function get_shipperdata_settings()
    {
        $settings = array(
            'shipper_name' => array(
                'name'     => __( 'Nome', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 60 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_name'
            ),
            'shipper_cnpj_cpf' => array(
                'name'     => __( 'CNPJ / CPF', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_shipper_cnpj_cpf'
            ),
            'shipper_ie' => array(
                'name'     => __( 'Inscrição estadual', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_shipper_ie'
            ),
            'shipper_endereco' => array(
                'name'     => __( 'Endereço', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 80 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_endereco'
            ),
            'shipper_numero' => array(
                'name'     => __( 'Número', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 10 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_numero'
            ),
            'shipper_complemento' => array(
                'name'     => __( 'Complemento', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 20 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_complemento'
            ),
            'shipper_bairro' => array(
                'name'     => __( 'Bairro', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 60 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_bairro'
            ),
            'shipper_cidade' => array(
                'name'     => __( 'Cidade', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 60 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_cidade'
            ),
            'shipper_uf' => array(
                'name'     => __( 'Sigla da UF', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(2 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_uf'
            ),
            'shipper_cep' => array(
                'name'     => __( 'CEP', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '',
                'id'       => 'wc_settings_tab_jadlog_shipper_cep'
            ),
            'shipper_fone' => array(
                'name'     => __( 'Telefone', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(formato: (11) 999999999)',
                'id'       => 'wc_settings_tab_jadlog_shipper_fone'
            ),
            'shipper_cel' => array(
                'name'     => __( 'Celular', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(formato: (11) 999999999)',
                'id'       => 'wc_settings_tab_jadlog_shipper_cel'
            ),
            'shipper_email' => array(
                'name'     => __( 'E-mail', 'jadlog' ),
                'type'     => 'email',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 100 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_email'
            ),
            'shipper_contato' => array(
                'name'     => __( 'Pessoa para contato', 'jadlog' ),
                'type'     => 'text',
                'css'      => 'width: 400px;',
                'desc'     => '(máximo de 50 caracteres)',
                'id'       => 'wc_settings_tab_jadlog_shipper_contato'
            ),
        );
        return $settings;
    }

    /* Uninstall plugin and DB tables */
    public function deactivate()
    {
        include_once('controllers/admin/jadlog-shipping-install-table.php');
        uninstall_table();
    }

    /* [BO] Orders management page */
    function display_export_page()
    {
        wp_enqueue_script( 'jquery-ui-dialog' );
        wp_enqueue_style( 'wp-jquery-ui-dialog' );

        global $wpdb;

        /* Display build */
        /* Loads scripts and page header */
        ?>
        <!-- <link rel="stylesheet" type="text/css" href="<?= JADLOG_ROOT_URL; ?>/assets/css/admin/AdminDPDFrance.css" -->
        <!--       xmlns="http://www.w3.org/1999/html"/> -->
        <link rel="stylesheet" type="text/css" href="<?= JADLOG_ROOT_URL; ?>/assets/js/jquery/plugins/fancybox/jquery.fancybox.css"/>
        <script type="text/javascript" src="<?= JADLOG_ROOT_URL; ?>/assets/js/jquery/plugins/marquee/jquery.marquee.min.js"></script>
        <script type="text/javascript" src="<?= JADLOG_ROOT_URL; ?>/assets/js/jquery/plugins/fancybox/jquery.fancybox.js"></script>
        <script type="text/javascript">
            var $ = jQuery.noConflict();
            $(document).ready(function(){
                $('.marquee').marquee({
                    duration: 20000,
                    gap: 50,
                    delayBeforeStart: 0,
                    direction: 'left',
                    duplicated: true,
                    pauseOnHover: true,
                    allowCss3Support: false,
                });
                $('a.popup').fancybox({
                    'hideOnContentClick': true,
                    'padding'           : 0,
                    'overlayColor'      :'#D3D3D3',
                    'overlayOpacity'    : 0.7,
                    'width'             : 1024,
                    'height'            : 640,
                    'type'              :'iframe'
                });
                $.expr[':'].contains = function(a, i, m) {
                    return $(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
                };

                $("#tableFilter").keyup(function () {
                    //split the current value of tableFilter
                    var data = this.value.split(";");
                    //create a jquery object of the rows
                    var jo = $("#the-list").find("tr");
                    if (this.value == "") {
                        jo.show();
                        return;
                    }
                    //hide all the rows
                    jo.hide();

                    //Recusively filter the jquery object to get results.
                    jo.filter(function (i, v) {
                        var t = $(this);
                        for (var d = 0; d < data.length; ++d) {
                            if (t.is(":contains('" + data[d] + "')")) {
                                return true;
                            }
                        }
                        return false;
                    })
                    //show the rows that match.
                        .show();
                }).focus(function () {
                    this.value = "";
                    $(this).css({
                        "color": "black"
                    });
                    $(this).unbind('focus');
                }).css({
                    "color": "#C0C0C0"
                });
            });
            function checkallboxes(ele) {
                var checkboxes = $("#the-list").find(".checkbox:visible");
                if (ele.checked) {
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == 'checkbox') {
                            checkboxes[i].checked = true;
                        }
                    }
                } else {
                    for (var i = 0; i < checkboxes.length; i++) {
                        if (checkboxes[i].type == 'checkbox') {
                            checkboxes[i].checked = false;
                        }
                    }
                }
            }
        </script>

        <div class="wrap">
            <h2 style="float:left; margin-top:20px">Envios Jadlog</h2>
            <div style="float:right"><img src="<?= JADLOG_ROOT_URL ?>/assets/img/jadlog_logo.png"/></div>
            <div style="clear:both"></div>

            <table class="wp-list-table widefat fixed posts">
                <thead>
                    <tr>
                        <th scope="col" id="checkbox" class="manage-column column-cb check-column">
                            <label class="screen-reader-text" for="cb-select-all-1"></label>
                            <input onchange="checkallboxes(this)" id="cb-select-all-1" type="checkbox"/>
                        </th>
                        <th scope="col" id="order_id"        class="manage-column column-order_number">
                            <?= __('Número do pedido', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_date"      class="manage-column column-order_date">
                            <?= __('Data do pedido', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_customer" class="manage-column column-order_customer">
                            <?= __('Destinatário', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_address"   class="manage-column column-order_address">
                            <?= __('Endereço', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_service"   class="manage-column column-order_service">
                            <?= __('Modalidade', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_postcode"  class="manage-column column-order_postcode">
                            <?= __('PUDO', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_full_name" class="manage-column column-order_receiver">
                            <?= __('Endereço PUDO', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_status"    class="manage-column column-order_status">
                            <?= __('Status', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_return"    class="manage-column column-order_result">
                            <?= __('Retorno', 'jadlog') ?>
                        </th>
                        <th scope="col" id="order_actions"   class="manage-column column-order_actions">
                            <?= __('Ações', 'jadlog') ?>
                        </th>
                    </tr>
                </thead>
                <tbody id="the-list">

                    <script>
                        var jadlog_embarcador_service_request = function(dialog) {
                            var id = $(dialog).data('id');
                            var manage_response = function(json, retorno) {
                                var status  = json['status'];
                                var tr      = $('#delivery_' + id);
                                $(tr).children('.status').html(status);
                                $(tr).children('.retorno').html(retorno);
                                alert(status + '\n' + retorno);
                            };
                            $.ajax({
                                type:     "POST",
                                dataType: "json",
                                url:      "<?= JADLOG_ROOT_URL ?>/controllers/EmbarcadorController.php",
                                data:     $(dialog).children('form').serialize(),
                                success: function (response) {
                                    console.log(response);
                                    manage_response(
                                        response, 
                                        'Shipment ID: ' + response['shipmentId'] + '\n' + 'Solicitação de coleta: ' + response['codigo']);
                                    $('#delivery_' + id + ' .jadlog_delivery_request').hide();
                                    $('#delivery_' + id + ' .jadlog_delivery_tracking').show();
                                    $('#delivery_' + id + ' .jadlog_delivery_cancel').show();
                                    $(dialog).dialog('close');
                                },
                                error: function (e) {
                                    console.error(e);
                                    manage_response(e['responseJSON'], e['responseJSON']['erro']['descricao']);
                                    $(dialog).dialog('close');
                                }
                            });
                        }
                        var jadlog_embarcador_dialog_setup = function(dialog_id) {
                            $(dialog_id).dialog({
                                autoOpen: false,
                                modal: true,
                                width: 'auto',
                                closeOnEscape: true,
                                buttons: {
                                    Cancelar: function() { $(this).dialog('close') },
                                    Enviar:   function(target) {
                                                  $(target.currentTarget).prop('disabled', true);
                                                  jadlog_embarcador_service_request(this);
                                              }
                                }
                            });
                        };
                    </script>

                    <?php
                    include_once("classes/Delivery.php");
                    include_once("classes/DeliveryRepository.php");
                    include_once("classes/EmbarcadorService.php");
                    include_once("classes/OrderHelper.php");
                    $deliveries = DeliveryRepository::get_all();

                    foreach ($deliveries as $delivery):
                        $delivery_id        = htmlentities($delivery->id);
                        $order_helper       = new OrderHelper($delivery->order_id);
                        $order              = $order_helper->get_order();
                        $order_id           = $order->get_order_number();
                        $order_date_created = $order_helper->get_formatted_date_created();
                        $order_full_name    = $order->get_formatted_shipping_full_name();
                        $order_address      = $order_helper->get_formatted_address('shipping');
                        $status_color = ($delivery->status == DeliveryRepository::INITIAL_STATUS) ?
                            'orange' :
                            (empty($delivery->shipment_id) ? 'red' : 'green');
                        ?>
                        <tr id="delivery_<?= $delivery_id ?>">
                            <td><input class="checkbox" type="checkbox" name="checkbox[]" value="<?= htmlentities($order_id) ?>"></td>
                            <td class="id"><?= htmlentities($order_id) ?></td>
                            <td class="date"><?= htmlentities($order_date_created) ?></td>
                            <td class="shipping"><?= htmlentities($order_full_name) ?></td>
                            <td class="shipping"><?= htmlentities($order_address) ?></td>
                            <td class="pudo"><?= htmlentities($delivery->modalidade) ?></td>
                            <td class="pudo"><?= isset($delivery->pudo_id) ? htmlentities($delivery->pudo_id.' - '.$delivery->pudo_name) : '' ?></td>
                            <td class="pudo"><?= htmlentities($delivery->pudo_address) ?></td>
                            <td class="status" style="color:<?= $status_color ?>"><?= htmlentities($delivery->status) ?></td>
                            <td class="retorno"><?= nl2br(htmlentities(Delivery::retorno($delivery))) ?></td>
                            <td>
                                <div>
                                    <a href="#" class="jadlog_delivery_tracking" data-id="<?= $delivery_id ?>" data-shipment-id="<?= $delivery->shipment_id ?>">
                                        <?#= __('Consultar', 'jadlog') ?>
                                    </a>
                                </div>
                                <div>
                                    <a href="#" class="jadlog_delivery_cancel button" data-id="<?= $delivery_id ?>" data-shipment-id="<?= $delivery->shipment_id ?>">
                                        <?= __('Cancelar', 'jadlog') ?>
                                    </a>
                                </div>
                                <?php if (empty($delivery->shipment_id)): ?>
                                    <div id="dialog-<?= $delivery_id ?>" data-id="<?= $delivery_id ?>" title="<?= __('Preencha os dados do documento fiscal', 'jadlog') ?>" class="hidden wp-dialog">
                                        <form class="form-wrap">
                                            <input type="hidden" name="id" value="<?= $delivery_id ?>">
                                            <p class="form-field">
                                                <label for="tp_documento">Tipo do documento fiscal:</label>
                                                <select name="tp_documento">
                                                    <?php foreach (EmbarcadorService::TIPOS_DOCUMENTOS as $key => $value): ?>
                                                        <?php $selected = $key == $delivery->dfe_tp_documento ? 'selected="selected"' : '' ?>
                                                        <option value="<?= htmlentities($key) ?>" <?= $selected ?>>
                                                            <?= htmlentities($value) ?>
                                                        </option>
                                                    <?php endforeach ?>
                                                </select>
                                            </p>
                                            <p class="form-field">
                                                <label for="nr_doc">Número do documento:</label>
                                                <input type="text" name="nr_doc" value="<?= htmlentities($delivery->dfe_nr_doc) ?>" maxlength="20" style="width:80%">
                                            </p>
                                            <p class="form-field">
                                                <label for="serie">Série do documento:</label>
                                                <input type="text" name="serie" value="<?= htmlentities($delivery->dfe_serie) ?>" maxlength="3" style="width:30%">
                                            </p>
                                            <p class="form-field">
                                                <label for="valor">Valor declarado:</label>
                                                <?php $valor_declarado = empty($delivery->dfe_valor) ? $order->get_total() : $delivery->dfe_valor ?>
                                                R$ <input type="text" name="valor" value="<?= htmlentities($valor_declarado) ?>" style="width:30%; text-align:right">
                                            </p>
                                            <p class="form-field">
                                                <label for="danfe_cte">Número da DANFE ou CTE:</label>
                                                <input type="text" name="danfe_cte" value="<?= htmlentities($delivery->dfe_danfe_cte) ?>" size="44" maxlength="44" style="width:100%">
                                            </p>
                                            <p class="form-field">
                                                <label for="cfop">CFOP da NF-e:</label>
                                                <input type="text" name="cfop" value="<?= htmlentities($delivery->dfe_cfop) ?>" maxlength="4" style="width:30%">
                                            </p>
                                        </form>
                                    </div>
                                    <a href="#" class="jadlog_delivery_request button" data-id="<?= $delivery_id ?>">
                                        <?= __('Enviar', 'jadlog') ?>
                                    </a>
                                    <script>
                                        $(function() {
                                            jadlog_embarcador_dialog_setup("#dialog-<?= $delivery_id ?>");
                                            $('#delivery_<?= $delivery_id ?> .jadlog_delivery_tracking').hide();
                                            $('#delivery_<?= $delivery_id ?> .jadlog_delivery_cancel').hide();
                                        });
                                    </script>
                                <?php elseif ($delivery->status == DeliveryRepository::CANCELED_STATUS): ?>
                                    <script>
                                        $(function() {
                                            $('#delivery_<?= $delivery_id ?> .jadlog_delivery_tracking').hide();
                                            $('#delivery_<?= $delivery_id ?> .jadlog_delivery_cancel').hide();
                                        });
                                    </script>
                                <?php endif ?>
                            </td>
                        </tr>
                    <?php endforeach ?>

                </tbody>
            </table>
        </div>

        <script>
            $('.jadlog_delivery_request').on("click", function () {
                var id = $(this).data('id');
                $("#dialog-" + id).dialog("open");
            });

            $('.jadlog_delivery_cancel').on("click", function () {
                if (!confirm('Deseja cancelar esta solicitação de coleta Jadlog?')) return;
                var id = $(this).data('id');
                var params = $.param({
                    id:          id,
                    shipment_id: $(this).data('shipment-id')
                });
                $.ajax({
                    type:     "DELETE",
                    dataType: "json",
                    url:      "<?= JADLOG_ROOT_URL ?>/controllers/EmbarcadorController.php?" + params,
                    success: function (response) {
                        $('#delivery_' + id + ' .jadlog_delivery_request').hide();
                        $('#delivery_' + id + ' .jadlog_delivery_tracking').hide();
                        $('#delivery_' + id + ' .jadlog_delivery_cancel').hide();
                        var status = response['status'];
                        $('#delivery_' + id + ' .status').html(status);
                        alert(status);
                        console.log(response);
                    },
                    error: function (e) {
                        var json = e['responseJSON'];
                        alert(json['status'] + '\n' + json['erro']['descricao'] + '\n' + json['erro']['detalhe']);
                        console.error(e);
                    }
                });
            });

            $('.jadlog_delivery_tracking').on("click", function () {
                var id = $(this).data('id');
                var params = $.param({
                    id:          id,
                    shipment_id: $(this).data('shipment-id')
                });
                $.ajax({
                    type:     "GET",
                    dataType: "json",
                    url:      "<?= JADLOG_ROOT_URL ?>/controllers/EmbarcadorController.php?" + params,
                    success: function (response) {
                        console.log(response);
                        alert(JSON.stringify(response, null, 2));
                    },
                    error: function (e) {
                        var json = e['responseJSON'];
                        alert(json['consulta'][0]['error']['descricao']);
                    }
                });
            });
        </script>

        <?php
    }
}

$module = new WooCommerceJadlog();

/* Register plugin status hooks */
register_activation_hook(__FILE__, array($module, 'install'));
register_deactivation_hook(__FILE__, array($module, 'deactivate'));

/* Exec */
$module->Jadlog_main();


