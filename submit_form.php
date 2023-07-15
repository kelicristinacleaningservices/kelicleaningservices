<?php
session_start();

// CSRF token generation and validation
function generateCSRFToken() {
  if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
  }
}

function validateCSRFToken($token) {
  return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
  // Check if the CSRF token is valid
  $token = $_POST['csrf_token'] ?? '';
  if (!validateCSRFToken($token)) {
    die("CSRF token validation failed.");
  }

  // Sanitize the form inputs to prevent potential security issues
  $name = sanitizeInput($_POST["name"]);
  $phone = sanitizeInput($_POST["phone"]);
  $message = sanitizeInput($_POST["message"]);

  // Validate the form inputs
  $errors = array();
  if (empty($name)) {
    $errors[] = "Name is required.";
  }
  if (empty($phone)) {
    $errors[] = "Phone number is required.";
  } else if (!preg_match("/^[0-9]{10}$/", $phone)) {
    $errors[] = "Invalid phone number format. Please enter a 10-digit number.";
  }
  if (empty($message)) {
    $errors[] = "Message is required.";
  }

  // Check for errors before processing the form
  if (empty($errors)) {
    // Check if this is a duplicate submission
    $hash = md5($name . $phone . $message);
    if ($_SESSION['form_hash'] === $hash) {
      die("Duplicate submission detected.");
    }

    // Customize the email content
    $to = "kelicristinacleaningservices@gmail.com";
    $subject = "New Contact Form Submission";
    $body = "Name: $name\nPhone: $phone\nMessage: $message";

    // Send the email
    if (mail($to, $subject, $body)) {
      // Email sent successfully
      $_SESSION['form_hash'] = $hash; // Store the form hash to prevent duplicate submissions
      echo "Thank you for your message!";
    } else {
      // Something went wrong with sending the email
      echo "Oops! Something went wrong. Please try again later.";
    }
  } else {
    // Handle errors and display them to the user
    echo "Errors occurred:";
    foreach ($errors as $error) {
      echo "<p>$error</p>";
    }
  }
}

function sanitizeInput($input) {
  // Remove any potential harmful elements
  $input = trim($input);
  $input = stripslashes($input);
  $input = htmlspecialchars($input);
  return $input;
}

// Generate CSRF token
generateCSRFToken();
?>
