<?php
	class TransportSMS extends Transport {

		function send(array $options = array('from' => NULL, 'to' => array(), 'subject' => NULL, 'text' => NULL)) {
			if (isNULL($options['subject'])) {
				$options['subject'] = 'нет темы';
			}
				
			if (isNULL($options['from'])) {
				$options['from'] = $this->getConstantValue('server_email');
			}
			
			$transport = new TransportProtocolSMS($this);
			return $transport->send($options);
		}
	}
?>