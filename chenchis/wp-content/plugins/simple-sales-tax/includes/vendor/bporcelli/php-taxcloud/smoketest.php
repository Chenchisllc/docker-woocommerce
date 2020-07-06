<?php

/**
 * This is not an exhaustive test.
 *
 * The purpose of the smoketest is to quickly identify if something has broken
 * integration with the TaxCloud API. The smoketest goes through the process
 * required by TaxCloud and actually makes connections to TaxCloud.
 */

use TaxCloud\Request\AddTransactions;
use TaxCloud\Transaction;

// API credentials loaded from environment variables.
$apiLoginID = getenv("TaxCloud_apiLoginID");
$apiKey = getenv("TaxCloud_apiKey");
// $uspsUserID = getenv("TaxCloud_uspsUserID");

// Some variable that need to be unique, but can't change.
$orderID = rand();
$cartID = rand(1, 999);

/**
 * Show us what step we are on.
 */
function step($message) {
  global $STEPCOUNTER;
  printf("\nStep %d. %s\n", $STEPCOUNTER++, sprintf($message));
}

/**
 * Helper to generate a unique order ID.
 *
 * @return string
 */
function generateOrderID()
{
  try {
    return bin2hex(random_bytes(16));
  } catch (Exception $e) {
    echo 'Failed to generate order ID: ' . $e->getMessage() . PHP_EOL;

    return '';
  }
}

require_once 'vendor/autoload.php';
require_once 'lib/php-taxcloud.php';

$client = new \TaxCloud\Client();

step('Ping');
$pingParams = new \TaxCloud\Request\Ping($apiLoginID, $apiKey);

try {
  $client->Ping($pingParams);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Verify Address');
$address = new \TaxCloud\Address(
  '162 East Avenue',
  'Third Floor',
  'Norwalk',
  'WA', // Intentionally incorrect
  '06851',
  '0000' // Intentionally unspecified
);

$verifyAddress = new \TaxCloud\Request\VerifyAddress($apiLoginID, $apiKey, $address);

try {
  $address = $client->VerifyAddress($verifyAddress);
}
catch (\TaxCloud\Exceptions\USPSIDException $e) {
 echo 'Caught exception: ', $e->getMessage(), "\n";
 return;
}
catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Cart Item');
$cartItems = array();
$cartItem = new \TaxCloud\CartItem($cartID + 1, 'ABC123', '00000', 12.00, 1);
$cartItems[] = $cartItem;
print_r($cartItem);

step('Cart Item - Shipping');

$cartItemShipping = new \TaxCloud\CartItem($cartID + 2, 'SHIPPING123', 11010, 8.95, 1);
$cartItems[] = $cartItemShipping;
print_r($cartItemShipping);

step('Cart Items Array');

print_r($cartItems);

step('Lookup');

$originAddress = new \TaxCloud\Address(
  $address->getAddress1(),
  $address->getAddress2(),
  $address->getCity(),
  $address->getState(),
  $address->getZip5(),
  $address->getZip4()
);

$destAddress = new \TaxCloud\Address(
  'PO Box 573',
  '',
  'Clinton',
  'OK',
  '73601',
  ''
);

$lookup = new \TaxCloud\Request\Lookup($apiLoginID, $apiKey, '123', $cartID, $cartItems, $originAddress, $destAddress);

try {
  $client->Lookup($lookup);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Lookup For Date');

$lookup = new \TaxCloud\Request\LookupForDate($apiLoginID, $apiKey, '123', $cartID, $cartItems, $originAddress, $destAddress, "06-01-2016");

try {
  $client->LookupForDate($lookup);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Authorized');

$authorization = new \TaxCloud\Request\Authorized($apiLoginID, $apiKey, '123', $cartID, $orderID, date("c"));
try {
  $client->Authorized($authorization);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Authorized With Capture');

$lookup = new \TaxCloud\Request\Lookup($apiLoginID, $apiKey, '123', $cartID + 1, $cartItems, $originAddress, $destAddress);
$client->Lookup($lookup);
$authcap = new \TaxCloud\Request\AuthorizedWithCapture($apiLoginID, $apiKey, '123', $cartID + 1, $orderID + 1, date("c"), date("c"));
try {
  $client->AuthorizedWithCapture($authcap);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Captured');

$capture = new \TaxCloud\Request\Captured($apiLoginID, $apiKey, $orderID);
try {
  $client->Captured($capture);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Returned');

$return = new \TaxCloud\Request\Returned($apiLoginID, $apiKey, $orderID + 1, $cartItems, date("c"));
try {
  $client->Returned($return);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Add Exemption Certificate');

$exemptState = new \TaxCloud\ExemptState("WA", \TaxCloud\ExemptionReason::Resale, "00000-00000");
$taxID = new \TaxCloud\TaxID(\TaxCloud\TaxIDType::FEIN, "00000-00000", "WA");
$exemptCert = new \TaxCloud\ExemptionCertificate(array($exemptState), false, "23463", "E-Commerce", "Geek", "Rockstar", "162 East Avenue", "Third Floor", "Norwalk", "WA", "06851", $taxID, \TaxCloud\BusinessType::RetailTrade, "", \TaxCloud\ExemptionReason::Resale, "foo");
$addExempt = new \TaxCloud\Request\AddExemptCertificate($apiLoginID, $apiKey, '123', $exemptCert);
$blanketCertID = '';
try {
  $blanketCertID = $client->AddExemptCertificate($addExempt);
  printf("ID: %s", $blanketCertID);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Get Exemption Certificates');

$getCerts = new \TaxCloud\Request\GetExemptCertificates($apiLoginID, $apiKey, '123');
try {
  $certs = $client->GetExemptCertificates($getCerts);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Get TICs');

$params = new \TaxCloud\Request\GetTICs($apiLoginID, $apiKey);
try {
  $client->GetTICs($params);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Lookup with Single Use Exemption Certificate');

$exemptState = new \TaxCloud\ExemptState("WA", \TaxCloud\ExemptionReason::Resale, "00000-00000");
$taxID = new \TaxCloud\TaxID(\TaxCloud\TaxIDType::FEIN, "00000-00000", "WA");
$singleUseCert = new \TaxCloud\ExemptionCertificate(array($exemptState), true, "23463", "E-Commerce", "Geek", "Rockstar", "162 East Avenue", "Third Floor", "Norwalk", "WA", "06851", $taxID, \TaxCloud\BusinessType::RetailTrade, "", \TaxCloud\ExemptionReason::Resale, "foo");

$lookup = new \TaxCloud\Request\Lookup($apiLoginID, $apiKey, '123', $cartID, $cartItems, $originAddress, $destAddress, false, $singleUseCert);
try {
  $client->Lookup($lookup);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Lookup with Blanket Certificate');

$blanketCert = new \TaxCloud\ExemptionCertificateBase($blanketCertID);
$lookup = new \TaxCloud\Request\Lookup($apiLoginID, $apiKey, '123', $cartID, $cartItems, $originAddress, $destAddress, false, $blanketCert);
try {
  $client->Lookup($lookup);
} catch (Exception $e) {
  echo 'Caught exception: ', $e->getMessage(), "\n";
}

step('Add Transactions');

$today = date('Y-m-d');
$todayTime = time();
$todayDateTime = new DateTime();

try {
  $taxedOrderID = generateOrderID();
  $taxedTransaction = new Transaction(
    '123',
    $taxedOrderID,
    $taxedOrderID,
    [],
    $originAddress,
    $destAddress,
    false,
    null,
    $today,
    $todayTime,
    $todayDateTime
  );

  $exemptOrderID = generateOrderID();
  $exemptTransaction = clone $taxedTransaction;
  $exemptTransaction->setExemptCert($blanketCert);
  $exemptTransaction->setCartID($exemptOrderID);
  $exemptTransaction->setOrderID($exemptOrderID);

  foreach ($cartItems as $cartItem) {
    $taxedTransaction->addCartItem(
      $cartItem->getIndex(),
      $cartItem->getItemID(),
      $cartItem->getTIC(),
      $cartItem->getPrice(),
      $cartItem->getQty(),
      0.0355
    );

    $exemptTransaction->addCartItem(
      $cartItem->getIndex(),
      $cartItem->getItemID(),
      $cartItem->getTIC(),
      $cartItem->getPrice(),
      $cartItem->getQty(),
      0
    );
  }

  $transactions = [
    $taxedTransaction,
    $exemptTransaction,
  ];
  $addTransactionsRequest = new AddTransactions($apiLoginID, $apiKey, $transactions);

  $client->AddTransactions($addTransactionsRequest);
} catch (Exception $ex) {
  echo 'Caught exception: ' . $ex->getMessage() . "\n";
}
