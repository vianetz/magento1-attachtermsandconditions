<?php
/**
 * AttachTermsAndConditions Helper Class
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
        $extensionNamespace = 'Vianetz_AttachTermsAndConditions';

        $extensionVersion = Mage::getConfig()->getModuleConfig($extensionNamespace)->version;
        $message = $extensionNamespace . ' v' . $extensionVersion . ': ' . $message;
        $logFilename = $extensionNamespace . '.log';

        Mage::log($message, $type, $logFilename, true);

        return $this;
    }
}