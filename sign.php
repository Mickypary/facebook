<?php

require "core/load.php";
require "connect/DB.php";

// For Sign up
if (isset($_POST['first-name']) && !empty($_POST['first-name'])) {
  $upFirst = $_POST['first-name'];
  $upLast = $_POST['last-name'];
  $upEmailMobile = $_POST['email-mobile'];
  $upPassword = $_POST['up-password'];
  $birthDay = $_POST['birth-day'];
  $birthMonth = $_POST['birth-month'];
  $birthYear = $_POST['birth-year'];
  if (!empty($_POST['gen'])) {
    $upgen = $_POST['gen'];
  }

  $birth = '' . $birthYear . '-' . $birthMonth . '-' . $birthDay . '';

  if (empty($upFirst) or empty($upLast) or empty($upEmailMobile) or empty($upgen)) {
    $error = 'All fields are required';
  } else {
    $first_name = $loadFromUser->checkInput($upFirst);
    $last_name = $loadFromUser->checkInput($upLast);
    $email_mobile = $loadFromUser->checkInput($upEmailMobile);
    $password = $loadFromUser->checkInput($upPassword);
    $screenName = $first_name . '_' . $last_name;
    if (DB::query("SELECT screenName FROM users WHERE screenName = :screenName", array(':screenName' => $screenName))) {
      $screenRand = rand();
      $userLink = $screenName . $screenRand;
    } else {
      $userLink = $screenName;
    }

    if (!preg_match('/^[_a-z0-9-]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[_a-z0-9-]+)*(\.[_a-z]{2,3})$/', $email_mobile)) { // For email preg_match
      // For Mobile No
      if (!preg_match('/^[0-9-]{11}/', $email_mobile)) {
        $error = 'Incorrect Email ID or Mobile No. Please try again';
      } else { // else for mobile preg_match
        $mob = strlen((string)$email_mobile);
        if ($mob > 11 || $mob < 11) {
          $error = 'Mobile No is not valid';
        } elseif (strlen($_POST['up-password']) < 5 || strlen($_POST['up-password']) >= 60) {
          $error = 'The password must be min of 5 and max of 60';
        } else {
          if (DB::query("SELECT mobile FROM users WHERE mobile = :mobile", array(':mobile' => $email_mobile))) {
            $error = 'Mobile number is already in use';
          } else {

            // User data for insert
            $dataUser = [
              'first_name' => $first_name,
              'last_name' => $last_name,
              'mobile' => $email_mobile,
              'password' => password_hash($password, PASSWORD_BCRYPT),
              'screenName' => $screenName,
              'userLink' => $userLink,
              'birthday' => $birth,
              'gender' => $upgen,
            ];

            // Insert into users table
            $user_id = $loadFromUser->create('users', $dataUser);


            // Profile data for insert
            $dataProfile = [
              'userId' => $user_id,
              'birthday' => $birth,
              'firstName' => $first_name,
              'lastName' => $last_name,
              'profilePic' => 'assets/image/defaultProfile.png',
              'coverPic' => 'assets/image/defaultCover.png',
              'gender' => $upgen,
            ];

            // Insert into Profile table
            $loadFromUser->create('profile', $dataProfile);




            $tstrong = true;
            $token = bin2hex(openssl_random_pseudo_bytes(64, $tstrong));

            // token data for insert into token table
            $dataToken = [
              'token' => sha1($token),
              'user_id' => $user_id,
            ];

            // Insert into token table
            $loadFromUser->create('token', $dataToken);

            $arr_cookie_options = array(
              // 'expires' => time() + 60 * 60 * 24 * 30,
              'expires' => time() + 60 * 60 * 24 * 7,
              'path' => '/',
              'domain' => NULL, // leading dot for compatibility or use subdomain
              'secure' => NULL,     // true or false
              'httponly' => true,    // or false
              // 'samesite' => 'None' // None || Lax  || Strict
            );

            setcookie('FBID', $token, $arr_cookie_options);

            header("Location: index.php");
          }
        }
      }
    } else { // else for email preg_match
      if (!filter_var($email_mobile)) {
        $error = 'Invalid Email Format';
      } elseif (strlen($first_name) > 20) {
        $error = 'Name must be between 2-20 characters';
      } elseif (strlen($_POST['up-password']) < 5 && strlen($_POST['up-password']) >= 60) {
        $error = 'The password is either too short or too long';
      } else {
        if (filter_var($email_mobile, FILTER_VALIDATE_EMAIL) && $loadFromUser->checkEmail($email_mobile) === true) {
          $error = 'Email is already in use';
        } else {
          $dataUser = [
            'first_name' => $first_name,
            'last_name' => $last_name,
            'email' => $upEmailMobile,
            'password' => password_hash($password, PASSWORD_BCRYPT),
            'screenName' => $screenName,
            'userLink' => $userLink,
            'birthday' => $birth,
            'gender' => $upgen,
          ];

          // Insert into Users table
          $user_id = $loadFromUser->create('users', $dataUser);

          $dataProfile = [
            'userId' => $user_id,
            'birthday' => $birth,
            'firstName' => $first_name,
            'lastName' => $last_name,
            'profilePic' => 'assets/image/defaultProfile.png',
            'coverPic' => 'assets/image/defaultCover.png',
            'gender' => $upgen,
          ];

          // Insert into Profile table
          $loadFromUser->create('profile', $dataProfile);


          $tstrong = true;
          $token = bin2hex(openssl_random_pseudo_bytes(64, $tstrong));
          $dataToken = [
            'token' => sha1($token),
            'user_id' => $user_id,
          ];

          // Insert into token table
          $loadFromUser->create('token', $dataToken);

          $arr_cookie_options = array(
            // 'expires' => time() + 60 * 60 * 24 * 30,
            'expires' => time() + 60 * 60 * 24 * 7,
            'path' => '/',
            'domain' => NULL, // leading dot for compatibility or use subdomain
            'secure' => NULL,     // true or false
            'httponly' => true,    // or false
            // 'samesite' => 'None' // None || Lax  || Strict
          );

          setcookie('FBID', $token, $arr_cookie_options);

          header("Location: index.php");
        }
      }
    }
  }
}  // End Signup


// For SignIn
if (isset($_POST['in-email-mobile']) && !empty($_POST['in-email-mobile'])) {
  $email_mobile = $_POST['in-email-mobile'];
  $in_pass = $_POST['in-pass'];

  if (!preg_match("/^[_a-z0-9-]+(\.[_a-z0-9]+)*@[a-z0-9-]+(\.[_a-z0-9-]+)*(\.[_a-z]{2,3})$/", $email_mobile)) {
    if (!preg_match('/^[0-9-]{11}/', $email_mobile)) { // for mobile
      $error = "Incorrect Email or Phone Number";
    } else { // for mobile
      if (DB::query("SELECT mobile FROM users WHERE mobile = :mobile", array(':mobile' => $email_mobile))) {
        if (password_verify($in_pass, DB::query("SELECT password FROM users WHERE mobile = :mobile", array(':mobile' => $email_mobile))[0]['password'])) {


          $user_id = DB::query("SELECT user_id FROM users WHERE mobile = :mobile", array(':mobile' => $email_mobile))[0]['user_id'];
          $tstrong = true;
          $token = bin2hex(openssl_random_pseudo_bytes(64, $tstrong));
          $dataToken = [
            'token' => sha1($token),
            'user_id' => $user_id,
          ];

          $loadFromUser->create('token', $dataToken);

          $arr_cookie_options = array(
            // 'expires' => time() + 60 * 60 * 24 * 30,
            'expires' => time() + 60 * 60 * 24 * 7,
            'path' => '/',
            'domain' => NULL, // leading dot for compatibility or use subdomain
            'secure' => NULL,     // true or false
            'httponly' => true,    // or false
            // 'samesite' => 'None' // None || Lax  || Strict
          );

          setcookie('FBID', $token, $arr_cookie_options);

          header("Location: index.php");
        } else {
          $error = "Password is not correct";
        }
      } else {
        $error = "User not found!";
      }
    } // end mobile else
  } else {
    if (DB::query("SELECT email FROM users WHERE email = :email", array(':email' => $email_mobile))) {
      if (password_verify($in_pass, DB::query("SELECT password FROM users WHERE email = :email", array(':email' => $email_mobile))[0]['password'])) {

        $user_id = DB::query("SELECT user_id FROM users WHERE email = :email", array(':email' => $email_mobile))[0]['user_id'];
        $tstrong = true;
        $token = bin2hex(openssl_random_pseudo_bytes(64, $tstrong));
        $dataToken = [
          'token' => sha1($token),
          'user_id' => $user_id,
        ];

        $loadFromUser->create('token', $dataToken);

        $arr_cookie_options = array(
          // 'expires' => time() + 60 * 60 * 24 * 30,
          'expires' => time() + 60 * 60 * 24 * 7,
          'path' => '/',
          'domain' => NULL, // leading dot for compatibility or use subdomain
          'secure' => NULL,     // true or false
          'httponly' => true,    // or false
          // 'samesite' => 'None' // None || Lax  || Strict
        );

        setcookie('FBID', $token, $arr_cookie_options);

        header("Location: index.php");
      } else {
        $error = "Password is not correct";
      }
    } else {
      $error = "User not found!";
    }
  }
}




?>





<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>facebook</title>
  <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
  <div class="header">
    <div class="logo">facebook</div>
    <form action="sign.php" method="post">
      <div class="sign-in-form">
        <div class="mobile-input">
          <div class="input-text">Email or Phone</div>
          <input type="text" class="input-text-field" name="in-email-mobile" id="email-mobile">
        </div>
        <div class="password-input">
          <div style="font-size: 12px; padding-bottom: 5px;">Password</div>
          <input type="password" name="in-pass" id="in-password" class="input-text-field">
          <div class="forgotten-acc">Forgotten Account?</div>
        </div>
        <div class="login-button">
          <input type="submit" name="" value="Log in" class="sign-in login">
        </div>
      </div>

    </form>
  </div>
  <div class="main">
    <div class="left-side">
      <img src="./assets/image/facebook Signin image.png" alt="">
    </div>
    <div class="right-side">
      <div class="error">
        <?php if (!empty($error)): ?>
          <?= $error; ?>
        <?php endif; ?>
      </div>
      <h1 style="color: #212121;">Create an account</h1>
      <div style="color: #212121; font-size:20px">Its free and always will be</div>
      <form action="sign.php" method="post" name="user-sign-up">
        <div class="sign-up-form">
          <div class="sign-up-name">
            <input type="text" name="first-name" id="first-name" class="text-field" placeholder="First Name">
            <input type="text" name="last-name" id="last-name" class="text-field" placeholder="Last Name">
          </div>

          <div class="sign-wrap-mobile">
            <input type="text" name="email-mobile" id="up-email" class="text-input" placeholder="Mobile number or email address">
          </div>

          <div class="sign-up-password">
            <input type="password" name="up-password" id="up-password" class="text-input" placeholder="Password">
          </div>

          <div class="sign-up-birthday">
            <div class="bday">Birthday</div>
            <div class="form-birthday">
              <select name="birth-day" id="days" class="select-body"></select>
              <select name="birth-month" id="months" class="select-body"></select>
              <select name="birth-year" id="years" class="select-body"></select>
            </div>
          </div>

          <div class="gender-wrap">
            <input type="radio" name="gen" id="fem" value="female" class="m0">
            <label for="fem" class="gender">Female</label>
            <input type="radio" name="gen" id="male" value="male" class="m0">
            <label for="male" class="gender">Male</label>
          </div>

          <div class="term">
            By clicking Sign Up, you agree to our terms, Data policy and Cookie policy. You may receive SMS notifications from us and can opt out at any time.
          </div>

          <input type="submit" value="Sign Up" class="sign-up">

        </div>
      </form>
    </div>
  </div>

  <script src="assets/js/jquery.js"></script>
  <script>
    for (i = new Date().getFullYear(); i > 1900; i--) {
      $("#years").append($('<option>').val(i).html(i));
    }

    for (i = 1; i < 13; i++) {
      $("#months").append($('<option>').val(i).html(i));
    }

    updateNumberOfDays();

    function updateNumberOfDays() {
      $("#days").html("");
      let month = $("#months").val();
      let year = $("#years").val();
      let days = daysInMonth(month, year);
      console.log(days);
      for (let i = 1; i < days + 1; i++) {
        $('#days').append($('<option>').val(i).html(i));

      }
    }

    // Trigger the event so it can reflect in the day select option
    $('#months, #years').on('change', function() {
      updateNumberOfDays();
    });

    function daysInMonth(month, year) {
      return new Date(year, month, 0).getDate()
    }
  </script>
</body>

</html>