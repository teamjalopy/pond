<?php

namespace Pond;

use Aws\Ses\SesClient;

require 'vendor/autoload.php';

Class Email {

    function __construct(){
        $client = SesClient::factory(array(
            //keys removed for security reasons
            'key' => '',
            'secret' => '',
            'region' => 'us-west-2'
        ));
    }

    function sendEmail(){

        $emailSentId = $client->sendEmail(array(
            // Source is required
            'Source' => 'pondedu.me',
            // Destination is required
            'Destination' => array(
                'ToAddresses' => array('typhon996@gmail.com')
            ),
            // Message is required
            'Message' => array(
                // Subject is required
                'Subject' => array(
                    // Data is required
                    'Data' => 'SES Testing',
                    'Charset' => 'UTF-8',
                ),
                // Body is required
                'Body' => array(
                    'Text' => array(
                        // Data is required
                        'Data' => 'My plain text email',
                        'Charset' => 'UTF-8',
                    ),
                    'Html' => array(
                        // Data is required
                        'Data' => '<b>My HTML Email</b>',
                        'Charset' => 'UTF-8',
                    ),
                ),
            ),
            'ReplyToAddresses' => array( 'pondedu.me' ),
            'ReturnPath' => 'pondedu.me'
        ));
    }
}
