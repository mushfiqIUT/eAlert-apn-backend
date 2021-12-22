<?php
/*
 * Copyright (C) 2013 IMpulse (BD) Ltd.
 * All Rights Reserved.  No use, copying or distribution of this
 * work may be made except in accordance with a valid license
 * agreement from IMpulse.  This notice must be
 * included on all copies, modifications and derivatives of this
 * work.
 */

require_once "util.php";
require_once "classes/APN.php";

authenticate($_POST);

$data_received = $_POST['notify'];
if (empty($data_received)) {
  dieWithError(400, "Invalid request as parameters are not set properly.");
}

logMessage(__FILE__, __LINE__, "Debug", "Received new email!", true);

$apn = new APN(getAPNConfig());

try {
  $db = getConnection();
  $stmt = $db->prepare("SELECT id, token, counter FROM devices WHERE is_enabled = 1");
  $stmt->execute();
  $result = $stmt->fetchAll();

  foreach ($result as $row) {
    $id = intval($row['id']);
    $token = $row['token'];
    $counter = intval($row['counter']);

    $counter++;

    logMessage(__FILE__, __LINE__, "Debug",
      "About to send notification for device id ".$id.", with counter ".
      $counter, true);
    
    // Send notification
    try {
      $apn->notifyDevice($token, $counter);
    } catch (Exception $ex) {
      logMessage(__FILE__, __LINE__, "Error",
          "Notification delivery failed for ".$token.". Exception occured: ".
          $ex->getMessage());
    }

    $stmt = $db->prepare("UPDATE devices SET counter = $counter WHERE id = $id");
    $stmt->execute();
  }
} catch (PDOException $ex) {
  $message = "Some error occured while trying to send notification: ".
    $ex->getMessage();

  logMessage(__FILE__, __LINE__, "Error", $message);
  dieWithError(500, "Some error occured while trying to send notification.");
}

$apn->close();