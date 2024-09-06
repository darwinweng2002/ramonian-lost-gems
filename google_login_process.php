<?php
require 'vendor/autoload.php'; // Load the Google API PHP Client Library

use Google\Auth\OAuth2;
use Google\Client;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
  $id_token = $_POST['id_token'];

  $client = new Client();
  $client->setAuthConfig('client_secret_462546722729-vflluo934lv9qei2jbeaqcib5sllh9t6.apps.googleusercontent.com.json'); // Use your client secret JSON file
  $client->addScope('profile');
  $client->addScope('email');

  $client->setAccessType('offline');
  $client->setPrompt('select_account consent');

  $oauth2 = new OAuth2($client);
  $payload = $oauth2->verifyIdToken($id_token);

  if ($payload) {
    // Process user data from $payload
    $email = $payload['email'];
    $name = $payload['name'];

    // Check if the user exists in your database or create a new record

    // Example response
    $response = ['success' => true];
  } else {
    $response = ['success' => false, 'message' => 'Invalid ID token'];
  }

  // Return a JSON response
  echo json_encode($response);
}
?>
