<?php
    class TransportProtocolSMS extends TransportProtocol {
   		
    	function send(array $options = array('from' => NULL, 'to' => array(), 'subject' => NULL, 'text' => NULL)) {
			if (isNotNULL($options['text']) and isNotNULL($options['to'])) {
				if (mail(implode(', ', $options['to']), $options['subject'], $options['text'])) {
					// success
					return true;
				} else {
					// fail
					return false;
				}
			} else {
				return false;
			}
		}
     }
?>