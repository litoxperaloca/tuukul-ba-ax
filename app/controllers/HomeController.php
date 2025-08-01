<?php
class HomeController {
    public function index() {
        $page_title = 'Bienvenido a Planeación Educativa IA';
        require_once '../app/views/_partials/header.php';
        require_once '../app/views/home/index.php';
        require_once '../app/views/_partials/footer.php';
    }
}
