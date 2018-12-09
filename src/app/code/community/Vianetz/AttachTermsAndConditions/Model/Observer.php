<?php
/**
 * AttachTermsAndConditions Email Observer
 *
 * @section LICENSE
 * This file is created by vianetz <info@vianetz.com>.
 * The code is distributed under the GPL license.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@vianetz.com so we can send you a copy immediately.
 *
 * @category    Vianetz
 * @package     Vianetz\AttachTermsAndConditions
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        https://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (https://www.vianetz.com)
 * @license     http://www.gnu.org/licenses/gpl-3.0.txt GNU GENERAL PUBLIC LICENSE
 */
class Vianetz_AttachTermsAndConditions_Model_Observer
{
    /**
     * Add pdf documents as email attachments.
     *
     * Event: vianetz_pdfattachments_email_template_init
     *
     * @param Varien_Event_Observer $observer
     *
     * @return Vianetz_AttachTermsAndConditions_Model_Observer
     */
    public function addPdfAttachment(Varien_Event_Observer $observer)
    {
        try {
            /** @var Mage_Core_Model_Email_Template_Mailer $mailer */
            $mailer = $observer->getEvent()->getMailer();

            $orderEmailTemplateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $mailer->getStoreId());
            $orderEmailGuestTemplateId = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_GUEST_TEMPLATE, $mailer->getStoreId());

            if ($mailer->getTemplateId() != $orderEmailTemplateId && $mailer->getTemplateId() != $orderEmailGuestTemplateId) {
                return $this;
            }

            /** @var Mage_Core_Model_Email_Template $emailTemplate */
            $emailTemplate = $observer->getEvent()->getEmailTemplate();

            foreach ($this->getAgreements() as $agreement) {
                $file = Mage::getBaseDir('media') . DS . $agreement->getName() . '.pdf';
                $this->getHelper()->log('Searching for attachment file ' . $file);

                if (file_exists($file) === true) {
                    $orderId = ($this->getOrder() !== null) ? $this->getOrder()->getIncrementId() : '';
                    $this->getHelper()->log('Attaching ' . $file . ' to order email for order #' . $orderId . '.');

                    $filename = Mage::helper('sales')->__($agreement->getName()) . '.pdf';
                    Mage::helper('pdfattachments')->addAttachmentToEmail($emailTemplate, file_get_contents($file), $filename);
                }
            }
        } catch (Exception $exception) {
            $this->getHelper()->log('Error while attaching pdf document: ' . $exception->getMessage() . ' ' . $exception->getTraceAsString());
        }

        return $this;
    }

    /**
     * @return int
     * @throws \Mage_Core_Model_Store_Exception
     */
    private function getStoreId()
    {
        $storeId = (int)Mage::app()->getStore()->getId();

        if ($storeId === Mage_Core_Model_App::ADMIN_STORE_ID && $this->getOrder() !== null) {
            $storeId = $this->getOrder()->getStoreId();
        }

        return $storeId;
    }

    /**
     * @return Mage_Sales_Model_Order|null
     */
    private function getOrder()
    {
        return Mage::registry('current_order');
    }

    /**
     * @return \Vianetz_AttachTermsAndConditions_Helper_Data
     */
    private function getHelper()
    {
        return Mage::helper('attachtermsandconditions');
    }

    /**
     * @return \Mage_Checkout_Model_Resource_Agreement_Collection
     * @throws \Mage_Core_Model_Store_Exception
     */
    private function getAgreements()
    {
        $this->getHelper()->log('Looking for all agreements in store ' . $this->getStoreId());
        $agreements = Mage::getModel('checkout/agreement')->getCollection()
            ->addStoreFilter($this->getStoreId());

        $isFilterActiveAgreements = Mage::getStoreConfigFlag('vianetz_attachtermsandconditions', $this->getStoreId());
        if ($isFilterActiveAgreements === true) {
            $agreements->addFieldToFilter('is_active', 1);
        }

        if (count($agreements) === 0) {
            $this->getHelper()->log('No active agreements found for store ' . $this->getStoreId() . '.');
        }

        return $agreements;
    }
}