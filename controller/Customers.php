<?php


class Customers
{
    protected $CustomerModel;
    public function __construct()
    {

        $this->CustomerModel = new CustomerModel();

    }

    public function search($f3, $param){
      
        $this->checkToken($param);
        $this->CustomerModel->search($_POST);
           
    }

    private function checkToken($param)
    {
        global $token;
        if ($param['token'] != $token) {
            echo "invalid token";
            exit();
        }
    }

 
}