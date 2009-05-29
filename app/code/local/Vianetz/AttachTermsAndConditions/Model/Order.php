<?php
/**
 * AttachTermsAndConditions Invoice Class
 * 
 * @category Vianetz
 * @package AttachTermsAndConditions
 * @author Christoph Massmann <C.Massmann@vianetz.com>
 */
class Vianetz_AttachTermsAndConditions_Model_Order extends
Mage_Sales_Model_Order
{
    /**
     * Sending email with order data
     *
     * @return Mage_Sales_Model_Order
     */
    public function sendNewOrderEmail()
    {
        if (!Mage::helper('sales')->canSendNewOrderEmail($this->getStore()->getId())) {
            return $this;
        }

        $translate = Mage::getSingleton('core/translate');
        /* @var $translate Mage_Core_Model_Translate */
        $translate->setTranslateInline(false);

        $paymentBlock = Mage::helper('payment')->getInfoBlock($this->getPayment())
            ->setIsSecureMode(true);

        $paymentBlock->getMethod()->setStore($this->getStore()->getId());

        $mailTemplate = Mage::getModel('core/email_template');
        /* @var $mailTemplate Mage_Core_Model_Email_Template */
        $copyTo = $this->_getEmails(self::XML_PATH_EMAIL_COPY_TO);
        $copyMethod = Mage::getStoreConfig(self::XML_PATH_EMAIL_COPY_METHOD, $this->getStoreId());
        if ($copyTo && $copyMethod == 'bcc') {
            foreach ($copyTo as $email) {
                $mailTemplate->addBcc($email);
            }
        }

        if ($this->getCustomerIsGuest()) {
            $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_GUEST_TEMPLATE, $this->getStoreId());
            $customerName = $this->getBillingAddress()->getName();
        } else {
            $template = Mage::getStoreConfig(self::XML_PATH_EMAIL_TEMPLATE, $this->getStoreId());
            $customerName = $this->getCustomerName();
        }

        $sendTo = array(
            array(
                'email' => $this->getCustomerEmail(),
                'name'  => $customerName
            )
        );
        if ($copyTo && $copyMethod == 'copy') {
            foreach ($copyTo as $email) {
                $sendTo[] = array(
                    'email' => $email,
                    'name'  => null
                );
            }
        }

try {
        if (Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
                $agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1);
//require_once('/srv/www/htdocs/html2text.php');
error_reporting(0);

            foreach ( $agreements as $agreement ) {
                $pdf = new Zend_Pdf();
                $style = new Zend_Pdf_Style();
/*
                $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA);
                $style->setFont($font, 10);

                $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                $pdf->pages[] = $page;
                $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
                $page->setFont($font, 11);
*/
$interp = new Vianetz_AttachTermsAndConditions_Model_Html2Text();
$text = $interp->html2pdf($pdf, $page, $style, utf8_encode($agreement->getContent()));

#Mage::log($agreement->getContent());
Mage::log($text);

#               $text = wordwrap($agreement->getContent(), 100, "\n", false);
                $text = explode("\n", $text);
                $y = 760;
                foreach ($text as $row) {
                    if ($y < 60) {
                        $page = $pdf->newPage(Zend_Pdf_Page::SIZE_A4);
                        $pdf->pages[] = $page;
                        $page->setFont($font, 11);

                        $y = 760;
                    } 
                    $page->drawText(trim(strip_tags(stripslashes($row))), 89, $y, 'UTF-8');
                    $y-=15;
                }

                $mailTemplate->addAttachment($pdf,
Mage::helper('sales')->__($agreement->getName().'.pdf'));
            }
        }
}
catch ( Exception $ex ) {
Mage::log("ERROR in AttachTermsAndConditions: ".$ex);
}

        foreach ($sendTo as $recipient) {
            $mailTemplate->setDesignConfig(array('area'=>'frontend', 'store'=>$this->getStoreId()))
                ->sendTransactional(
                    $template,
                    Mage::getStoreConfig(self::XML_PATH_EMAIL_IDENTITY, $this->getStoreId()),
                    $recipient['email'],
                    $recipient['name'],
                    array(
                        'order'         => $this,
                        'billing'       => $this->getBillingAddress(),
                        'payment_html'  => $paymentBlock->toHtml(),
                    )
                );
        }

        $translate->setTranslateInline(true);

        return $this;
    }
}
