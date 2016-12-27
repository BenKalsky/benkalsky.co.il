<?php

class contactForm{
	
		
	function contactForm($cfg)
	{
		
		
		$this->cfg['email_address'] = isset($cfg['email_address'])?$cfg['email_address']:'';
		
		// =?UTF-8?B? required to avoid bad character encoding in the From field
		// é (keeps utf-8 encoding in the file)
		$this->cfg['email_from'] = (isset($cfg['email_from']) && $cfg['email_from'])?'=?UTF-8?B?'.base64_encode($cfg['email_from']).'?=':$this->cfg['email_address'];
		$this->cfg['email_address_cc'] = isset($cfg['email_address_cc'])?$cfg['email_address_cc']:'';
		$this->cfg['email_address_bcc'] = isset($cfg['email_address_bcc'])?$cfg['email_address_bcc']:'';
		
		$this->cfg['timezone'] = isset($cfg['timezone'])?$cfg['timezone']:'';
		
		$this->cfg['adminnotification_subject'] = isset($cfg['adminnotification_subject'])?$cfg['adminnotification_subject']:'';
		
		$this->cfg['usernotification_insertformdata'] = isset($cfg['usernotification_insertformdata'])?$cfg['usernotification_insertformdata']:'';
		$this->cfg['usernotification_inputid'] = isset($cfg['usernotification_inputid'])?$cfg['usernotification_inputid']:'';
		$this->cfg['usernotification_subject'] = isset($cfg['usernotification_subject'])?$cfg['usernotification_subject']:'';
		$this->cfg['usernotification_message'] = isset($cfg['usernotification_message'])?preg_replace('#<br(\s*)/>|<br(\s*)>#i', "\r\n",$cfg['usernotification_message']):'';
		
		$this->cfg['form_name'] = isset($cfg['form_name'])?$cfg['form_name']:'';
		
		$this->cfg['form_errormessage_captcha'] = isset($cfg['form_errormessage_captcha'])?$cfg['form_errormessage_captcha']:'';
		$this->cfg['form_errormessage_emptyfield'] = isset($cfg['form_errormessage_emptyfield'])?$cfg['form_errormessage_emptyfield']:'';
		$this->cfg['form_errormessage_invalidemailaddress'] = isset($cfg['form_errormessage_invalidemailaddress'])?$cfg['form_errormessage_invalidemailaddress']:'';
		$this->cfg['form_validationmessage'] = isset($cfg['form_validationmessage'])?$cfg['form_validationmessage']:'';
		$this->cfg['form_redirecturl'] = isset($cfg['form_redirecturl'])?$cfg['form_redirecturl']:'';
		
		$this->dash_line = '--------------------------------------------------------------';
		
		$this->mail_content_type_format = 'plaintext'; // html
		
		if($this->mail_content_type_format == 'plaintext')
		{
			$this->mail_content_type_format_charset = 'Content-type: text/plain; charset=utf-8';
			$this->mail_line_break = "\r\n";
		}
		if($this->mail_content_type_format == 'html')
		{
			$this->mail_content_type_format_charset = 'Content-type: text/html; charset=utf-8';
			$this->mail_line_break = "<br />";
		}
		
		
		/**
		 * USER NOTIFICATION MAIL FORMAT
		 */
		$this->cfg['usernotification_format'] = isset($cfg['usernotification_format'])?$cfg['usernotification_format']:'';
		
		if($this->cfg['usernotification_format'] == 'plaintext')
		{
			$this->mail_content_type_format_charset_usernotification = 'Content-type: text/plain; charset=utf-8';
			$this->mail_line_break_usernotification = "\r\n";
		}
		
		if($this->cfg['usernotification_format'] == 'html')
		{
			$this->mail_content_type_format_charset_usernotification = 'Content-type: text/html; charset=utf-8';
			$this->mail_line_break_usernotification = "<br />";
		}
		
		
		$this->merge_post_index = 0;
		
		$this->demo = 0;
		
		$this->envato_link = '';
	}
	
	
	function sendMail($param)
	{
		$count_files_to_attach = 0;
		
		// grab and insert the form URL in the notification message
		$form_url = (@$_SERVER['HTTPS'] == 'on') ? 'https://' : 'http://';
		
		if($_SERVER['SERVER_PORT'] != '80')
		{
			$form_url .= $_SERVER['SERVER_NAME'].':'.$_SERVER['SERVER_PORT'].rawurlencode($_SERVER['SCRIPT_NAME']);
		}
		else 
		{
			$form_url .= $_SERVER['SERVER_NAME'].rawurlencode($_SERVER['SCRIPT_NAME']);
		}
		
		$form_url = str_replace('%2F', '/', $form_url);
		
		$form_url_exp = explode('/', $form_url);
		
		
		// remove contactform/inc/form-validation.php
		$pattern_slash = $form_url_exp[count($form_url_exp)-3].'/'.$form_url_exp[count($form_url_exp)-2].'/'.$form_url_exp[count($form_url_exp)-1];
		
		$form_url = str_replace($pattern_slash, '', $form_url);
		
		if($this->cfg['timezone'])
		{
			date_default_timezone_set($this->cfg['timezone']);
		}
		
		// g:i A | 01:37 AM
		// G:i | 13:37
		$mail_body = $this->cfg['adminnotification_subject'].': '.@date("F jS, Y, G:i")
							.$this->mail_line_break.$this->mail_line_break.$this->cfg['form_name']
							.$this->mail_line_break.$this->mail_line_break.'Form URL: '
							.$this->mail_line_break.$form_url
							.$this->mail_line_break.$this->dash_line;

		if($this->merge_post)
		{
			foreach($this->merge_post as $value)
			{
				if(
				   isset($value['element_type']) && $value['element_type'] == 'upload'
				   && isset($value['filename']) && $value['filename']
				   )
				{
					
					if( isset($value['deletefile']) && ($value['deletefile'] == 1 || $value['deletefile'] == 2) )
					{
						$count_files_to_attach++;
					}
					

					$explode_requesturi = explode('/',$_SERVER['REQUEST_URI']);
					//print_r($explode_requesturi);
					
					$explode_requesturi = explode('/',$_SERVER['SCRIPT_NAME']);
					//print_r($explode_requesturi);

					$inc_form_validation = $explode_requesturi[count($explode_requesturi)-2].'/'.$explode_requesturi[count($explode_requesturi)-1] ;

					$install_dir = str_replace($inc_form_validation,'',$_SERVER['SCRIPT_NAME']);
					
					
					
					$mail_body .= $this->mail_line_break.$this->mail_line_break.$value['elementlabel_value'].': '.$value['element_value'];
					
					// No file link if we delete the file after the upload
					// 1: File Attachment + Download Link
					// 2: File Attachment Only
					if( isset($value['deletefile']) && ($value['deletefile'] == 1 || $value['deletefile'] == 3) )
					{
						$mail_body .= $this->mail_line_break
											.'http://'.$_SERVER['SERVER_NAME']
											.str_replace('%2F', '/', rawurlencode($install_dir.'upload/'.$value['element_value']));
					}

				} 
				else{
					$mail_body .= $this->mail_line_break.$this->mail_line_break.$value['elementlabel_value'].': '.$value['element_value'];
				}
			}
		}
		
		$mail_body .= $this->mail_line_break.$this->mail_line_break.$this->dash_line;
		$mail_body .= $this->mail_line_break.'IP address: '.$_SERVER['REMOTE_ADDR'];
		$mail_body .= $this->mail_line_break.'Host: '.gethostbyaddr($_SERVER['REMOTE_ADDR']);
		
		if(preg_match('#html#', $this->mail_content_type_format_charset))
		{
			$mail_body = nl2br($mail_body);
		}
		
		if($this->demo != 1)
		{
			// for the admin: if the user provides his email address, it will appear in the "from" field
			$param['reply_emailaddress'] = (isset($param['reply_emailaddress']) && $param['reply_emailaddress'])?$param['reply_emailaddress']:$this->cfg['email_address'];
			
			// for the admin: if the user provides his email address, it will appear in the "reply-to" field
			$replyto_name = $param['reply_emailaddress']?$param['reply_emailaddress']:'';
			$replyto_address = $param['reply_emailaddress']?$param['reply_emailaddress']:'';
			
			$mailheaders_options = array(
														'from'=>array('name'=>$param['reply_emailaddress'], 'address'=>$param['reply_emailaddress']),
														'replyto'=>array('name'=>$replyto_name, 'address'=>$replyto_address),
														'cc'=>array('address'=>$this->cfg['email_address_cc']),
														'bcc'=>array('address'=>$this->cfg['email_address_bcc'])
													   );
			
			
			
			
			$mailheaders = $this->getMailHeaders($mailheaders_options);
			
			
			//if(!isset($param['uploads']) || !$param['uploads'])
			if(!$count_files_to_attach)
			{
				$mailheaders .= $this->mail_content_type_format_charset."\r\n";
				
				$mailmessage = $mail_body;
			} else
			{

				// boundary 
				$semi_rand = md5(time());
				$mime_boundary = "==Multipart_Boundary_x{$semi_rand}x";
					 
				// headers for attachment 
				$mailheaders .= "MIME-Version: 1.0\n"
										."Content-Type: multipart/mixed;\n"
										." boundary=\"{$mime_boundary}\"";
					 
				// multipart boundary 
				
				$mailmessage = "This is a multi-part message in MIME format.\n\n"
										."--{$mime_boundary}\n"
										.$this->mail_content_type_format_charset."\n"
										."Content-Transfer-Encoding: 7bit\n\n"
										.$mail_body
										."\n\n";
									
				$mailmessage .= "--{$mime_boundary}\n";
					 
				// preparing attachments
				$count_attached_file = 0;
					
				foreach($this->merge_post as $value)
				{
						if(
							isset($value['element_type']) && $value['element_type'] == 'upload'
							&& isset($value['filename']) && $value['filename']
							&& isset($value['deletefile']) && ($value['deletefile'] == 1 || $value['deletefile'] == 2)																	   
						)
						{
							$count_attached_file++;
								
							$file = fopen('../upload/'.$value['filename'],"rb");
							$data = fread($file,filesize('../upload/'.$value['filename']));
							fclose($file);
								
							$data = chunk_split(base64_encode($data));
							
							$mailmessage .= 'Content-Type: {"application/octet-stream"};'."\n" . ' name="'.$value['filename'].'"'."\n" 
													.'Content-Disposition: attachment;'."\n" . ' filename="'.$value['filename'].'"'."\n" 
													.'Content-Transfer-Encoding: base64'."\n\n" . $data . "\n\n";
							
							// "--" must be added for the last file, or an empty file will be also attached in the message
							if($count_attached_file == $count_files_to_attach)
							{
								$mailmessage .= "--{$mime_boundary}--\n";
							} else{
								$mailmessage .= "--{$mime_boundary}\n";
							}
								
							// delete attached file?
							// this is different from deleting the file when the user deletes the file himself in the from: check form-validation.php for this (in form-validation.php because the file must be deleted even if sendMail() is not called - when there are errors for example)
							if(isset($value['deletefile']) && $value['deletefile'] == 2)
							{
								@unlink('../upload/'.$value['filename']);
							}
						}
				} // foreach
			} // if(!$count_files_to_attach)
			
			@mail($this->cfg['email_address'], $this->cfg['adminnotification_subject'], $mailmessage, $mailheaders);
			
		}
	}
	
	
	function sendMailReceipt($value)
	{
		if($this->demo != 1)
		{
			
			$mailheaders_options = array(
														'from'=>array('name'=>$this->cfg['email_from'], 'address'=>$this->cfg['email_address']),
														'replyto'=>array('name'=>$this->cfg['email_from'], 'address'=>$this->cfg['email_address'])
													   );
			
			$mailheaders = $this->getMailHeaders($mailheaders_options)
									.$this->mail_content_type_format_charset_usernotification."\r\n"
									;
			
			$mail_body = '';
			$mail_body .= $this->cfg['usernotification_message'];
			
			if($this->cfg['usernotification_insertformdata'])
			{
				$mail_body .= $this->mail_line_break_usernotification."--------------------------------------------------------";
				
				foreach($this->merge_post as $form_data)
				{
					$mail_body .= $this->mail_line_break_usernotification.$this->mail_line_break_usernotification.$form_data['elementlabel_value'].': '.$form_data['element_value'];
				}
			}
			
			if(preg_match('#html#', $this->mail_content_type_format_charset_usernotification))
			{
				$mail_body = nl2br($mail_body);
			}

			@mail($value['email_address'], $this->cfg['usernotification_subject'], $mail_body, $mailheaders);
		}
	}
	
	function mergePost($value)
	{
		$this->merge_post[$this->merge_post_index]['element_id'] = $value['element_id'];
		$this->merge_post[$this->merge_post_index]['element_value'] = $this->quote_smart(trim($value['element_value']));
		$this->merge_post[$this->merge_post_index]['elementlabel_value'] = $this->quote_smart(trim($value['elementlabel_value']));
		$this->merge_post[$this->merge_post_index]['elementlabel_id'] = $this->quote_smart(trim($value['elementlabel_id']));
		
		if(isset($value['element_type']) && $value['element_type'])
		{	// if element_type == upload, we add the download link in the mail body message
			$this->merge_post[$this->merge_post_index]['element_type'] = trim($value['element_type']);
		}
		
		if(isset($value['filename']) && $value['filename'])
		{
			$this->merge_post[$this->merge_post_index]['filename'] = $this->quote_smart(trim($value['filename']));
		}
		
		if(isset($value['deletefile']) && $value['deletefile'])
		{
			$this->merge_post[$this->merge_post_index]['deletefile'] = trim($value['deletefile']);
		}
		
		$this->merge_post_index++;
	}
	

	function isEmail($email)
	{
		$atom   = '[-a-z0-9\\_]';   // authorized caracters before @
		$domain = '([a-z0-9]([-a-z0-9]*[a-z0-9]+)?)'; // authorized caracters after @
									   
		$regex = '/^' . $atom . '+' .   
		'(\.' . $atom . '+)*' .         
										
		'@' .                           
		'(' . $domain . '{1,63}\.)+' .  
										
		$domain . '{2,63}$/i';          
		
		// test de l'adresse e-mail
		return preg_match($regex, trim($email)) ? 1 : 0;
		
	}
	
	
	function quote_smart($value)
	{
		if(get_magic_quotes_gpc())
		{
			$value = stripslashes($value);
		}
		
		return $value;
	}
	
	
	
	function getMailHeaders($mailheaders_options)
	{
		$mailheaders_options['from']['name'] = isset($mailheaders_options['from']['name'])?$mailheaders_options['from']['name']:$mailheaders_options['from']['address'];
		
		$mailheaders_options['cc']['address'] = isset($mailheaders_options['cc']['address'])?$mailheaders_options['cc']['address']:'';
		
		$mailheaders_options['bcc']['address'] = isset($mailheaders_options['bcc']['address'])?$mailheaders_options['bcc']['address']:'';


		$from_name = $mailheaders_options['from']['name']?$mailheaders_options['from']['name']:$mailheaders_options['from']['address'];
		
		
		if($this->isEmail($from_name))
		{
			// 	From: user@domain.com <user@domain.com> is invalid => user@domain.com
			$mail_header_from = 'From: '.$from_name."\r\n";
			$mail_header_replyto = 'Reply-To: '.$from_name."\r\n";
		} else
		{
			$mail_header_from = 'From: '.$from_name.'<'.$mailheaders_options['from']['address'].'>'."\r\n";
			$mail_header_replyto = 'Reply-To: '.$from_name.'<'.$mailheaders_options['from']['address'].'>'."\r\n";
		}
		
		
		$mail_header_cc = '';
		if($mailheaders_options['cc']['address'])
		{
			
			$explode_email = explode(',', $mailheaders_options['cc']['address']);
			
			$cc = '';

			foreach($explode_email as $email_value)
			{
				$cc .= $email_value.",";
			}
			
			$mail_header_cc .= 'Cc: '.substr($cc, 0, -1)."\r\n";
		}
		
		$mail_header_bcc = '';
		if($mailheaders_options['bcc']['address'])
		{
			$explode_email = explode(',', $mailheaders_options['bcc']['address']);
			
			$bcc = '';

			foreach($explode_email as $email_value)
			{
				$bcc .= $email_value.",";
			}
			
			$mail_header_bcc .= 'Bcc: '.substr($bcc, 0, -1)."\r\n";

		}
		
		$mailheaders = 	$mail_header_from
								.$mail_header_cc
								.$mail_header_bcc
								.$mail_header_replyto
								.'MIME-Version: 1.0'."\r\n"
								.'X-Mailer: PHP/'.phpversion()."\r\n"
								;
		/*
		Examples of headers that should work would be:
			From: user@domain.com will work
			From: "user" <user@domain.com>
		
		Examples of headers that will NOT work:
			From: "user@domain.com"
			From: user @ domain.com
			From: user@domain.com <user@domain.com>								
		*/
		
		// 	echo $mailheaders;
		return($mailheaders);
		
	}

	
}

/**
 * NO SPACES AFTER THIS LINE TO PREVENT
 * Warning: Cannot modify header information
 */
?>