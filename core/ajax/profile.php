<?php

include "../load.php";
include "../../connect/Login.php";

$userid = Login::isLoggedIn();

if (isset($_POST['imgName'])) {
  $imgName = $loadFromUser->checkInput($_POST['imgName']);
  $userid = $loadFromUser->checkInput($_POST['userid']);

  $loadFromUser->update('profile', $userid, array('coverPic' => $imgName));
  echo "Cover Photo found";
} else {
  echo "Cover Photo not found";
}
