<?php
class EmbarcadorService {

    const TIPOS_DOCUMENTOS = array(
        0 => 'Declaração',
        1 => 'NF',
        2 => 'NF-e',
        4 => 'CT-e'
    );

    public function __construct($jadlog_id) {
        include_once("DeliveryRepository.php");
        include_once("Modalidade.php");
        include_once("OrderHelper.php");

        global $wpdb;
        $this->table = $wpdb->prefix . 'woocommerce_jadlog';

        $this->jadlog_delivery = DeliveryRepository::get_by_id($jadlog_id);

        $this->url_inclusao     = get_option('wc_settings_tab_jadlog_url_inclusao_pedidos');
        $this->key              = get_option('wc_settings_tab_jadlog_key_embarcador');
        $this->codigo_cliente   = get_option('wc_settings_tab_jadlog_codigo_cliente');
        $this->modalidade       = Modalidade::codigo_modalidade($this->jadlog_delivery->modalidade);
        $this->conta_corrente   = get_option('wc_settings_tab_jadlog_conta_corrente');
        $this->tipo_coleta      = get_option('wc_settings_tab_jadlog_tipo_coleta');
        $this->tipo_frete       = get_option('wc_settings_tab_jadlog_tipo_frete');
        $this->unidade_origem   = get_option('wc_settings_tab_jadlog_unidade_origem');
        $this->contrato         = get_option('wc_settings_tab_jadlog_contrato');
        $this->servico          = get_option('wc_settings_tab_jadlog_servico');
        $sufix = '_'.strtolower(Modalidade::TODOS[$this->modalidade]);
        $this->valor_coleta     = get_option('wc_settings_tab_jadlog_valor_coleta'.$sufix);

        $this->rem_nome         = get_option('wc_settings_tab_jadlog_shipper_name');
        $this->rem_cpf_cnpj     = get_option('wc_settings_tab_jadlog_shipper_cnpj_cpf');
        $this->rem_ie           = get_option('wc_settings_tab_jadlog_shipper_ie');
        $this->rem_endereco     = get_option('wc_settings_tab_jadlog_shipper_endereco');
        $this->rem_numero       = get_option('wc_settings_tab_jadlog_shipper_numero');
        $this->rem_complemento  = get_option('wc_settings_tab_jadlog_shipper_complemento');
        $this->rem_bairro       = get_option('wc_settings_tab_jadlog_shipper_bairro');
        $this->rem_cidade       = get_option('wc_settings_tab_jadlog_shipper_cidade');
        $this->rem_uf           = get_option('wc_settings_tab_jadlog_shipper_uf');
        $this->rem_cep          = get_option('wc_settings_tab_jadlog_shipper_cep');
        $this->rem_fone         = get_option('wc_settings_tab_jadlog_shipper_fone');
        $this->rem_cel          = get_option('wc_settings_tab_jadlog_shipper_cel');
        $this->rem_email        = get_option('wc_settings_tab_jadlog_shipper_email');
        $this->rem_contato      = get_option('wc_settings_tab_jadlog_shipper_contato');
    }

    /**
     * This function is used to send the post request to embarcador
     *
     * @access public
     * @return json
     */
    public function create($dfe) {
        $order = wc_get_order($this->jadlog_delivery->order_id);
        $order_helper = new OrderHelper($order);

        $request_params         = $this->build_create_request_params($order);
        $request_params->rem    = $this->build_rem();
        $request_params->des    = $this->build_des($order);
        $request_params->dfe    = $this->build_dfe($dfe);
        $request_params->volume = $this->build_volume($order);

        error_log('embarcador/coleta body: '.var_export($request_params, true));
        error_log(var_export($order->get_data(), true));
        return;

        $response = wp_remote_post($this->url_inclusao, array(
            'method' => 'POST',
            'timeout' => 500,
            'blocking' => true,
            'headers' => ['Content-Type' => 'application/json; charset=utf-8', 'Authorization' => $this->key],
            'body' => json_encode($request_params),
            'cookies' => array()
        )
        );
        error_log( 'In ' . __FUNCTION__ . '(), $request_params = ' . var_export( $request_params, true ) );
        error_log( 'In ' . __FUNCTION__ . '(), $response = ' . var_export( $response, true ) );

        if ( is_wp_error( $response ) ) {
            $error_message = $response->get_error_message();
            return $error_message;
        } else {
            //TODO: save shipment id
            $this->_saveStatus($response['body']);
            return $response['body'];
        }

    }

    private function build_create_request_params($order) {
        $order_helper = new OrderHelper($order);
        $params = new stdClass();
        // $params->codCliente      = $this->codigo_cliente;
        $params->conteudo        = substr($order_helper->get_items_names(), 0, 80);
        $params->pedido          = array($order->get_order_number());
        $params->totPeso         = floatval($this->jadlog_delivery->peso_taxado);
        $params->totValor        = floatval($this->jadlog_delivery->valor_total);
        $params->obs             = null;
        $params->modalidade      = $this->modalidade;
        $params->contaCorrente   = $this->conta_corrente;
        // $params->centroCusto     = null;
        $params->tpColeta        = $this->tipo_coleta;
        $params->tipoFrete       = intval($this->tipo_frete);
        $params->cdUnidadeOri    = $this->unidade_origem;
        $params->cdUnidadeDes    = null;
        $params->cdPickupOri     = null;
        $params->cdPickupDes     = $this->jadlog_delivery->pudo_id;
        $params->nrContrato      = intval($this->contrato);
        $params->servico         = intval($this->servico);
        $params->shipmentId      = null;
        $params->vlColeta        = empty($this->valor_coleta) ? null : floatval($this->valor_coleta);
        return $params;
    }

    private function build_rem() {
        $rem = new stdClass();
        $rem->nome       = $this->rem_nome;
        $rem->cnpjCpf    = $this->only_digits($this->rem_cpf_cnpj);
        $rem->ie         = $this->rem_ie;
        $rem->endereco   = $this->rem_endereco;
        $rem->numero     = $this->rem_numero;
        $rem->compl      = $this->rem_complemento;
        $rem->bairro     = $this->rem_bairro;
        $rem->cidade     = $this->rem_cidade;
        $rem->uf         = $this->rem_uf;
        $rem->cep        = $this->only_digits($this->rem_cep);
        $rem->fone       = $this->rem_fone;
        $rem->cel        = $this->rem_cel;
        $rem->email      = $this->rem_email;
        $rem->contato    = $this->rem_contato;
        return $rem;
    }

    private function build_des($order) {
        $order_helper = new OrderHelper($order);
        $des = new stdClass();
        $des->nome     = $order->get_formatted_shipping_full_name();
        $des->cnpjCpf  = $this->only_digits($order_helper->get_cpf_or_cnpj());
        $des->ie       = $order_helper->get_billing_ie();
        $des->endereco = $order->get_shipping_address_1();
        $des->numero   = $order_helper->get_shipping_number();
        $des->compl    = $order->get_shipping_address_2();
        $des->bairro   = $order_helper->get_shipping_neighborhood();
        $des->cidade   = $order->get_shipping_city();
        $des->uf       = $order->get_shipping_state();
        $des->cep      = $this->only_digits($order->get_shipping_postcode());
        $des->fone     = $order->get_billing_phone();
        $des->cel      = $order_helper->get_billing_cellphone();
        $des->email    = $order->get_billing_email();
        $des->contato  = $order->get_formatted_billing_full_name();
        return $des;
    }

    private function build_dfe($params) {
        $dfe = new stdClass();
        $dfe->danfeCte    = $params['danfe_cte'];
        $dfe->valor       = $params['valor'];
        $dfe->nrDoc       = $params['nr_doc'];
        $dfe->serie       = $params['serie'];
        $dfe->cfop        = $params['cfop'];
        $dfe->tpDocumento = $params['tp_documento'];
        return $dfe;
    }

    private function build_volume($order) {
        $volume = new stdClass();
        $volume->altura        = null;
        $volume->comprimento   = null;
        $volume->largura       = null;
        $volume->peso          = floatval($this->jadlog_delivery->peso_taxado);
        $volume->identificador = $order->get_order_number();
        // $volume->lacre          = null;
        return $volume;
    }

    private function only_digits($string) {
        return preg_replace('/[^0-9]/', '', $string);
    }

    /**
     * This function is used to send the post request to embarcador
     *
     * @access public
     * @return json
     */
    public function _saveStatus($response) {

        global $wpdb;

        $response = json_decode($response);

        if (isset($response->erro)) {
            $wpdb->update( $this->table, [
                'status' => $response->status,
                'erro' => $response->erro->descricao
            ], [
                'id' => $this->jadlog_id
            ]);
        } else {
            $wpdb->update( $this->table, [
                'status' => $response->status,
                'erro' => ''
            ], [
                'id' => $this->jadlog_id
            ]);
        }

    }

}
