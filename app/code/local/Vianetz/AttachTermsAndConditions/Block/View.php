<?php
class Vianetz_AttachTermsAndConditions_Block_View extends Mage_Core_Block_Template
{
	protected function _toHtml() {
            $agreements = Mage::getModel('checkout/agreement')->getCollection()
                    ->addStoreFilter(Mage::app()->getStore()->getId())
                    ->addFieldToFilter('is_active', 1)
                    ->addFieldToFilter('name', $this->getBlockId());

            $html = ""

            foreach ( $agreements as $agreement ) {
                $html .= $agreement->getContent();
                break;
            }

            return $html; 
	}
}


/* vim: set ts=4 sw=4 expandtab nu tw=90: */

