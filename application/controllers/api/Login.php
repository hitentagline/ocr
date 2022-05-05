<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/api/REST_Controller.php';

class Login extends REST_Controller
{
    /**
     * @return Response
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Post All Data from this method.
     *
     * @return Response
     */
    public function index_post()
    {
        $response = array();
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $data = array();
            $checkUser = $this->QueryModel->selectSingleRecord('users', array('email' => $this->input->post('email')));
            if ($checkUser) {
                if (password_verify($this->input->post('password'), $checkUser['password'])) {
                    $userInfo = $this->QueryModel->selectSelectedSingelRecord('users', array('email' => $this->input->post('email')), 'id,name,email,mobile');
                    $response['status'] = API_SUCCESS;
                    $response['message'] = "Login successfully.";
                    $response['userInfo'] = $userInfo;
                } else {
                    $response['message'] = "Invalid credentials!";
                }
            } else {
                $response['message'] = "Invalid credentials!";
            }
        }
        $this->response($response);
    }
}
