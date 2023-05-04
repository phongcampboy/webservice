<?php


class Receipt{
    protected $CustomerModel;
    public function __construct()
    {

        $this->CustomerModel = new CustomerModel();

    }


    public function promotions($f3, $param){
      
        $this->checkToken($param);
        $this->CustomerModel->promotions($_POST['branch']);
           
    }

    public function numbers($f3, $param){
        $this->checkToken($param);
        $this->CustomerModel->receiptNumber($_POST['branch']);
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