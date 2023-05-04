<?php

class Users 
{

    protected $UserModel;
    public function __construct()
    {
        $this->UserModel = new UserModel();
    }

    public function login(){

        $this->UserModel->login($_POST);

    }

    public function status(){

        $this->UserModel->statusUpdate($_POST);

    }

    public function keeper(){

        $this->UserModel->getKepper($_POST);

    }

    public function logs($f3, $param){

        $date = null;

        if($_GET['date']){
             $date = $_GET['date'];
        }

        $this->UserModel->get_logs($date);
    }

    public function test(){
        echo "Test";
    }
}
