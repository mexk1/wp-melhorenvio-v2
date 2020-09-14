<?php

namespace Services;

use Models\ShippingService;

class ShippingMelhorEnvioService
{
    const URL_MELHOR_ENVIO_SHIPMENT_SERVICES = 'https://api.melhorenvio.com/v2/me/shipment/services';

    /**
     * function to search for the shipping services available in the Melhor Envio api
     *
     * @return array
     */
    public function getServicesApiMelhorEnvio()
    {
        $shippingService = new ShippingService();

        $services = $shippingService->get();

        if (!empty($services)) {
            return $services;
        }

        $response = wp_remote_request(
            self::URL_MELHOR_ENVIO_SHIPMENT_SERVICES
        );

        if (wp_remote_retrieve_response_code($response) != 200) {
            return [];
        }

        $services =  json_decode(
            wp_remote_retrieve_body(
                $response
            )
        );

        $shippingService->save($services);

        return $services;
    }

    /**
     * function to get service codes enableds on WooCommerce.
     *
     * @return array
     */
    public function getCodesEnableds()
    {
        return $this->getCodesWcShippingClass();
    }

    /**
     * Function to search the codes of services available in woocommerce in string format separated by commas
     *
     * @return string
     */
    public function getStringCodesEnables()
    {
        return implode(",", $this->getCodesEnableds());
    }

    /**
     * Function to search the codes of services available in woocommerce
     *
     * @return array
     */
    public function getCodesWcShippingClass()
    {
        $shippings = WC()->shipping->get_shipping_methods();

        if (is_null($shippings)) {
            return [];
        }

        $codes = [];

        foreach ($shippings as $method) {
            if (!isset($method->code) || is_null($method->code)) {
                continue;
            }
            $codes[] = $method->code;
        }

        return $codes;
    }

    /**
     * Function to return the "Melhor Envio" shipping methods used 
     * in the delivery zones
     *
     * @return array
     */
    public function getMethodsActivedsMelhorEnvio()
    {
        $methods = array();
        $delivery_zones = \WC_Shipping_Zones::get_zones();
        foreach ($delivery_zones as  $zone) {
            foreach ($zone['shipping_methods'] as $method) {
                if (!$this->isMelhorEnvioMethod($method)) {
                    continue;
                }
                $methods[] = $method;
            }
        }
        return $methods;
    }

    /**
     * Function to check if the method is Melhor Envio.
     *
     * @param object $method
     * @return boolean
     */
    public function isMelhorEnvioMethod($method)
    {
        return (is_numeric(strpos($method->id, 'melhorenvio_')));
    }
}
