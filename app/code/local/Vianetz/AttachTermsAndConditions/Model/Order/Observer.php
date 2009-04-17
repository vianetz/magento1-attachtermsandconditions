<?php
class Vianetz_AttachTermsAndConditions_Model_Order_Observer
{
    public function __construct()
    {
    }

    public function attachTermsAndConditions($observer)
    {
       $event = $observer->getEvent();
       $order = $event->getOrder();
Mage::log("in local observer TERMS AND CONDITIONS");
#Mage::log($order);
#Mage::log(Mage::getSingleton('checkout/agreement'));

            if (!Mage::getStoreConfigFlag('checkout/options/enable_agreements')) {
                $agreements = array();
            } else {
                $agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1);
            }
#Mage::log($agreements);

	require_once('/dompdf-0.5.1/dompdf_config.inc.php');

	foreach ( $agreements as $agreement ) {
Mage::log($agreement->getContent());
		$dompdf = new DOMPDF();
		$dompdf->load_html($agreement->getContent());
		$dompdf->render();
		$dompdf->
		break;
	}


        if ($order->canInvoice()) {
            $invoice = $order->prepareInvoice();

            $invoice->register();
            Mage::getModel('core/resource_transaction')
               ->addObject($invoice)
               ->addObject($invoice->getOrder())
               ->save();

            $invoice->sendEmail(true, '');
        }
    }
}
