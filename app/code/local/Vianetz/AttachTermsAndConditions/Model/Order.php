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

        if (Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
                $agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1);

            foreach ( $agreements as $agreement ) {
		$baseDir = Mage::getBaseDir('media');
		$file = $baseDir . '/' . $agreement->getName() . '.pdf';
		if ( file_exists($file)) {
                	$mailTemplate->addAttachment(file_get_contents($file), Mage::helper('sales')->__($agreement->getName()).'.pdf');
		}
            }
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
