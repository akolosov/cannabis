<?php
	class TransportSMTPMail extends Transport {

		function send(array $options = array('from' => NULL, 'to' => array(), 'subject' => NULL, 'text' => NULL)) {

			if (isNotNULL($options['text']) and isNotNULL($options['to'])) {
				$connect = array('host' => $this->getProperty('server_address'),
								 'port' => $this->getProperty('server_port'),
								 'helo' => $_SERVER['HTTP_HOST']);

				if (isNotNULL($this->getProperty('server_login')) and isNotNULL($this->getProperty('server_passwd'))) {
					$connect['user'] = $this->getProperty('server_login');
					$connect['pass'] = $this->getProperty('server_passwd');
					$connect['auth'] = true;
				}
				
				if (isNULL($options['from'])) {
					$options['from'] = $this->getConstantValue('server_email');
				}

				$smtp = new TransportProtocolSMTP($this, $connect);
				if ($smtp->connect()) {
					// success
					$data = array('from' => $options['from'],
								  'recipients' => array_unique($options['to']),
								  'body' => stripslashes($options['text']),
								  'headers' => array('Content-Type: text/plain; charset='.DEFAULT_CHARSET,
								  					'X-Mailer: '.ENGINE_NAME." v".ENGINE_VERSION."/".ENGINE_BUILD),
													);

					if (isNotNULL($options['subject'])) {
						$data['headers'][] = 'Subject: '.encodeEMail(stripslashes($options['subject']), DEFAULT_CHARSET);
					} else {
						$data['headers'][] = 'Subject: '.encodeEMail('нет темы', DEFAULT_CHARSET);
					}
					
					if ($smtp->send($data)) {
						// succcess
						$smtp->quit();
						return true;
					} else {
						// fail
						return false;
					}
				} else {
					// fail
				}
			} else {
				return false;
			}
		}
	}
?>