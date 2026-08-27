<?php

if(isset($formdata["RedirectURL"])){
  $redirectURL= $formdata["RedirectURL"];
}
$merchantID= $formdata['MerchantID'];
$amount= $formdata['Amount'];
$currencyCode= $formdata['CurrencyISOCode'];
$messageID= $formdata['MessageID'];
$transactionID = $formdata['TransactionID'];
if(isset($formdata["ThemeID"])){
  $themeID= $formdata['ThemeID'];
}
if(isset($formdata["ItemID"])){
  $ItemID= $formdata['ItemID'];
}
$responseBackURL= $formdata['ResponseBackURL'];
$quantity= $formdata['Quantity'];
$channel= $formdata['Channel'];
$secureHash= $formdata['SecureHash'];
$version= $formdata['Version'];
$paymentMethod =$formdata["PaymentMethod"];
$paymentDescription= $formdata['PaymentDescription'];
if(isset($formdata["GenerateToken"])){
  $genertaeToken =$formdata["GenerateToken"];
}
if(isset($formdata["card_number"])){
  $cardNumber = $formdata["card_number"];
  $cardNumber = preg_replace('/\s+/', '', $cardNumber);
}
if(isset($formdata["card_name"])){
  $cardName   = $formdata["card_name"];
}
if(isset($formdata["card_expiry_month"])){
  $expiryDateMonth = $formdata["card_expiry_month"];
}
if(isset($formdata["card_expiry_year"])){
  $expiryDateYear = $formdata["card_expiry_year"];
}
if(isset($formdata["card_cvv"])){
  $cardCVV = $formdata["card_cvv"];
}

if(isset($formdata["Language"])){
  $Language = $formdata["Language"];
}
if(isset($formdata["tabby_email"])){
  $tabby_email = $formdata["tabby_email"];
}
if(isset($formdata["tabby_phone"])){
  $tabby_phone = $formdata["tabby_phone"];
}

//$expiryDateYear = substr($expiryDateYear, -2);

?>

<html>

<head>
  <!-- Loading animation -->
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <style>
  body {
    width: 100vw;
    height: 100vh;
    margin: 0;
    background-color: white;
    display: flex;
    justify-content: center;
    align-items: center;
    align-content: center;
  }

  div {
    width: 8vmax;
    height: 8vmax;
    border-right: 4px solid #767EED;
    border-radius: 100%;
    animation: spinRight 800ms linear infinite;

    &:before,
    &:after {
      content: '';
      width: 6vmax;
      height: 6vmax;
      display: block;
      position: absolute;
      top: calc(50% - 3vmax);
      left: calc(50% - 3vmax);
      border-left: 3px solid #767EED;
      border-radius: 100%;
      animation: spinLeft 800ms linear infinite;
    }

    &:after {
      width: 4vmax;
      height: 4vmax;
      top: calc(50% - 2vmax);
      left: calc(50% - 2vmax);
      border: 0;
      border-right: 2px solid #767EED;
      animation: none;
    }
  }

 @keyframes spinLeft {
    from {
      transform: rotate(0deg);
    }

    to {
      transform: rotate(720deg);
    }
  }

  @keyframes spinRight {
    from {
      transform: rotate(360deg);
    }

    to {
      transform: rotate(0deg);
    }
  }
  </style>
</head>

<body onload="javascript:document.redirectForm.submit();" >
  <div></div>
  <form action="<?php echo $redirectURL?>" method="post" name="redirectForm">
    <?php if(isset($cardNumber)){ ?>
      <input type="hidden" name="CardNumber" value="<?php echo $cardNumber?>">
    <?php } ?>
    <input name="MerchantID" type="hidden" value="<?php echo $merchantID?>" />

    <?php if(isset($expiryDateMonth)){ ?>
      <input type="hidden" name="ExpiryDateMonth" value="<?php echo $expiryDateMonth?>">
    <?php } ?>

    <?php if(isset($cardName)){ ?>
      <input type="hidden" name="CardHolderName" value="<?php echo $cardName?>">
    <?php } ?>

    <input name="Amount" type="hidden" value="<?php echo $amount?>" />

    <input name="CurrencyISOCode" type="hidden" value="<?php echo $currencyCode?>" />

    <?php if(isset($Language)){ ?>
      <input name="Language" type="hidden" value="<?php echo $Language?>">
    <?php } ?>

    <input name="MessageID" type="hidden" value="<?php echo $messageID?>" />

    <?php if(isset($expiryDateYear)){ ?>
      <input name="ExpiryDateYear" type="hidden" value="<?php echo $expiryDateYear?>">
    <?php } ?>

    <input name="TransactionID" type="hidden" value="<?php echo $transactionID?>" />

    <?php if(isset($themeID)){ ?>
      <input name="ThemeID" type="hidden" value="<?php echo $themeID?>" />
    <?php } ?>

    <?php if(isset($ItemID)){ ?>
      <input name="ItemID" type="hidden" value="<?php echo $ItemID?>" />
    <?php } ?>

    <input name="ResponseBackURL" type="hidden" value="<?php echo $responseBackURL?>" />

    <?php if(isset($quantity)){ ?>
    <input name="Quantity" type="hidden" value="<?php echo $quantity?>" />
    <?php } ?>

    <input name="Channel" type="hidden" value="<?php echo $channel?>" />

    <input name="Version" type="hidden" value="<?php echo $version?>" />

    <input name="PaymentMethod" type="hidden" value="<?php echo $paymentMethod?>" />

    <input name="PaymentDescription" type="hidden" value="<?php echo $paymentDescription?>" />

    <?php if(isset($genertaeToken)){ ?>
      <input name="GenerateToken" type="hidden" value="<?php echo $genertaeToken?>" />
    <?php } ?>

    <?php if(isset($cardCVV)){ ?>
      <input name="SecurityCode" type="hidden" value="<?php echo $cardCVV?>">
    <?php } ?>

    <?php if(isset($tabby_email)){ ?>
      <input name="email" type="hidden" value="<?php echo $tabby_email?>">
    <?php } ?>

    <?php if(isset($tabby_phone)){ ?>
      <input name="phoneNumber" type="hidden" value="<?php echo $tabby_phone?>">
    <?php } ?>

    <input name="SecureHash" type="hidden" value="<?php echo $secureHash?>" />

  </form>

</body>

</html>



<!-- <input type="hidden" name="CardNumber" value="5105105105105100">
<input type="hidden" name="SecureHash" value="<?php echo $secureHash;?>">
<input type="hidden" name="ResponseBackURL" value="http://localhost/directpay/payment_confirmation.php">
<input type="hidden" name="GenerateToken" value="yes">
<input type="hidden" name="MessageID" value="1">
<input type="hidden" name="ExpiryDateYear" value="31">
<input type="hidden" name="MerchantID" value="DP00000017">
<input type="hidden" name="ExpiryDateMonth" value="01">
<input type="hidden" name="CurrencyISOCode" value="682">
<input type="hidden" name="PaymentDescription" value="Direct Pay Demo Payment">
<input type="hidden" name="Version" value="1.0">
<input type="hidden" name="Quantity" value="1">
<input type="hidden" name="CardHolderName" value="Aleem Khan">
<input type="hidden" name="Channel" value="0">
<input type="hidden" name="PaymentMethod" value="1">
<input type="hidden" name="Amount" value="200000">
<input type="hidden" name="TransactionID" value="<?php echo $trasnid;?>">
<input type="hidden" name="SecurityCode" value="999"> -->