<?php

/**
 * Portions Copyright (c) 2009-2012 The Federal Tax Authority, LLC (FedTax).
 * All Rights Reserved.
 *
 * This file contains Original Code and/or Modifications of Original Code as
 * defined in and that are subject to the FedTax Public Source License (the
 * ‘License’). You may not use this file except in compliance with the License.
 * Please obtain a copy of the License at http://FedTax.net/ftpsl.pdf or
 * http://dev.taxcloud.net/ftpsl/ and read it before using this file.
 *
 * The Original Code and all software distributed under the License are
 * distributed on an ‘AS IS’ basis, WITHOUT WARRANTY OF ANY KIND, EITHER
 * EXPRESS OR IMPLIED, AND FEDTAX  HEREBY DISCLAIMS ALL SUCH WARRANTIES,
 * INCLUDING WITHOUT LIMITATION, ANY WARRANTIES OF MERCHANTABILITY, FITNESS FOR
 * A PARTICULAR PURPOSE, QUIET ENJOYMENT OR NON-INFRINGEMENT.
 *
 * Please see the License for the specific language governing rights and
 * limitations under the License.
 *
 * Modifications made April 25, 2017 by Brett Porcelli
 */

namespace TaxCloud;

class ExemptionCertificate extends ExemptionCertificateBase
{
  protected $Detail; // ExemptionCertificateDetail

  public function __construct(array $ExemptStates, $SinglePurchase = 0, $SinglePurchaseOrderNumber, $PurchaserFirstName, $PurchaserLastName, $PurchaserTitle, $PurchaserAddress1, $PurchaserAddress2 = '', $PurchaserCity, $PurchaserState, $PurchaserZip, TaxID $PurchaserTaxID, $PurchaserBusinessType, $PurchaserBusinessTypeOtherValue = '', $PurchaserExemptionReason, $PurchaseExemptionReasonValue, $CreatedDate = NULL)
  {
    parent::__construct();
    
    $this->Detail = new ExemptionCertificateDetail(
      $ExemptStates,
      $SinglePurchase,
      $SinglePurchaseOrderNumber,
      $PurchaserFirstName,
      $PurchaserLastName,
      $PurchaserTitle,
      $PurchaserAddress1,
      $PurchaserAddress2,
      $PurchaserCity,
      $PurchaserState,
      $PurchaserZip,
      $PurchaserTaxID,
      $PurchaserBusinessType,
      $PurchaserBusinessTypeOtherValue,
      $PurchaserExemptionReason,
      $PurchaseExemptionReasonValue,
      $CreatedDate
    );
  }

  public function getDetail()
  {
    return $this->Detail;
  }

  /**
   * Create ExemptionCertificate given array.
   *
   * @since 0.2.0
   *
   * @param  array $certificate
   * @return ExemptionCertificate
   */
  public static function fromArray($certificate) {
    $states = array();

    $detail = $certificate['Detail'];

    foreach ($detail['ExemptStates'] as $state) {
      $states[] = new ExemptState($state['StateAbbr'], $state['ReasonForExemption'], $state['IdentificationNumber']);
    }

    $taxID = new TaxID(
      $detail['PurchaserTaxID']['TaxType'],
      $detail['PurchaserTaxID']['IDNumber'],
      $detail['PurchaserTaxID']['StateOfIssue']
    );

    $cert = new self(
      $states, 
      $detail['SinglePurchase'],
      $detail['SinglePurchaseOrderNumber'],
      $detail['PurchaserFirstName'],
      $detail['PurchaserLastName'],
      $detail['PurchaserTitle'],
      $detail['PurchaserAddress1'],
      $detail['PurchaserAddress2'],
      $detail['PurchaserCity'],
      $detail['PurchaserState'],
      $detail['PurchaserZip'],
      $taxID,
      $detail['PurchaserBusinessType'],
      $detail['PurchaserBusinessTypeOtherValue'],
      $detail['PurchaserExemptionReason'],
      $detail['PurchaserExemptionReasonValue'],
      $detail['CreatedDate']
    );

    $cert->CertificateID = $certificate['CertificateID'];
    
    return $cert;
  }
}
