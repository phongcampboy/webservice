<?php

require_once("module/barcode/BarcodeGenerator.php");
require_once("module/barcode/BarcodeGenerator.php");
require_once("module/barcode/BarcodeGeneratorJPG.php");

class Barcode
{

    public function __construct()
    {


    }

    public function json()
    {
        header('Content-Type: application/json');
        $code = $_POST['code'];//รหัส Barcode ที่ต้องการสร้าง  

        if($code){

            $generator = new Picqer\Barcode\BarcodeGeneratorJPG();
            $border = 1;//กำหนดความหน้าของเส้น Barcode
            $height = 30;//กำหนดความสูงของ Barcode
            $generateot_encode = base64_encode($generator->getBarcode($code, $generator::TYPE_CODE_128, $border, $height));
            echo json_encode(array('img' => 'data:image/jpeg;base64,' . $generateot_encode, 'code'=> $code));

        }else{
            echo json_encode(array('img' => '', 'code'=> ''));
        }
         


    }

}