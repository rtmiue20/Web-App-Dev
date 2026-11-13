<?php
require_once __DIR__ . '/../app/bootstrap.php';
require_once __DIR__ . '/../app/controllers/ChatbotController.php';

$chatbot = new ChatbotController();
$chatbot->message();

