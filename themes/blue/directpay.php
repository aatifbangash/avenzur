<?php


$redirectURL= $formdata["RedirectURL"];
$merchantID= $formdata['MerchantID'];
$amount= $formdata['Amount'];
$currencyCode= $formdata['CurrencyISOCode'];
$messageID= $formdata['MessageID'];
$transactionID = $formdata['TransactionID'];
$themeID= $formdata['ThemeID'];
$ItemID= $formdata['ItemID'];
$responseBackURL= $formdata['ResponseBackURL'];
$quantity= $formdata['Quantity'];
$channel= $formdata['Channel'];
$secureHash= $formdata['SecureHash'];
$version= $formdata['Version'];
$paymentMethod =$formdata["PaymentMethod"];
$paymentDescription= $formdata['PaymentDescription'];
$genertaeToken =$formdata["GenerateToken"];


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

    <input name="MerchantID" type="hidden" value="<?php echo $merchantID?>" />

    <input name="Amount" type="hidden" value="<?php echo $amount?>" />

    <input name="CurrencyISOCode" type="hidden" value="<?php echo $currencyCode?>" />

    <input name="MessageID" type="hidden" value="<?php echo $messageID?>" />

    <input name="TransactionID" type="hidden" value="<?php echo $transactionID?>" />

    <input name="ThemeID" type="hidden" value="<?php echo $themeID?>" />

    <input name="ItemID" type="hidden" value="<?php echo $ItemID?>" />

    <input name="ResponseBackURL" type="hidden" value="<?php echo $responseBackURL?>" />

    <input name="Quantity" type="hidden" value="<?php echo $quantity?>" />

    <input name="Channel" type="hidden" value="<?php echo $channel?>" />

    <input name="Version" type="hidden" value="<?php echo $version?>" />

    <input name="PaymentMethod" type="hidden" value="<?php echo $paymentMethod?>" />

    <input name="PaymentDescription" type="hidden" value="<?php echo $paymentDescription?>" />

    <input name="GenerateToken" type="hidden" value="<?php echo $genertaeToken?>" />

    <input name="SecureHash" type="hidden" value="<?php echo $secureHash?>" />

  </form>

</body>

</html>

