<?php
/**
 * Copyright (C) 2013 IMpulse (BD) Ltd.
 * All Rights Reserved. No use, copying or distribution of this work may be
 * made except in accordance with a valid license agreement from IMpulse. This
 * notice must be included on all copies, modifications and derivatives of this
 * work.
 */

require_once "./util.php";

class APN {
  private $url;
  private $passphrase;
  private $cert;
  private $socket;

  public function __construct($config) {
    $this->url = $config['url'];
    $this->passphrase = $config['passphrase'];
    $this->cert = $config['cert'];

    logMessage(__FILE__, __LINE__, "Debug",
        "Construction APN instance.", true);
    logMessage(__FILE__, __LINE__, "Debug",
        "URL: ".$this->url.", passphrase: ".$this->passphrase.", cert file: ".
        $this->cert, true);

    if(!file_exists($this->cert)) {
      die("Certificate file doesn't exist!");
    }
  }

  private function _init() {
    $ctx = stream_context_create();
    stream_context_set_option($ctx, 'ssl', 'local_cert',
      $this->cert);
    stream_context_set_option($ctx, 'ssl', 'passphrase',
      $this->passphrase);

    $this->socket = stream_socket_client(
      $this->url, $err,
      $errstr, 60, STREAM_CLIENT_CONNECT | STREAM_CLIENT_PERSISTENT, $ctx);

    if (!$this->socket) {
      logMessage(__FILE__, __LINE__, "Error",
        "Socket connection failed. Message: $errstr ($err)");

      throw new Exception("Socket connection failed.");
    }
  }

  public function notifyDevice($token, $counter) {
    $this->_init();

    // Create the payload body
    $body['aps'] = array(
      'badge' => $counter
    );

    $payload = json_encode($body);

    // Build the binary notification
    $msg = chr(0) . pack('n', 32) . pack('H*', $token) .
      pack('n', strlen($payload)) . $payload;

    // Send it to the server
    $result = fwrite($this->socket, $msg, strlen($msg));

    if (!$result) {
      $message = 'Notification delivery failed for '.$token;
      logMessage(__FILE__, __LINE__, "Error", $message);
    }
    else {
      logMessage(__FILE__, __LINE__, "Debug",
        "Notification sent successfully for ".$token.", witu counter ".
        $counter, true);
    }
  }

  public function close() {
    fclose($this->socket);
  }
}