<?php
  session_start();  //Check the user is logged in;

  $conn = new mqsqli("localhost", "root", "", "projectx_db");
  if($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
  }

  //Fetch videos from newest to oldest (LIFO)
  