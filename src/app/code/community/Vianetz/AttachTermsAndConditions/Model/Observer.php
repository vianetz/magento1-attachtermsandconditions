<?php
/**
 * AttachTermsAndConditions Email Observer
 *
 * @section LICENSE
 * This file is created by vianetz <info@vianetz.com>.
 * The Magento module is distributed under a commercial license.
 * Any redistribution, copy or direct modification is explicitly not allowed.
 *
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@vianetz.com so we can send you a copy immediately.
 *
 * @category    Vianetz
 * @package     Vianetz_AttachTermsAndConditions
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        http://www.vianetz.com
 * @copyright   Copyright (c) 2006-16 vianetz - C. Massmann (http://www.vianetz.com)
 * @license     http://www.vianetz.com/license Commercial Software License
 * @version     %%MODULE_VERSION%%
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

            $orderEmailTemplate = Mage::getStoreConfig(Mage_Sales_Model_Order::XML_PATH_EMAIL_TEMPLATE, $mailer->getStoreId());
            if ($mailer->getTemplateId() != $orderEmailTemplate) {
                return $this;
            }

            /** @var Mage_Core_Model_Email_Template $emailTemplate */
            $emailTemplate = $observer->getEvent()->getEmailTemplate();

            $agreements = Mage::getModel('checkout/agreement')->getCollection()->addStoreFilter($this->getStoreId())->addFieldToFilter('is_active', 1);

            if (count($agreements) === 0) {
                $this->getHelper()->log('No active agreements found for store ' . $this->getStoreId() . '.');
                return $this;
            }

            foreach ($agreements as $agreement) {
                $file = Mage::getBaseDir('media') . DS . $agreement->getName() . '.pdf';
                $this->getHelper()->log('Searching for attachment file ' . $file);

                if (file_exists($file) === true) {
                    $this->getHelper()->log('Attaching ' . $file . ' to order email for order #' . $this->getOrder()->getIncrementId() . '.');

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
     * @return integer
     */
    private function getStoreId()
    {
        $storeId = (int)Mage::app()->getStore()->getId();
        $order = $this->getOrder();

        if ($storeId === Mage_Core_Model_App::ADMIN_STORE_ID && empty($order) === false) {
            $storeId = $this->getOrder()->getStoreId();
        }

        return $storeId;
    }

    /**
     * @return Mage_Sales_Model_Order
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
}