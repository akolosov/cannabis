<?php
	class TransportMail extends Transport {

		function send(array $options = array('from' => NULL, 'to' => array(), 'subject' => NULL, 'text' => NULL)) {
			if (isNotNULL($options['text']) and isNotNULL($options['to'])) {
				if (isNULL($options['subject'])) {
					$options['subject'] = 'нет темы';
				} else {
					$options['subject'] = encodeEMail($options['subject'], DEFAULT_CHARSET);
				}
				
				if (isNULL($options["from"])) {
					$options["from"] = $this->getConstantValue("server_email");
				}

				if (mail(implode(", ", $options["to"]), $options["subject"], $options["text"], "Content-Type: text/plain; charset=".DEFAULT_CHARSET)) {
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