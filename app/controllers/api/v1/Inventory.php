<?php

defined('BASEPATH') or exit('No direct script access allowed');

require APPPATH . '/libraries/REST_Controller.php';
use Restserver\Libraries\REST_Controller;

class Inventory extends REST_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->methods['index_get']['limit'] = 500;
        $this->load->api_model('inventory_api');
    }

    protected function setInventory($inv)
    {
        $inv = (array) $inv;
        ksort($inv);
        return $inv;
    }

    public function index_get()
    {
        $warehouse_id = $this->get('warehouse_id');

        $filters = [
            'warehouse_id'     => $warehouse_id,
            'include'  => $this->get('include') ? explode(',', $this->get('include')) : null,
            'group'    => $this->get('group') ? $this->get('group') : 'customer',
            'start'    => $this->get('start') && is_numeric($this->get('start')) ? $this->get('start') : 1,
            'limit'    => $this->get('limit') && is_numeric($this->get('limit')) ? $this->get('limit') : 10,
            'order_by' => $this->get('order_by') ? explode(',', $this->get('order_by')) : ['code', 'acs'],
        ];

        if ($warehouse_id) {
            if ($inventory = $this->inventory_api->getInventory($filters)) {
                $pr_data = [];
                foreach ($inventory as $inv) {
                    $pr_data[] = $this->setInventory($inv);
                }

                $data = [
                    'data'  => $pr_data,
                    'limit' => $filters['limit'],
                    'start' => $filters['start'],
                    'total' => $this->inventory_api->countInventory($filters),
                ];
                $this->response($data, REST_Controller::HTTP_OK);
            } else {
                $this->response([
                    'message' => 'No warehouse were found.',
                    'status'  => false,
                ], REST_Controller::HTTP_NOT_FOUND);
            }
        } else {
            $this->set_response([
                'message' => 'Warehouse Id not passed',
                'status'  => false,
            ], REST_Controller::HTTP_NOT_FOUND);
        }
    }
}
