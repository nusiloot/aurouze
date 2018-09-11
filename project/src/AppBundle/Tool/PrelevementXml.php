<?php

namespace AppBundle\Tool;

use Digitick\Sepa\DomBuilder\DomBuilderFactory;
use Digitick\Sepa\GroupHeader;
use Digitick\Sepa\TransferFile\CustomerCreditTransferFile;
use Digitick\Sepa\TransferInformation\CustomerCreditTransferInformation;
use Digitick\Sepa\PaymentInformation;
use Digitick\Sepa\DomBuilder\CustomerCreditTransferDomBuilder;
use Digitick\Sepa\TransferFile\Factory\TransferFileFacadeFactory;

class PrelevementXml {

protected $banqueParameters;
protected $creditorId;
protected $creditorName;
protected $creditorAccountIBAN;
protected $creditorAgentBIC;
protected $debtorMandate;
protected $dateMandate;

protected $factures;
protected $directDebit;
protected $xml;

    public function __construct($factures,$banqueParameters)
    {
        $this->factures = $factures;

        $this->banqueParameters = $banqueParameters;
        $this->creditorId = $banqueParameters['creditorId'];
        $this->creditorName = $banqueParameters['creditorName'];
        $this->creditorAccountIBAN = $banqueParameters['creditorAccountIBAN'];
        $this->creditorAgentBIC = $banqueParameters['creditorAgentBIC'];
        $this->debtorMandate = $banqueParameters['debtorMandate'];
        $this->dateMandate = $banqueParameters['dateMandate'];

    }

    public function createPrelevement(){

        $header = new GroupHeader(date('Y-m-d-H-i-s'), 'Aurouze');
        $header->setInitiatingPartyId($this->creditorAccountIBAN); //ID Aurouze
        $this->directDebit = TransferFileFacadeFactory::createDirectDebitWithGroupHeader($header, 'pain.008.001.02');

        $date = new \DateTime('now');
        $idPrelevement = $this->createPrelevementId($date);

        // create a payment, it's possible to create multiple payments
        $this->directDebit->addPaymentInfo($idPrelevement, array(
            'id'                    => $idPrelevement,
            'dueDate'               => $date,
            'creditorName'          => $this->creditorName,
            'creditorAccountIBAN'   => $this->creditorAccountIBAN,
            'creditorAgentBIC'      => $this->creditorAgentBIC,
            'seqType'               => PaymentInformation::S_FIRST, // Le premier sera first après PaymentInformation::S_RECURRING
            'creditorId'            => $this->creditorId, //ID
            'localInstrumentCode'   => 'CORE'
        ));

        // Add a Single Transaction to the named payment
        $this->addTransferts($idPrelevement);

        // Retrieve the resulting XML
        $this->xml = $this->directDebit->asXML();
        file_put_contents(realpath('..').'/data/'.$idPrelevement.'.xml',$this->xml);

    }

    public function getXml(){
        return $this->xml;
    }


    public function addTransferts($idPrelevement){
        foreach ($this->factures as $key => $facture) {
            $this->directDebit->addTransfer($idPrelevement, array(
                'amount'                => ''.intval($facture->getMontantAPayer()*100),
                'debtorIban'            => $facture->getSociete()->getIban(),
                'debtorBic'             => $facture->getSociete()->getBic(),
                'debtorName'            => $facture->getSociete()->getRaisonSociale(),
                'debtorMandate'         => $this->debtorMandate, // MANDAT RUM /!\/!\/!\/!\/!\
                'debtorMandateSignDate' => $this->dateMandate, // MANDAT DATE /!\/!\/!\/!\/!\
                'remittanceInformation' => $facture->getNumeroFacture().' '.$this->dateFacturation().' '. str_replace(",",'EUR',"".$facture->getMontantAPayer()),
                'endToEndId'            => 'Aurouze Facture'
            ));
        }
    }

    public function createPrelevementId($date){
        return 'prelevement_aurouze_'.$date->format('Ymd-His');
    }


}
