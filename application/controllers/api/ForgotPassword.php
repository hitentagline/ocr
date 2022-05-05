<?php
defined('BASEPATH') or exit('No direct script access allowed');
require APPPATH . '/libraries/api/REST_Controller.php';

class ForgotPassword extends REST_Controller
{
    /**
     * @return Response
     */
    public function __construct()
    {
        parent::__construct();
        $this->load->config('email');
        $this->load->library('email');
    }

    /**
     * Send OTP Mail For Forgot Password Using this Method. 
     *
     * @return Response
     */
    public function sendmail_post()
    {
        $response = array();
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|valid_email');
        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $checkUser = $this->QueryModel->selectSingelRecord('users', array('email' => $this->input->post('email')));
            if ($checkUser) {
                $otp = rand(000000, 999999);
                $subject = 'OCR - Reset Password';
                $from = $this->config->item('smtp_user');
                $this->email->set_newline("\r\n");
                $this->email->from($from);
                $this->email->to($this->input->post('email'));
                $this->email->subject($subject);
                $this->email->message('Your OCR OTP is - ' . $otp);
                if ($this->email->send()) {
                    $update_otp = $this->QueryModel->updateRecord('users', array('forgot_pass_otp' => $otp), array('email' => $this->input->post('email')));
                    if ($update_otp) {
                        $response['status'] = API_SUCCESS;
                        $response['message'] = "Check your inbox.";
                    } else {
                        $response['status'] = API_ERROR;
                        $response['message'] = "Something went wrong!";
                    }
                } else {
                    $response['status'] = API_ERROR;
                    $response['message'] = "Something went wrong!";
                }
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "Invalid email!";
            }
        }
        $this->response($response);
    }

    /**
     * Check Forgot password OTP Using this Method.
     *
     * @return Response
     */
    public function checkOTP_post()
    {
        $response = array();
        $this->form_validation->set_rules('email', 'Email ID', 'trim|required|valid_email');
        $this->form_validation->set_rules('otp', 'OTP', 'trim|required');
        if ($this->form_validation->run() == FALSE) {
            $response['status'] = FORM_VALIDATION_ERROR;
            $errorString = implode(",", $this->form_validation->error_array());
            $response['message'] = $errorString;
        } else {
            $checkUser = $this->QueryModel->selectSingelRecord('users', array('email' => $this->input->post('email')));
            if ($checkUser) {
                $checkOTP = $this->QueryModel->selectSingelRecord('users', array('forgot_pass_otp' => $this->input->post('otp')));
                if ($checkOTP) {
                    $response['status'] = API_SUCCESS;
                    $response['message'] = "Successfull";
                } else {
                    $response['status'] = API_ERROR;
                    $response['message'] = "Invalid OTP!";
                }
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "Invalid Email!";
            }
        }
        $this->response($response);
    }

    /**
     * Change password Using this Method.
     *
     * @return Response
     */
    public function changepassword_post()
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
            $checkUser = $this->QueryModel->selectSingelRecord('users', array('email' => $this->input->post('email')));
            if ($checkUser) {
                $data['password'] = $this->QueryModel->passwordHash($this->input->post('password'));
                $data['forgot_pass_otp'] = '';
                $update_password = $this->QueryModel->updateRecord('users', $data, array('email' => $this->input->post('email')));
                if ($update_password) {
                    $response['status'] = API_SUCCESS;
                    $response['message'] = "Password changed Successfull";
                } else {
                    $response['status'] = API_ERROR;
                    $response['message'] = "Invalid OTP!";
                }
            } else {
                $response['status'] = API_ERROR;
                $response['message'] = "Invalid Email!";
            }
        }
        $this->response($response);
    }
}
