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
  $stmt = $db->prepare("UPDATE devices SET is_enabled = 0 WHERE token = :token");
  $stmt->bindParam(':token', $deviceToken, PDO::PARAM_STR);
  if ($stmt->execute()) {
    die("Device successfully removed.");
  }
  else {
    dieWithError(500, "Removal failed due to internal server error.");
  }
} catch (PDOException $ex) {
  logMessage(__FILE__, __LINE__, "Error", $ex->getMessage());
  dieWithError(500, "Removal failed due to internal server error.");
}