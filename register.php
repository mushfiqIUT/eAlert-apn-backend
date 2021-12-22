<?php
/**
 * Copyright (C) 2013 IMpulse (BD) Ltd.
 * All Rights Reserved. No use, copying or distribution of this work may be
 * made except in accordance with a valid license agreement from IMpulse. This
 * notice must be included on all copies, modifications and derivatives of this
 * work.
 */

require_once "util.php";

authenticate($_POST);

$deviceToken = $_POST['token'];
if (empty($deviceToken)) {
  dieWithError(400, "You must post device token to this URL.");
}

$db = getConnection();

try {
  $stmt = $db->prepare("SELECT id FROM devices WHERE token = :token");
  $stmt->bindParam(':token', $deviceToken, PDO::PARAM_STR);
  $stmt->execute();

  $query = "INSERT INTO devices (token) VALUES (:token)";
  if ($stmt->rowCount() > 0) {
    $query = "UPDATE devices SET counter = 0, is_enabled = 1 WHERE token = :token";
    logMessage(__FILE__, __LINE__, "Debug",
      "Got a re-register request from ".$deviceToken.", resetting counter",
      true);
  }
  else {
    logMessage(__FILE__, __LINE__, "Debug",
      "Got a new register request from ".$deviceToken, true);
  }

  $stmt = $db->prepare($query);
  $stmt->bindParam(':token', $deviceToken, PDO::PARAM_STR);

  if ($stmt->execute()) {
    die("Registration successful.");
  }
  else {
    dieWithError(500, "Registration failed due to internal server error.");
  }
} catch (PDOException $ex) {
  logMessage(__FILE__, __LINE__, "Error", $ex->getMessage());
  dieWithError(500, "Some error occured while trying to register a device.");
}