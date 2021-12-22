<?php
/*
 * Copyright (C) 2013 IMpulse (BD) Ltd.
 * All Rights Reserved.  No use, copying or distribution of this
 * work may be made except in accordance with a valid license
 * agreement from IMpulse.  This notice must be
 * included on all copies, modifications and derivatives of this
 * work.
 */

function loadConfig() {
  $ini_array = parse_ini_file("config.ini", true);
  if (!$ini_array) {
    dieWithError(500, 'Some error occured while parsing the config file.');
  }

  return $ini_array;
}

function authenticate($credentials) {
  $config = loadConfig();

  if (!isset($credentials['username']) || !isset($credentials['password'])) {
    logMessage(__FILE__, __LINE__, "Error", "Someone tried to log in with".
      " wrong credentials.");
    dieWithError(401, "Wrong username/password.");
  }

  $user = $credentials['username'];
  $pass = $credentials['password'];
  if ($config['credentials']['user'] != $user ||
    $config['credentials']['pass'] != $pass) {

    logMessage(__FILE__, __LINE__, "Error", "Someone tried to log in with".
      " wrong credentials.");
    dieWithError(401, "Wrong username/password.");
  }
}

function getConnection() {
  $config = loadConfig();

  try {
    $db = new PDO("mysql:host=". $config['database']['host'] .";dbname=".
      $config['database']['name'], $config['database']['user'],
      $config['database']['pass']);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    return $db;
  } catch (PDOException $e) {
    logMessage(__FILE__, __LINE__, "Error",
      "Error occured while connecting to database: ".$e->getMessage());
    dieWithError(500, "Some Error occured while trying to connect to the database.");
  }
}

function logMessage($file, $line, $severity, $message, $isDebugInfo = false) {
  $date = date('Y-m-d H:i:s');
  $message = "$date: $file, line $line - $severity: $message".PHP_EOL;

  if ($isDebugInfo) {
    $config = loadConfig();

    if($config['debug']['enabled']) {
      file_put_contents("log.txt", $message, FILE_APPEND | LOCK_EX);
    }

    return;
  }

  file_put_contents("log.txt", $message, FILE_APPEND | LOCK_EX);
}

function dieWithError($code, $message) {
  header(':', true, $code);
  die($message);
}

function getAPNConfig() {
  $config = loadConfig();
  $env = $config['env']['production'] ? 'apn-prod' : 'apn-dev';

  return array(
    'url' => $config["$env"]["url"].'',
    'passphrase' => $config["$env"]["passphrase"].'',
    'cert' => $config["$env"]["cert"].''
  );
}