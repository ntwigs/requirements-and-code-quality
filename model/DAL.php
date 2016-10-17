<?php

namespace model;

require_once('exceptions/WrongCredentialsException.php');

class DAL {
    private static $REGISTER_SUCCESS = false;


  public function __construct() {
    $this->jsonString = file_get_contents('./database/UserCredentials.json');
  }

  public function decodeJson() {
    return json_decode($this->jsonString, true);
  }

  public function compareCredentials($enteredUsername, $enteredPassword) {
    $accounts = $this->decodeJson();
    $isCredentialsCorrect = false;

    for ($i = 0; $i < count($accounts); $i++) {
      if ($accounts[$i]['username'] === $enteredUsername && $accounts[$i]['password'] === $enteredPassword) {
        $isCredentialsCorrect = true;
      }
    }

    if (!$isCredentialsCorrect) {
        throw new \WrongCredentialsException('User credentials are allllll wronk.');
    }

    return $isCredentialsCorrect;

  }

  public function validateUsername(string $username) : bool {
      preg_match('/^[a-zA-Z0-9]+$/', $username, $matches);
      return count($matches) > 0;
  }

  public function stripUsername(string $username) : string {
      $cleanedUsername = strip_tags($username);
      return $cleanedUsername;
  }

  public function compareUsername($enteredUsername) {
    $accounts = $this->decodeJson();
    $usernameExists = false;

    for ($i = 0; $i < count($accounts); $i++) {
      if ($accounts[$i]['username'] === $enteredUsername) {
        $usernameExists = true;
      }
    }
    return $usernameExists;
  }

  public function addUserToDB($enteredUsername, $enteredPassword) {
    $json = $this->decodeJson();
    $newCredArray = array();
    $newCredArray['username'] = $enteredUsername;
    $newCredArray['password'] = $enteredPassword;
    array_push($json, $newCredArray);
    if ($enteredUsername !== NULL || $enteredPassword !== NULL) {
      $this->saveDB($json);
    }
  }

  public function saveDB($newJson) {
    $encodedJson = json_encode($newJson);
    file_put_contents('./database/UserCredentials.json', $encodedJson);
    self::$REGISTER_SUCCESS = true;
  }

  public function wasSuccessfull() {
      return self::$REGISTER_SUCCESS;
  }



}
