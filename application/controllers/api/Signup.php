<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/api/REST_Controller.php';

class Signup extends REST_Controller
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
     * Post All Data from this method.
     *
     * @return Response
     */
    public function index_post()
    {
        $response = array();
        $this->form_validation->set_rules('name', 'Name', 'trim|required');
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|valid_email|is_unique[users.email]');
        $this->form_validation->set_rules('mobile', 'Mobile Number', 'trim|required|max_length[10]|min_length[10]');
        $this->form_validation->set_rules('password', 'Password', 'trim|required');

        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $data = array();
            $data['name'] =  $this->input->post('name');
            $data['email'] =  $this->input->post('email');
            $data['mobile'] =  $this->input->post('mobile');
            $data['password'] = $this->QueryModel->passwordHash($this->input->post('password'));

            $user = $this->QueryModel->insertRecord('users', $data);
            if ($user) {
                $userInfo = $this->QueryModel->selectSelectedSingelRecord('users', array('email' => $this->input->post('email')), 'id,name,email,mobile');
                $response['status'] = API_SUCCESS;
                $response['message'] = "Sign-Up successfully.";
                $response['userInfo'] = $userInfo;
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "Something went wrong!";
            }
        }
        $this->response($response);
    }
}
