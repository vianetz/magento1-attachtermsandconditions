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
     * @param Zend_Pdf $pdf
     * @param string $filename
     */
    public function addAttachment(Zend_Pdf $pdf, $filename){
        $file = $pdf->render();
        $attachment = $this->getMail()->createAttachment($file);
        $attachment->type = 'application/pdf';
        $attachment->filename = $filename;
    } 
}

/* vim: set ts=4 sw=4 expandtab nu tw=90: */
