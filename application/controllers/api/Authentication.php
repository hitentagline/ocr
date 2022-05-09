<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/api/REST_Controller.php';

class Authentication extends REST_Controller
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
     * SignUp and Login Using this method.
     *
     * @return Response
     */
    public function index_post()
    {
        $response = array();
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|valid_email');
        $this->form_validation->set_rules('password', 'Password', 'trim|required|numeric|max_length[6]|min_length[6]');
        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $data = array();

            $checkUser = $this->QueryModel->selectSingleRecord('users', array('email' => $this->input->post('email')));
            if ($checkUser) {
                if (password_verify($this->input->post('password'), $checkUser['password'])) {
                    $blank_otp = $this->QueryModel->updateRecord('users', array('forgot_pass_otp' => ''), array('email' => $this->input->post('email')));
                    $userInfo = $this->QueryModel->selectSelectedSingelRecord('users', array('email' => $this->input->post('email')), 'id,name,email');
                    $response['status'] = API_SUCCESS;
                    $response['message'] = "Login successfully.";
                    $response['userInfo'] = $userInfo;
                } else {
                    $response['status'] = API_ERROR;
                    $response['message'] = "Invalid password!";
                }
            } else {
                $data['email'] =  $this->input->post('email');
                $data['password'] = $this->QueryModel->passwordHash($this->input->post('password'));
                $user = $this->QueryModel->insertRecord('users', $data);
                if ($user) {
                    $userInfo = $this->QueryModel->selectSelectedSingelRecord('users', array('email' => $this->input->post('email')), 'id,name,email');
                    $response['status'] = API_SUCCESS;
                    $response['message'] = "Sign-Up successfully.";
                    $response['userInfo'] = $userInfo;
                } else {
                    $response['status'] = API_ERROR;
                    $response['message'] = "Something went wrong!";
                }
            }
        }
        $this->response($response);
    }
}
