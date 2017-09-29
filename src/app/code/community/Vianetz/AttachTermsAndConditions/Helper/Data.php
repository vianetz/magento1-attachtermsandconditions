<?php
/**
 * AttachTermsAndConditions Helper Class
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
 * @package     Vianetz\AttachTermsAndConditions
 * @author      Christoph Massmann, <C.Massmann@vianetz.com>
 * @link        http://www.vianetz.com
 * @copyright   Copyright (c) since 2006 vianetz - Dipl.-Ing. C. Massmann (http://www.vianetz.com)
 * @license     http://www.vianetz.com/license Commercial Software License
 * @version     %%MODULE_VERSION%%
 */
class Vianetz_AttachTermsAndConditions_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Log message to file if enabled in system configuration.
     *
     * @param string $message
     * @param int $type
     *
     * @return Vianetz_AttachTermsAndConditions_Helper_Data
     */
    public function log($message, $type = LOG_DEBUG)
    {
        Mage::helper('vianetz_core/log')->log($message, $type, 'Vianetz_AttachTermsAndConditions');

        return $this;
    }
}