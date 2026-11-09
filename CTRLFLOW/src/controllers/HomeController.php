<?php
class HomeController {
    public function index() {
        header("Location: /CTRLFLOW/public/login.php");
        exit;
    }
}
