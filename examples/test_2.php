<?php
require_once("vendor/autoload.php");

use pizzo90\DHLExpress\Ship;
use pizzo90\DHLExpress\Address;
use pizzo90\DHLExpress\Shipper;
use pizzo90\DHLExpress\Contact;
use pizzo90\DHLExpress\Packages;
use pizzo90\DHLExpress\Recipient;
use pizzo90\DHLExpress\Commodities;
use pizzo90\DHLExpress\Credentials;
use pizzo90\DHLExpress\ShipmentInfo;
use pizzo90\DHLExpress\ShipmentRequest;
use pizzo90\DHLExpress\RequestedPackage;
use pizzo90\DHLExpress\RequestedShipment;
use pizzo90\DHLExpress\InternationalDetail;

$credentials = new Credentials(true);
$credentials
    ->setUsername('YOUR-USERNAME')
    ->setPassword('YOUR-PASSWORD');

$shipmentInfo = new ShipmentInfo();
$shipmentInfo
    ->setDropOffType(ShipmentInfo::DROP_OFF_TYPE_REGULAR_PICKUP)
    ->setServiceType(ShipmentInfo::SERVICE_TYPE_DOMESTIC_EXPRESS_BEFORE_0900)
    ->setAccount('YOUR-ACCOUNT')
    ->setCurrency('EUR')
    ->setUnitOfMeasurement(ShipmentInfo::UNIT_OF_MEASRUREMENTS_KG_CM)
    ->setLabelType(ShipmentInfo::LABEL_TYPE_PDF)
    ->setLabelTemplate(ShipmentInfo::LABEL_TEMPLATE_ECOM26_A6_002);

$shipperContact = new Contact();
$shipperContact
    ->setPersonName('Max Mustermann')
    ->setCompanyName('Acme Inc.')
    ->setPhoneNumber('0123456789')
    ->setEmailAddress('max.mustermann@example.com');

$shipperAddress = new Address();
$shipperAddress
    ->setStreetLines('Hauptstrasse 1')
    ->setCity('Berlin')
    ->setPostalCode('10317')
    ->setCountryCode('DE');

$shipper = new Shipper();
$shipper
    ->setContact($shipperContact)
    ->setAddress($shipperAddress);

$recipientContact = new Contact();
$recipientContact
    ->setPersonName('Max Mustermann')
    ->setCompanyName('Acme Inc.')
    ->setPhoneNumber('0123456789')
    ->setEmailAddress('max.mustermann@example.com');

$recipientAddress = new Address();
$recipientAddress
    ->setStreetLines('Hauptstrasse 1')
    ->setCity('Berlin')
    ->setPostalCode('10317')
    ->setCountryCode('DE');

$recipient = new Recipient();
$recipient
    ->setContact($recipientContact)
    ->setAddress($recipientAddress);

$ship = new Ship();
$ship
    ->setShipper($shipper)
    ->setRecipient($recipient);

$package1 = new RequestedPackage();
$package1
    ->setWeight(2)
    ->setDimensions(1, 2, 3)
    ->setCustomerReferences('test 1');

$package2 = new RequestedPackage();
$package2
    ->setWeight(2)
    ->setDimensions(1, 2, 3)
    ->setCustomerReferences('test 2');

$packages = new Packages();
$packages
    ->addRequestedPackage($package1)
    ->addRequestedPackage($package2);

$commodities = new Commodities();
$commodities->setDescription('Stuff');

// The InternationalDetail seems to be required even if its a domestic package
$internationalDetail = new InternationalDetail();
$internationalDetail
    ->setCommodities($commodities)
    ->setContent(InternationalDetail::CONTENT_DOCUMENTS);

$timestamp = new DateTime("now", new DateTimeZone("Europe/Berlin"));
$timestamp->modify('+3 days');

$requestedShipment = new RequestedShipment();
$requestedShipment
    ->setShipmentInfo($shipmentInfo)
    ->setShipTimestamp($timestamp)
    ->setPaymentInfo(RequestedShipment::PAYMENT_INFO_DELIVERED_AT_PLACE)
    ->setShip($ship)
    ->setPackages($packages)
    ->setInternationalDetail($internationalDetail);

$shipment = new ShipmentRequest($credentials);
$shipment->setRequestedShipment($requestedShipment);
$response = $shipment->send();

if ($response->isSuccessful()) {
    print_r($response->getTrackingNumber());
    file_put_contents('label_2.pdf', base64_decode($response->getLabel()));
} else {
    print_r($response->getErrors());
}