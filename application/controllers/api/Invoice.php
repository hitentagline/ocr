<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/api/REST_Controller.php';

class Invoice extends REST_Controller
{
    /**
     * @return Response
     */
    public function __construct()
    {
        parent::__construct();
        // $this->load->database();
    }

    /**
     * Add invoice Using this method.
     *
     * @return Response
     */
    public function add_post()
    {
        $response = array();
        $this->form_validation->set_rules('user_id', 'User id', 'trim|required');
        $this->form_validation->set_rules('company', 'Company', 'trim|required');
        $this->form_validation->set_rules('invoice_no', 'Invoice number', 'trim|required|is_unique[invoices.invoice_no]');
        $this->form_validation->set_rules('due_date', 'Due date', 'trim|required');
        $this->form_validation->set_rules('total', 'Total', 'trim|required');


        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $data = array();
            $data['company'] =  $this->input->post('company');
            $data['invoice_no'] =  $this->input->post('invoice_no');
            $data['due_date'] =  date('Y-m-d', strtotime($this->input->post('due_date')));
            $data['total'] =  $this->input->post('total');
            $data['user_id'] =  $this->input->post('user_id');

            $invoice = $this->QueryModel->insertRecord('invoices', $data);
            if ($invoice) {
                $userInfo = $this->QueryModel->selectSelectedSingelRecord('invoices', array('id' => $invoice), 'id,company,invoice_no,due_date,total');
                $response['status'] = API_SUCCESS;
                $response['message'] = "Created successfully.";
                $response['userInfo'] = $userInfo;
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "Something went wrong!";
            }
        }
        $this->response($response);
    }


    /**
     * Update invoice Using this method.
     *
     * @return Response
     */
    public function update_post()
    {
        $response = array();
        $this->form_validation->set_rules('invoice_id', 'Invoice id', 'trim|required');
        $this->form_validation->set_rules('company', 'Company', 'trim|required');
        $this->form_validation->set_rules('invoice_no', 'Invoice number', 'trim|required');
        $this->form_validation->set_rules('due_date', 'Due date', 'trim|required');
        $this->form_validation->set_rules('total', 'Total', 'trim|required');
        $this->form_validation->set_rules('user_id', 'User id', 'trim|required');


        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $data = array();
            $data['company'] =  $this->input->post('company');
            $data['total'] =  $this->input->post('total');
            $data['due_date'] =  $this->input->post('due_date');
            $data['invoice_no'] =  $this->input->post('invoice_no');
            $data['user_id'] =  $this->input->post('user_id');
            $checkInvoice = $this->QueryModel->selectSingleRecord('invoices', array('id' => $this->input->post('invoice_id')));
            if ($checkInvoice) {
                $checkInvoiceNo = $this->QueryModel->selectSingleRecord('invoices', array('id!=' => $this->input->post('invoice_id'), 'invoice_no' => $this->input->post('invoice_no')));
                if ($checkInvoiceNo) {
                    $response['status'] = API_ERROR;
                    $response['message'] = "Invoice number aleready exist in another invoice!";
                } else {
                    $invoice = $this->QueryModel->updateRecord('invoices', $data, array('id' => $this->input->post('invoice_id')));
                    if ($invoice) {
                        $invoiceInfo = $this->QueryModel->selectSelectedSingelRecord('invoices', array('id' => $this->input->post('invoice_id')), 'id,company,invoice_no,due_date,total');
                        $response['status'] = API_SUCCESS;
                        $response['message'] = "Update successfully.";
                        $response['invoiceInfo'] = $invoiceInfo;
                    } else {
                        $response['status'] = API_ERROR;
                        $response['message'] = "Something went wrong!";
                    }
                }
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "Invalid Invoice Id!";
            }
        }
        $this->response($response);
    }

    /**
     * Delete invoice Using this method.
     *
     * @return Response
     */
    public function delete_post()
    {
        $response = array();
        $this->form_validation->set_rules('invoice_id', 'Invoice id', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $checkInvoice = $this->QueryModel->selectSingleRecord('invoices', array('id' => $this->input->post('invoice_id')));
            if ($checkInvoice) {
                $invoice = $this->QueryModel->deleteRecord('invoices', array('id' => $this->input->post('invoice_id')), '', '');
                if ($invoice) {
                    $response['status'] = API_SUCCESS;
                    $response['message'] = "Delete successfully.";
                } else {
                    $response['status'] = API_ERROR;
                    $response['message'] = "Something went wrong!";
                }
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "Invalid Invoice Id!";
            }
        }
        $this->response($response);
    }

    /**
     * List(All/Upcoming) invoice Using this method.
     *
     * @return Response
     */
    public function list_post()
    {
        $response = array();
        $this->form_validation->set_rules('user_id', 'User id', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $filterData = $this->QueryModel->selectSelectedMultipleRecord('invoices', array('user_id' => $this->input->post('user_id')), 'id,user_id,company,invoice_no,due_date,total', 'due_date ASC');

            $start_date = date('Y-m-d');
            $end_date = date('Y-m-d', strtotime("+30 days"));
            $list = array();
            foreach ($filterData as $fd) {
                $list['id'] = $fd['id'];
                $list['user_id'] = $fd['user_id'];
                $list['company'] = $fd['company'];
                $list['invoice_no'] = $fd['invoice_no'];
                $list['due_date'] = $fd['due_date'];
                $list['total'] = $fd['total'];
                $list['upcoming'] = 0;
                if ($fd['due_date'] > $start_date && $fd['due_date'] <= $end_date) {
                    $list['upcoming'] = 1;
                }

                $data[] = $list;
            }

            if ($filterData) {
                $response['status'] = API_SUCCESS;
                $response['message'] = "List found successfully.";
                $response['invoiceData'] = $data;
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "No one bill found!";
            }
        }
        $this->response($response);
    }
}
