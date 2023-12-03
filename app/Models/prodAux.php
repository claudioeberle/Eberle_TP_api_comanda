<?php

class ProdAux{

    public $idProducto;
    public $cantidad;

    public function __construct($idProducto, $cantidad){

        $this->idProducto = $idProducto;
        $this->cantidad = $cantidad;
    }
}