<?php
/**
 * AttachTermsAndConditions Invoice Class
 * 
 * @category Vianetz
 * @package AttachTermsAndConditions
 * @author Christoph Massmann <C.Massmann@vianetz.com>
 */
class Vianetz_AttachTermsAndConditions_Model_Order_Invoice extends
Mage_Sales_Model_Order_Invoice
{  
    /**
     * Sending email with Invoice data
     *
     * @param bool $notifyCustomer
     * @param string $comment
     * @return Mage_Sales_Model_Order_Invoice
     */
    public function sendEmail($notifyCustomer=true, $comment='')
    {
        if (!Mage::helper('sales')->canSendNewInvoiceEmail($this->getOrder()->getStore()->getId())) {
            return $this;
        }

        $currentDesign = Mage::getDesign()->setAllGetOld(array(
            'package' => Mage::getStoreConfig('design/package/name', $this->getStoreId()),
            'store'   => $this->getStoreId()
        ));

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $order  = $this->getOrder();
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStoreId());

        if (!$notifyCustomer && !$copyTo) {
            return $this;
        }
        $paymentBlock   = Mage::helper('payment')->getInfoBlock($order->getPayment())
            ->setIsSecureMode(true);

        $mailTemplate = Mage::getModel('core/email_template');

        if ($order->getCustomerIsGuest()) {
            $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $order->getStoreId());
            $customerName = $order->getBillingAddress()->getName();
        } else {
            $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $order->getStoreId());
            $customerName = $order->getCustomerName();
        }

        if ($notifyCustomer) {
            $sendTo[] = array(
                'name'  => $customerName,
                'email' => $order->getCustomerEmail()
            );
            if ($copyTo && $copyMethod == 'bcc') {
                foreach ($copyTo as $email) {
                    $mailTemplate->addBcc($email);
                }
            }

        }

        if ($copyTo && ($copyMethod == 'copy' || !$notifyCustomer)) {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'name'  => null,
                    'email' => $email
                );
            }
        }

        // BEGIN Customization by vianetz
        // 2009-04-17
            if (Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
                $agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1);
#Mage::log($agreements);

        foreach ( $agreements as $agreement ) {
Mage::log($agreement);

$pdf = new Zend_Pdf();
         $style = new Zend_Pdf_Style();

      $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
       $style->setFont($font, 10);


Mage::log("TEST..");
Mage::log($agreement->getName());

$page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
$pdf->pages[] = $page;
$page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
$page->setFont($font, 10);

Mage::log("TEST...");
$page->drawText("Allgemeine Geschaeftsbedingungen", 89, 700, 'UTF-8');
$text = wordwrap($agreement->getContent(), 70, "\n", false);
    $text = explode("\n", $text);
$y = 720;
    foreach ($text as $row) {
        if ($y < 60) {
            $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
            $pdf->pages[] = $page;
            $page->setFont(Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA),
12);
            $y = 760;
        } 
        $page->drawText($row, 89, $y);
        $y-=15;
    }

Mage::log("TEST....");
            $mailTemplate->addAttachment($pdf,
Mage::helper('sales')->__($agreement->getName().'.pdf'));
                break;
        }


        }

        // END Customization by vianetz

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$order->getStoreId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $order->getStoreId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'       => $order,
                        'invoice'     => $this,
                        'comment'     => $comment,
                        'billing'     => $order->getBillingAddress(),
                        'payment_html'=> $paymentBlock->toHtml(),
                    )
                );
        }

        $translate->setTranslateInline(true);

        Mage::getDesign()->setAllGetOld($currentDesign);

        return $this;
    }
}

/* vim: set ts=4 sw=4 expandtab nu tw=90: */
