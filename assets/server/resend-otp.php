<?php

session_start();
if (time() > $_SESSION['otp_expiry']) {
  $otp = rand(100000, 999999);
  $_SESSION['otp_code'] = $otp;
  $_SESSION['otp_expiry'] = time() + 600;
  mail($_SESSION['pending_email'], "Your New OTP", "Your new OTP is: $otp");
  echo "OTP resent!";
} else {
  echo "Please wait before resending.";
}


?>
