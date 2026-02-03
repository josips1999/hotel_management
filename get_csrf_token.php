<?php
session_start();
require_once('lib/CSRFToken.php');
echo CSRFToken::generate();
