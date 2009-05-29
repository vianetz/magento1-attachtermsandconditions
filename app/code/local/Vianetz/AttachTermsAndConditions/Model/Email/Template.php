<?php
/**
 * AttachTermsAndConditions Email Class
 * 
 * @category Vianetz
 * @package AttachTermsAndConditions
 * @author Christoph Massmann <C.Massmann@vianetz.com>
 */
class Vianetz_AttachTermsAndConditions_Model_Email_Template extends Mage_Core_Model_Email_Template
{  
    /**
     * Add attachment to email
     *
     * @param string $file
     * @param string $filename
     */
    public function addAttachment($file, $filename){
        $attachment = $this->getMail()->createAttachment($file);
        $attachment->type = 'application/pdf';
        $attachment->filename = $filename;
        $attachment->disposition = Zend_Mime::DISPOSITION_ATTACHMENT;
        $attachment->encoding = Zend_Mime::ENCODING_BASE64;
    } 
}

/* vim: set ts=4 sw=4 expandtab nu tw=90: */
