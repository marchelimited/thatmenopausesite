<?php

//Info: Script to Create Slack Accounts

// <config>

        date_default_timezone_set('America/New_York');

        mb_internal_encoding("UTF-8");



        $typeformApiKey='da64545ac49009b1422f93ffbe68f84f84a4888d';

        $typeformFormId='L5BCdQ';

        $typeformEmailField='email_8989686';

        $typeformNameField='textfield_8989684';

        $previouslyInvitedEmailsFile=__DIR__.'/previouslyInvitedEmails.json';



        // your slack team/host name 

        $slackHostName='thatmenopausesite';



        // find this when checking the post at https://<slack name>.slack.com/admin/invites/full

        // NO SPACES between channel values where the commas are below

       $slackAutoJoinChannels='C07TW5E4C,C07TUK80Z,C07TUCHLJ,C07TW5E4C,C07TUCHLJ';

    
        // generate token at https://api.slack.com/

        $slackAuthToken='xoxp-7946289248-7946289312-7956606163-f52ca8';

// </config>





// <get typeform emails>

        if(@!file_get_contents($previouslyInvitedEmailsFile)) {

                $previouslyInvitedEmails=array();

        }

        else {

                $previouslyInvitedEmails=json_decode(file_get_contents($previouslyInvitedEmailsFile),true);

        }

        $offset=count($previouslyInvitedEmails);



        $typeformApiUrl='https://api.typeform.com/v0/form/'.$typeformFormId.'?key='.$typeformApiKey.'&completed=true&offset='.$offset;



        if(!$typeformApiResponse=file_get_contents($typeformApiUrl)) {

                echo "Sorry, can't access API";

                exit;

        }



        $typeformData=json_decode($typeformApiResponse,true);



        $usersToInvite=array();

        foreach($typeformData['responses'] as $response) {

                $user['email']=$response['answers'][$typeformEmailField];

                $user['name']=$response['answers'][$typeformNameField];

                if(!in_array($user['email'],$previouslyInvitedEmails)) {

                        array_push($usersToInvite,$user);

                }

        }

// </get typeform emails>







// <invite to slack>

        $slackInviteUrl='https://'.$slackHostName.'.slack.com/api/users.admin.invite?t='.time();



        $i=1;

        foreach($usersToInvite as $user) {

                echo date('c').' - '.$i.' - '."\"".$user['name']."\" <".$user['email']."> - Inviting to ".$slackHostName." Slack\n";



                // <invite>

                        $fields = array(

                                'email' => urlencode($user['email']),

                                'channels' => urlencode($slackAutoJoinChannels),

                                'first_name' => urlencode($user['name']),

                                'token' => $slackAuthToken,

                                'set_active' => urlencode('true'),

                                '_attempts' => '1'

                        );



                        // url-ify the data for the POST

                                $fields_string='';

                                foreach($fields as $key=>$value) { $fields_string .= $key.'='.$value.'&'; }

                                rtrim($fields_string, '&');



                        // open connection

                                $ch = curl_init();



                        // set the url, number of POST vars, POST data

                                curl_setopt($ch,CURLOPT_URL, $slackInviteUrl);

                                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                                curl_setopt($ch,CURLOPT_POST, count($fields));

                                curl_setopt($ch,CURLOPT_POSTFIELDS, $fields_string);



                        // exec

                                $replyRaw = curl_exec($ch);

                                $reply=json_decode($replyRaw,true);

                                if($reply['ok']==false) {

                                        echo date('c').' - '.$i.' - '."\"".$user['name']."\" <".$user['email']."> - ".'Error: '.$reply['error']."\n";

                                }

                                else {

                                        echo date('c').' - '.$i.' - '."\"".$user['name']."\" <".$user['email']."> - ".'Invited successfully'."\n";

                                }



                        // close connection

                                curl_close($ch);



                                array_push($previouslyInvitedEmails,$user['email']);

                                file_put_contents($previouslyInvitedEmailsFile,json_encode($previouslyInvitedEmails));

                // </invite>

                $i++;

        }

// </invite to slack>
