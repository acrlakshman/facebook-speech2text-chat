<?php

/**
 * This sample app is provided to kickstart your experience using Facebook's
 * resources for developers.  This sample app provides examples of several
 * key concepts, including authentication, the Graph API, and FQL (Facebook
 * Query Language). Please visit the docs at 'developers.facebook.com/docs'
 * to learn more about the resources available to you
 */

// Provides access to app specific values such as your app id and app secret.
// Defined in 'AppInfo.php'
require_once('AppInfo.php');

// Enforce https on production
if (substr(AppInfo::getUrl(), 0, 8) != 'https://' && $_SERVER['REMOTE_ADDR'] != '127.0.0.1') {
  header('Location: https://'. $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
  exit();
}

// This provides access to helper functions defined in 'utils.php'
require_once('utils.php');


/*****************************************************************************
 *
 * The content below provides examples of how to fetch Facebook data using the
 * Graph API and FQL.  It uses the helper functions defined in 'utils.php' to
 * do so.  You should change this section so that it prepares all of the
 * information that you want to display to the user.
 *
 ****************************************************************************/

require_once('sdk/src/facebook.php');

$facebook = new Facebook(array(
  'appId'  => AppInfo::appID(),
  'secret' => AppInfo::appSecret(),
  'sharedSession' => true,
  'trustForwarded' => true,
));

$user_id = $facebook->getUser();

if ($user_id) {
  try {
    // Fetch the viewer's basic information
    $basic = $facebook->api('/me');
  } catch (FacebookApiException $e) {
    // If the call fails we check if we still have a user. The user will be
    // cleared if the error is because of an invalid accesstoken
    if (!$facebook->getUser()) {
      header('Location: '. AppInfo::getUrl($_SERVER['REQUEST_URI']));
      exit();
    }
  }

  // This fetches some things that you like . 'limit=*" only returns * values.
  // To see the format of the data you are retrieving, use the "Graph API
  // Explorer" which is at https://developers.facebook.com/tools/explorer/
  $likes = idx($facebook->api('/me/likes?limit=4'), 'data', array());

  // This fetches 4 of your friends.
  $friends = idx($facebook->api('/me/friends?limit=4'), 'data', array());

  // And this returns 16 of your photos.
  $photos = idx($facebook->api('/me/photos?limit=16'), 'data', array());

  // Here is an example of a FQL call that fetches all of your friends that are
  // using this app
  $app_using_friends = $facebook->api(array(
    'method' => 'fql.query',
    'query' => 'SELECT uid, name FROM user WHERE uid IN(SELECT uid2 FROM friend WHERE uid1 = me()) AND is_app_user = 1'
  ));
}

// Fetch the basic info of the app that they are using
$app_info = $facebook->api('/'. AppInfo::appID());

$app_name = idx($app_info, 'name', '');

?>

<!DOCTYPE html>
<html xmlns:fb="http://ogp.me/ns/fb#" lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=2.0, user-scalable=yes" />

    <title><?php echo he($app_name); ?></title>
    <link rel="stylesheet" href="stylesheets/screen.css" media="Screen" type="text/css" />
    <link rel="stylesheet" href="stylesheets/mobile.css" media="handheld, only screen and (max-width: 480px), only screen and (max-device-width: 480px)" type="text/css" />

    <!--[if IEMobile]>
    <link rel="stylesheet" href="mobile.css" media="screen" type="text/css"  />
    <![endif]-->

    <!-- These are Open Graph tags.  They add meta data to your  -->
    <!-- site that facebook uses when your content is shared     -->
    <!-- over facebook.  You should fill these tags in with      -->
    <!-- your data.  To learn more about Open Graph, visit       -->
    <!-- 'https://developers.facebook.com/docs/opengraph/'       -->
    <meta property="og:title" content="<?php echo he($app_name); ?>" />
    <meta property="og:type" content="website" />
    <meta property="og:url" content="<?php echo AppInfo::getUrl(); ?>" />
    <meta property="og:image" content="<?php echo AppInfo::getUrl('/logo.png'); ?>" />
    <meta property="og:site_name" content="<?php echo he($app_name); ?>" />
    <meta property="og:description" content="My first app" />
    <meta property="fb:app_id" content="<?php echo AppInfo::appID(); ?>" />

    <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
    <script type="text/javascript" src="./javascript/jquery-1.7.1.min.js"></script>
    <script src="./scripts/strophe.js" type="text/javascript"></script>
    <script type="text/javascript" src="facebook.js"></script>
    <script type="text/javascript" src="http://www.google.com/intl/en/chrome/assets/common/js/chrome.min.js"></script>

    <link rel="stylesheet" href="./stylesheets/ui-lightness/jquery-ui-1.10.3.custom.css" type="text/css" media="screen" />
    <script type="text/javascript" src="./scripts/jquery-ui-1.10.3.custom.js"></script>
    <link type="text/css" href="./stylesheets/jquery.ui.chatbox.css" rel="stylesheet" />
    <script type="text/javascript" src="./scripts/jquery.ui.chatbox.js"></script>
    <!--<script type="text/javascript" src="./scripts/chatboxManager.js"></script>-->

    <script type="text/javascript">
      function logResponse(response) {
        if (console && console.log) {
          console.log('The response was', response);
        }
      }

      $(function(){
        // Set up so we handle click on the buttons
        $('#postToWall').click(function() {
          FB.ui(
            {
              method : 'feed',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendToFriends').click(function() {
          FB.ui(
            {
              method : 'send',
              link   : $(this).attr('data-url')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });

        $('#sendRequest').click(function() {
          FB.ui(
            {
              method  : 'apprequests',
              message : $(this).attr('data-message')
            },
            function (response) {
              // If response is null the user canceled the dialog
              if (response != null) {
                logResponse(response);
              }
            }
          );
        });
      });
    </script>

    <!--[if IE]>
      <script type="text/javascript">
        var tags = ['header', 'section'];
        while(tags.length)
          document.createElement(tags.pop());
      </script>
    <![endif]-->
  </head>
  <body>
    <div id="fb-root"></div>
    <script type="text/javascript">

      // chat functions
      var BOSH_SERVICE = 'http://bosh.metajack.im:80/xmpp-httpbind';
      var connection = null;

      function log (msg) 
      {
        $('#log').append('<div></div>').append(document.createTextNode(msg));
	//$('#log').append('<div></div>').text(document.createTextNode(msg));
      };

      function reclog (msg) {
        $('#reclog').append('<div></div>').append(document.createTextNode(msg));
      };

      function chat_log (msg) {
        $('#chat_log').append('<div></div>').append(document.createTextNode(msg));
      };

      function chat_alert (msg) {
        $('#chat_alert').append('<div></div>').append(document.createTextNode(msg));
      };
      
      // append 'msg' to a div with id 'div_id'
      function append_text (div_id, msg) {
        $('#'+div_id).append('<div></div>').append(document.createTextNode(msg));
      }

      // hide all chat boxes
      function hide_all_chat_boxes (chat_lists) {
        for (var i = 0; i < chat_lists.length; ++i) {
	  $('#'+chat_lists[i]).hide();
	}
      }

      function rawInput (data) {
        //log('RECV: ' + data);
	for (var i = 0; i < $(data).length; ++i) {
	  if (Strophe.isTagEqual($(data)[i], 'presence')) {
	    read_presence($(data)[i]);
	  }
	}
      };

      function rawOutput(data) {
        //log('SENT: ' + data);
      };

      // read presence
      function read_presence(data) {
        if (data.getAttribute("type") === 'unavailable') {
	  remove_from_chatlist(get_presence_userid(data));
	}
	else if (data.getAttribute("type") == null) {
	  add_to_chatlist(get_presence_userid(data));
	}
	/*
	if (data.getElementsByTagName("x").length > 0) {
	  if (data.getElementsByTagName("x").item(0).getAttribute("xmlns") === 'vcard-temp:x:update') {
	    console.log(get_presence_userid(data) + ' is online');
	  }
	}
	*/
      };

      // get userid from input-jid
      function get_presence_userid (data) {
        return remove_first_char(explode('@', data.getAttribute("from"), 2)[0]);
      };

      function xmlInput(data) {
      };

      function onConnect(status) {
        if (status == Strophe.Status.CONNECTING) {
	  log('Connecting...');
        } else if (status == Strophe.Status.CONNFAIL) {
	    log('Connection failed...');
	    $('#connect').get(0).value = 'connect';
        } else if (status == Strophe.Status.DISCONNECTING) {
 	    log('Disconnecting...');
        } else if (status == Strophe.Status.DISCONNECTED) {
	    log('Disconnected...');
	    $('#connect').get(0).value = 'connect';
	    $("#to").empty();
	    $('#log').html('');
	    $('#reclog').html('');
	    $('div.chat_data').hide();
	    hide_all_chat_boxes(chat_box_idlist);
        } else if (status == Strophe.Status.CONNECTED) {
	    log('You are online...');
            console.log('Connected');
	    //connection.disconnect();

	    //log('Send a message to ' + connection.jid + ' to talk to me.');

	    connection.addHandler(onMessage, null, 'message', null, null,  null);
	    connection.send($pres().tree());

	    /*
	    FB.api('/me/friends', function(response) {
		var to = $("#to");
		to.empty();
		$.each(response.data, function(i,v){
			to.append($("<option value='" + v.id + "'>" + v.name + "</option>"));
		});
	    });
	    */

	    // show login div
	    $('div.chat_data').show();

        }
      };

      // check if chat userid already exists
      function check_chatuser_exists (userid) {
        for (var i = 0; i < document.getElementById('to').options.length; ++i) {
	  if (document.getElementById('to').options[i].value === userid) {
	    return true;
	  }
	}
	return false;
      };

      // get chat list user full name via ChatFriendsListArray
      function get_chat_user_name (userid) {
        if (ChatFriendsListArray.indexOf(userid) !== -1) {
          return ChatFriendsListArray_uname[ ChatFriendsListArray.indexOf(userid) ];
	}
      };

      // get chat list user first name via ChatFriendsListArray
      function get_chat_first_name (userid) {
        if (ChatFriendsListArray.indexOf(userid) !== -1) {
	  return ChatFriendsListArray_fname[ ChatFriendsListArray.indexOf(userid) ];
	}
      };

      // add user to chatlist
      function add_to_chatlist (userid) {
        FB.api('/' + userid, function (response) {
	  if (ChatFriendsListArray.indexOf(userid) == -1) {
	    ChatFriendsListArray.push(userid);
	    ChatFriendsListArray_uname.push(response.name);
	    ChatFriendsListArray_fname.push(response.first_name);
	  }

	  if (!check_chatuser_exists(userid)) {
	    ChatFriendPos = ChatFriendsListArray.indexOf(userid);
	    var selectid = 'friend' + ChatFriendPos;
	    $("#to").append($("<option value='" + userid + "'>" +
	    		       response.name + " (friend " +
			       ChatFriendPos + ")" +
			       "</option>"));
	    /*
	    $("#to").append($("<option value='" + userid +
	    		       "' id='" + selectid + "'>" +
	    		       response.name + " (friend " +
			       ChatFriendPos + ")" +
			       "</option>"));
	    */
	  }
	});
      };

      // remove user from chatlist
      function remove_from_chatlist (userid) {
        FB.api('/' + userid, function (response) {
	  $("#to option[value='"+userid+"']").remove();
	});
      };

      /*
        Connecting to facebook chat server:
	sources:
	1) "https://github.com/javierfigueroa/turedsocial"
	2) Book: "Wrox Press Professional XMPP Programming with JavaScript and jQuery"
      */
      var access_token;
      function chat_login() {
	  var button = $('#connect').get(0);
	  if (button.value == 'connect') {
	      button.value = 'disconnect';

	      //alert('jid: ' + $('#jid').get(0).value);
	      //alert('access_token after jid: ' + access_token);

	      var jabberID = "<?php echo $basic['id']; ?>" + "@chat.facebook.com";
	      //$('#jid').get(0).value,
	      connection.facebookConnect(jabberID,
					onConnect,
					60,
					1,
					'<?php echo AppInfo::appID(); ?>' , //app id
					'<?php echo AppInfo::appSecret(); ?>',//secret key
					access_token);
	  } else {
	      button.value = 'connect';
	      connection.disconnect();
	  }
      }; // end chat_login

      // send chat message
      function sendMessage() {
	var message = $('#message').get(0).value;
	var to = '-' + $('#to').get(0).value + "@chat.facebook.com";
		
	if(message && to){
		var reply = $msg({
		to: to,
		type: 'chat'
	    })
	    .cnode(Strophe.xmlElement('body', message));
	    connection.send(reply.tree());

	    //log('I sent ' + to + ': ' + message);
	}
	document.getElementById('message').value = "";
	$('#message').focus();
      };

      // receive chat message
      function onMessage(msg) {
	var to = msg.getAttribute('to');
	var from = msg.getAttribute('from');
	var type = msg.getAttribute('type');
	var elems = msg.getElementsByTagName('body');

	if (type == "chat" && elems.length > 0) {
	  var body = elems[0];

	  //log('I got a message from ' + from + ': ' + Strophe.getText(body));
	  //reclog(Strophe.getText(body));
	  var chat_recv = get_chat_first_name(get_presence_userid(msg)) + ": " + Strophe.getText(body);
	  //alert(chat_recv);
	  // //chat_log(chat_recv);
	  //chat_log(Strophe.getText(body));
	  var chat_uid = get_presence_userid(msg);
	  // create chat area for this user if doesn't exist
	  if (chat_box_idlist.indexOf(chat_uid) === -1) {

	    chat_box_idlist.push(chat_uid);
	    //chat_box_idlist_active.push(chat_uid);
	    //chat_box = false; // redundant variable

	    // create chat area, since it didn't exist till now
	    $("<div id=" + chat_uid + "></div>").appendTo(wallspace_chatbox_id);
	    $('#'+chat_uid).css("max-height", "200px");
	    $('#'+chat_uid).css("overflow", "scroll");

	    var chat_user_tmp_name;
	    
	    FB.api('/' + chat_uid, function (response) {

	      char_recv = response.name + ": " + Strophe.getText(body);
	      append_text(chat_uid, "Chat with: " + response.name);
	      append_text(chat_uid, chat_recv);

	    });
	    
	  } else {
	    append_text(chat_uid, chat_recv);
	  }

	}

	// we must return true to keep the handler alive.
	// returning false would remove it after it finishes.
	return true;
      };
      // End chat functions

      var IsConnected = false;
      var show_wallspace = true;
      var show_additionalspace = true;
      var ChatFriendPos = 0;
      var ChatFriendsListArray = new Array();
      var ChatFriendsListArray_uname = new Array();
      var ChatFriendsListArray_fname = new Array();

      // variables to store active chat details
      var chat_box = true; // if 'true' chat area exists
      var chat_box_idlist = new Array();
      var chat_box_idlist_active = new Array();

      function fblogout() {
        FB.logout( function(response) {
	});
      };

      window.fbAsyncInit = function() {
        FB.init({
          appId      : '<?php echo AppInfo::appID(); ?>', // App ID
          channelUrl : '//<?php echo $_SERVER["HTTP_HOST"]; ?>/channel.html', // Channel File
          status     : true, // check login status
          cookie     : true, // enable cookies to allow the server to access the session
          xfbml      : true // parse XFBML
        });

        // Listen to the auth.login which will be called when the user logs in
        // using the Login button
	<?php if (isset($basic)) { ?>
 	FB.Event.subscribe('auth.authResponseChange', function(response) {
	  console.log(response);
	  console.log(response.authResponse.accessToken);
    	  if (response.status === 'connected') {
	    access_token = response.authResponse.accessToken;
	    IsConnected = true;
    	  } else if (response.status === 'not_authorized') {
      	    FB.login();
    	  } else {
      	    FB.login();
    	  }

  	});
	<?php } ?>
	
        FB.Event.subscribe('auth.login', function(response) {
          window.location = window.location;
	  if (response.status === 'connected') {
	    access_token = response.authResponse.accessToken;
	    IsConnected = true;
	  }
    	  else if (response.status === 'not_authorized') {
	    FB.login();
	  }
	  else {
	    FB.login();
	  }
        });

	connection = new Strophe.Connection(BOSH_SERVICE);
	// Uncomment these lines to see xml communication
    	connection.rawInput = rawInput;
    	connection.rawOutput = rawOutput;
	connection.xmlInput = xmlInput;

      };

      $(document).ready( function() {
        $('div.chat_data').hide();

	//var chat_box = true; // if 'true' chat area exists
	//var chat_box_idlist = new Array();
	//var chat_box_idlist_active = new Array();

	// If a friend is selected in chat list
	$('#to').change( function() {
	  if ($(this).attr('value') !== 'no_select') {
	    //alert('Value changed to: ' + $(this).attr('value'));
	    //alert('chat_box: ' + chat_box);

	    var chat_uid = $(this).attr('value');
	    // Add id to chat_box_idlist
	    if (chat_box_idlist.indexOf(chat_uid) === -1) {

	      chat_box_idlist.push(chat_uid);
	      chat_box_idlist_active.push(chat_uid);
	      chat_box = false; // redundant variable

	      // create chat area, since it didn't exist tillnow
	      $("<div id=" + chat_uid + "></div>").appendTo(wallspace_chatbox_id);
	      $('#'+chat_uid).css("max-height", "200px");
	      $('#'+chat_uid).css("overflow", "scroll");
	      //$('#'+chat_uid).append("Chat with: " + get_chat_first_name(chat_uid));
	      FB.api('/'+chat_uid, function(response) {
	        $('#'+chat_uid).append("Chat with: " + response.name);
	      });

	    }

	    //alert('chat_uname: ' + get_chat_user_name(chat_uid));

	  }
	});

	// send chat message by return key
	$('#message').keydown(function(event) {
	  if (event.which == 13) {
	    event.preventDefault();
	    // //chat_log($('#message').get(0).value);
	    var chat_sending = "Me: " + $('#message').get(0).value;
	    append_text($('#to').attr('value'), chat_sending);
	    sendMessage();
	  }
	});

	// toggle show/hide of news feed
	$('.wall-i').bind('click', function () {
	  if (show_wallspace) {
	    show_wallspace = false;
	    $('div.wall-i').html('<img src="./images/me/icon-w.png">');
	    //$('div.WallSpace').hide();
	    $('div.user_home').hide();
	  } else {
	    show_wallspace = true;
	    $('div.wall-i').html('<img src="./images/me/icon-w-glow.png">');
	    //$('div.WallSpace').show();
	    $('div.user_home').show();
	  }
	});

	// toggle show/hide of extreme right workspace
	$('.chat-i').bind('click', function () {
	  if (show_additionalspace) {
	    show_additionalspace = false;
	    $('div.chat-i').html('<img src="./images/me/icon-chat.png">');
	    $('.AdditionalSpace').hide();
	  } else {
	    show_additionalspace = true;
	    $('div.chat-i').html('<img src="./images/me/icon-chat-glow.png">');
	    $('.AdditionalSpace').show();
	  }
	});

      });

      // Load the SDK Asynchronously
      (function(d, s, id) {
        var js, fjs = d.getElementsByTagName(s)[0];
        if (d.getElementById(id)) return;
        js = d.createElement(s); js.id = id;
        js.src = "//connect.facebook.net/en_US/all.js";
        fjs.parentNode.insertBefore(js, fjs);
      }(document, 'script', 'facebook-jssdk'));
    </script>

    <!-- Add here -->

    <?php if (isset($basic)) { ?>
      <div class="DockSpace">
        <!--<img src="./images/me/dock-app.png">-->
        <div class="wall-i" id="wall-icon">
          <img src="./images/me/icon-w-glow.png">
        </div>
        <div class="chat-i" id="chat-icon">
          <img src="./images/me/icon-chat-glow.png">
        </div>
      </div>

      <div class="WallSpace" id="wallspace-id">
	<p>
	  <fb:login-button show-faces="true" width="200" max-rows="1"></fb:login-button>
	</p>
	<p>
	  <button type="button" onclick="fblogout()">Logout</button>
	</p>
        <p> Hi <?php echo he(idx($basic, 'name')); ?> </p>

	<a href="#" class="facebook-button speech-bubble" id="sendToFriends" data-url="<?php echo AppInfo::getUrl(); ?>">
          <span class="speech-bubble">Send Message</span>
        </a>

	<!-- Failed to write chatboxes code, so taking this route -->
	<!-- Start chat box space -->

	<div class="wallspace_chatbox" id="wallspace_chatbox_id" style="background-color: #66FFCC">

	</div>

	<!-- End chat box space -->

	<div class="user_home" id="user_home_id" style="background-color: #b0c4de">
	  <?php
	    $ret_home = $facebook->api('/me/home', 'GET');
	  ?>
	  <?php
	    //echo("<p>".$ret_home['data'][0]['id']."</p>");
	    foreach ($ret_home['data'] as $item) {
	      //echo("<p>" . $item['id'] . "</p>");
	      if ($item['status_type'] === "added_photos") {
	        $friend_basic = $facebook->api('/'.$item['from']['id'], 'GET');
	        echo("<p><a href=" . $friend_basic['link'] . ">" . $item['from']['name'] . "</a>: added a photo</p>");
	        echo("<p><img src=" . $item['picture'] . "></p>");
		//echo $item['picture'];
	      }
	      if ($item['type'] === "status") {
	        $friend_basic = $facebook->api('/'.$item['from']['id'], 'GET');
		//var msg_status = $item['message'];
		if ( strlen($item['message']) > 0) {
	          echo("<p><a href=" . $friend_basic['link'] . ">" . $item['from']['name'] . "</a>: says</p>");
	          echo("<p>" . $item['message'] . "</p>");
		}
	      }
	    }
	  ?>

	</div>

      </div>

      <div class="AdditionalSpace">
	<!-- Speech -->
	<div id="info">
          <p id="info_start">
            Click on the microphone icon and begin speaking for as long as you like.
          </p>
          <p id="info_speak_now" style="display:none">
            Speak now.
          </p>
          <p id="info_no_speech" style="display:none">
            No speech was detected. You may need to adjust your <a href=
            "//support.google.com/chrome/bin/answer.py?hl=en&amp;answer=1407892">microphone
            settings</a>.
          </p>
          <p id="info_no_microphone" style="display:none">
            No microphone was found. Ensure that a microphone is installed and that
            <a href="//support.google.com/chrome/bin/answer.py?hl=en&amp;answer=1407892">
            microphone settings</a> are configured correctly.
          </p>
          <p id="info_allow" style="display:none">
            Click the "Allow" button above to enable your microphone.
          </p>
          <p id="info_denied" style="display:none">
            Permission to use microphone was denied.
          </p>
          <p id="info_blocked" style="display:none">
            Permission to use microphone is blocked. To change, go to
            chrome://settings/contentExceptions#media-stream
          </p>
          <p id="info_upgrade" style="display:none">
            Web Speech API is not supported by this browser. Upgrade to <a href=
            "//www.google.com/chrome">Chrome</a> version 25 or later.
          </p>
        </div>
	<div id="div_start">
          <button id="start_button" onclick="startButton(event)"><img alt="Start" id="start_img"
          src="https://www.google.com/intl/en/chrome/assets/common/images/content/mic.gif"></button>
        </div>
        <div id="results">
	  <!--<textarea id="final_span" class="final"></textarea>-->
          <span class="final" id="final_span"></span>
	  <span class="interim" id="interim_span"></span> <br />
        </div>
	<div id="slate" class="slate">
	  <!-- Display process -->
	</div>

	<!-- Chat space -->

	<div id="ChatSpace">
	  <div id='login' style='text-align: left'>
	    Chat server: <input type='button' id='connect' value='connect' onclick='chat_login()'>
	    <!--
            <form name='cred'>
              <label for='jid'>JID:</label>
              <input type='text' id='jid'>
              <label for='pass'>Password:</label>
              <input type='password' id='pass'>
              <input type='button' id='connect' value='connect' onclick='chat_login()'>
            </form>
	    -->
          </div>

          <div id='login' style='text-align: left' class='chat_data'>
	    <hr>
	    <label for='to'>
	       to:
	    </label>
	    <select id='to' multiple size=10>
	      <option value='no_select' selected>--online friends--</option>
	    </select>
	    <div id='to_div' class='to_div'></div>
	    <br />
	    <label for='message'>
		message:
	    </label>
	    <br />
	    <textarea id='message' style='height: 60px'></textarea>
	    <br />
	    <div class='chat_alert' id='chat_alert'></div>
	    <br />
	    <div class='chat_log' id='chat_log'></div>
	    <div class='chat_div' id='chat_div'></div>
          </div>

          <div id='reclog'></div>
          <div id='log'></div>
	</div>

	<!-- End Chat space -->

	<script src="//www.google.com/intl/en/chrome/assets/common/js/chrome.min.js"></script>
	<script>
	// speech
	/*
	  source of "http://www.google.com/intl/en/chrome/demos/speech.html" is
	  the basis for this speech2text code
	*/
	var Trigger = 'lucky';
	var slate_req = '';
	var send_div_elements = '';
	var create_email = false;
	var final_transcript = '';
	var recognizing = false;
	var ignore_onend;
	var start_timestamp;
	if (!('webkitSpeechRecognition' in window)) {
	  upgrade();
	} else {
  	  start_button.style.display = 'inline-block';
  	  var recognition = new webkitSpeechRecognition();
  	  recognition.continuous = true;
  	  recognition.interimResults = true;

  	  recognition.onstart = function() {
    	    recognizing = true;
    	    showInfo('info_speak_now');
    	    start_img.src = '//www.google.com/intl/en/chrome/assets/common/images/content/mic-animate.gif';
  	  };

  	  recognition.onerror = function(event) {
    	    if (event.error == 'no-speech') {
      	      start_img.src = '//www.google.com/intl/en/chrome/assets/common/images/content/mic.gif';
      	      showInfo('info_no_speech');
      	      ignore_onend = true;
    	    }
    	    if (event.error == 'audio-capture') {
      	      start_img.src = '//www.google.com/intl/en/chrome/assets/common/images/content/mic.gif';
      	      showInfo('info_no_microphone');
      	      ignore_onend = true;
    	    }
    	    if (event.error == 'not-allowed') {
      	      if (event.timeStamp - start_timestamp < 100) {
                showInfo('info_blocked');
      	      } else {
                showInfo('info_denied');
      	      }
      	      ignore_onend = true;
    	    }
  	  };

  	  recognition.onend = function() {
    	    recognizing = false;
    	    if (ignore_onend) {
      	      return;
    	    }
    	    start_img.src = 'https://www.google.com/intl/en/chrome/assets/common/images/content/mic.gif';
    	    if (!final_transcript) {
      	      showInfo('info_start');
      	      return;
    	    }
    	    showInfo('');
    	    if (window.getSelection) {
      	      window.getSelection().removeAllRanges();
      	      var range = document.createRange();
      	      //range.selectNode(document.getElementById('final_span'));
      	      //window.getSelection().addRange(range);
    	    }
    	    if (create_email) {
      	      create_email = false;
      	      createEmail();
    	    }
  	  };

  	  recognition.onresult = function(event) {

    	    var interim_transcript = '';
    	    var prefinal_transcript = '';
    	    var req_received = 'false';
    	    if (typeof(event.results) == 'undefined') {
      	      recognition.onend = null;
      	      recognition.stop();
      	      upgrade();
      	      return;
    	    }
    
	    for (var i = event.resultIndex; i < event.results.length; ++i) {
      	      if (event.results[i].isFinal) {
                prefinal_transcript += event.results[i][0].transcript;
              } else {
                interim_transcript += event.results[i][0].transcript;
      	      }
    	    }

    	    // get first word
    	    var prefinal = get_word(prefinal_transcript, 0);
    	    if (prefinal[0] === Trigger) {
      	      final_transcript += remove_first_word(prefinal_transcript);
      	      prefinal_transcript = remove_first_word(prefinal_transcript);
      	      req_received = 'true';
    	    }
    	    else if (prefinal[0] === "" && prefinal[1] === Trigger) {
      	      final_transcript += remove_two_words(prefinal_transcript);
      	      prefinal_transcript = remove_two_words(prefinal_transcript);
      	      req_received = 'true';
      	      //alert('req_received: ' + prefinal_transcript);
    	    }
    	    else {
      	      final_transcript += prefinal_transcript;
    	    }

    	    final_transcript = capitalize(final_transcript);

    	    //var textcontent = document.getElementById('final_span').value;
    	    //final_span.innerHTML = linebreak(final_transcript);
    	    interim_span.innerHTML = linebreak(interim_transcript);

    	    // speech code
    	    if (req_received === 'true') {
      	      req_received = 'false';

      	      // Check the request
      	      var req = get_request_type(prefinal_transcript);
      	      //alert('slate_req: ' + slate_req + '; ' + 'req: ' + req);

      	      switch(req) {
              case 1: // send_message
	        if (!is_children_exists('div.slate > div','send_to_div')) {

	    	  $('div.slate').html('');

	    	  // append send message "to" attribute to 'slate' div
	    	  $('#slate').append("<div id='send_to_div'></div>");
	    	  // textarea inside 'send_to_div'
	    	  $('#send_to_div').append("<textarea id='send_to' autofocus></textarea>");

	    	  // append send message "compose" attribute to 'slate' div
	    	  $('#slate').append("<div id='send_compose_div'></div>");
	    	  // textarea inside 'send_compose_div'
	    	  $('#send_compose_div').append("<textarea id='send_compose'></textarea>");

	    	  // create 'send', 'cancel' buttons
	    	  $('#slate').append("<button type='button' id='send_send_button' onclick='send_message_send()'>Send</button>");
	    	  $('#slate').append("<button type='button' id='send_cancel_button' onclick='send_message_cancel()'>Cancel</button>");

	    	  slate_req = 'send_message_compose';
	    	  $('#send_compose').focus();
	  	}
	  	else {
	    	  interim_span.innerHTML = 'send space already exists';
	  	}
	  	break;

	      case 101: // send_to
	        // add text to 'send_to'
	  	slate_req = 'send_message_to';
	  	//alert(prefinal_transcript);
	  	$('#send_to').focus();
	  	break;

	      case 102: // send_compose
	        // add text to 'send_compose'
	  	slate_req = 'send_message_compose';
	  	//alert(prefinal_transcript);
	  	$('#send_compose').focus();
	  	break;

	      case 103: // End composing message
	        slate_req = 'send_message_to';
	  	//alert(prefinal_transcript);
	  	$('#send_to').focus();
	  	break;

	      case 2: // send_message onto timeline
	        if (!is_children_exists('div.slate > div','timeline_to_div')) {

	    	  $('div.slate').html('');

	    	  // append timeline message "to" attribute to 'slate' div
	    	  $('#slate').append("<div id='timeline_to_div'></div>");
	    	  // textarea inside 'timeline_to_div'
	    	  $('#timeline_to_div').append("<textarea id='timeline_to' autofocus></textarea>");

	    	  // append timeline message "compose" attribute to 'slate' div
	    	  $('#slate').append("<div id='timeline_compose_div'></div>");
	    	  // textarea inside 'timeline_compose_div'
	    	  $('#timeline_compose_div').append("<textarea id='timeline_compose'></textarea>");

	    	  // create 'send', 'cancel' buttons
	    	  $('#slate').append("<button type='button' id='timeline_send_button' onclick='timeline_message_send()'>Write</button>");
	    	  $('#slate').append("<button type='button' id='timeline_cancel_button' onclick='timeline_message_cancel()'>Cancel</button>");

	    	  slate_req = 'timeline_message_compose';
	    	  $('#timeline_compose').focus();
	  	}
	  	else {
	    	  interim_span.innerHTML = 'timeline space already exists';
	  	}
	  	break;

	      case 201: // timeline_to
	        // add text to 'timeline_to'
	  	slate_req = 'timeline_message_to';
	  	//alert(prefinal_transcript);
	  	$('#timeline_to').focus();
	  	break;

	      case 202: // timeline_compose
	        // add text to 'timeline_compose'
	  	slate_req = 'timeline_message_compose';
	  	//alert(prefinal_transcript);
	  	$('#timeline_compose').focus();
	  	break;

	      case 203: // End composing message
	        slate_req = 'timeline_message_to';
	  	//alert(prefinal_transcript);
	  	$('#timeline_to').focus();
	  	break;

	      case 3: // post_on_wall
	        if (!is_children_exists('div.slate > div','postonwall_to_div')) {

	    	  $('div.slate').html('');

	    	  // append postonwall message "to" attribute to 'slate' div
	    	  $('#slate').append("<div id='postonwall_to_div'></div>");
	    	  // textarea inside 'postonwall_to_div'
	    	  $('#postonwall_to_div').append("<textarea id='postonwall_to' autofocus></textarea>");

	    	  // append postonwall message "compose" attribute to 'slate' div
	    	  $('#slate').append("<div id='postonwall_compose_div'></div>");
	    	  // textarea inside 'postonwall_compose_div'
	    	  $('#postonwall_compose_div').append("<textarea id='postonwall_compose'></textarea>");

	    	  // create 'send', 'cancel' buttons
	    	  $('#slate').append("<button type='button' id='postonwall_send_button' onclick='postonwall_message_send()'>Write</button>");
	    	  $('#slate').append("<button type='button' id='postonwall_cancel_button' onclick='postonwall_message_cancel()'>Cancel</button>");

	    	  slate_req = 'postonwall_message_compose';
	    	  $('#postonwall_compose').focus();

	    	  // hide postonwall_to_div
	    	  $('#postonwall_to_div').hide();
	  	}
	  	else {
	    	  interim_span.innerHTML = 'timeline space already exists';
	  	}
	  	break;

	      case 301: // postonwall_to
	        // add text to 'postonwall_to'
	  	slate_req = 'postonwall_message_to';
	  	//alert(prefinal_transcript);
	  	$('#postonwall_to').focus();
	  	break;

	      case 302: // postonwall_compose
	        // add text to 'postonwall_compose'
	  	slate_req = 'postonwall_message_compose';
	  	//alert(prefinal_transcript);
	  	$('#postonwall_compose').focus();
	  	break;

	      case 303: // End composing message
	        postonwall_message_send();
	  	//slate_req = 'postonwall_message_to';
	  	//alert(prefinal_transcript);
	  	//$('#postonwall_to').focus();
	  	break;

	      case 304: // post composed message on wall
	        postonwall_message_send();
	  	break;

	      case 305: // cancel composed message for posting on wall
	        postonwall_message_cancel();
	  	break;

	      case 4: // start chat
	        if ($('#connect').get(0).value === 'connect') {
	    	  chat_login();
	  	} else {
	    	  interim_span.innerHTML = 'chatting in progress...';
	  	}
	  	break;

	      case 401: // close chat
	        if ($('#connect').get(0).value === 'disconnect') {
	    	  chat_login();
	  	} else {
	    	  interim_span.innerHTML = 'You are already offline...';
	  	}
	  	break;

	      default:

      	      }

    	    }
    	    else {
      	      // if send_message_req
      	      if (slate_req === 'send_message_req') {
                //$('#send_to').focus();
              }
      	      else if (slate_req === 'send_message_to') { // send_message_to
                $('#send_to').append(prefinal_transcript);
              }
      	      else if (slate_req === 'send_message_compose') { // send_message_compose
                $('#send_compose').append(prefinal_transcript);
      	      }
      	      else if (slate_req === 'timeline_message_to') { // timeline_message_to
                $('#timeline_to').append(prefinal_transcript);
              }
      	      else if (slate_req === 'timeline_message_compose') { // timeline_message_compose
                $('#timeline_compose').append(prefinal_transcript);
      	      }
      	      else if (slate_req === 'postonwall_message_to') { // postonwall_message_to
                $('#postonwall_to').append(prefinal_transcript);
              }
      	      else if (slate_req === 'postonwall_message_compose') { // postonwall_message_compose
                $('#postonwall_compose').append(prefinal_transcript);
              }
      	      else if ($('#connect').get(0).value === 'disconnect') { // start chating
                //alert('chat_on: ' + prefinal_transcript);
		document.getElementById('message').value = prefinal_transcript;
		append_text($('#to').attr('value'), prefinal_transcript);
		// //chat_log(prefinal_transcript);
		sendMessage();
      	      }
      	      else { // default
                //alert(prefinal_transcript);
              }
    	    }

    	    if (final_transcript || interim_transcript) {
      	      showButtons('inline-block');
    	    }
  	  };
	}

	function upgrade() {
  	  start_button.style.visibility = 'hidden';
  	  showInfo('info_upgrade');
	}

	var two_line = /\n\n/g;
	var one_line = /\n/g;
	function linebreak(s) {
  	  return s.replace(two_line, '<p></p>').replace(one_line, '<br>');
	}

	var first_char = /\S/;
	function capitalize(s) {
  	  return s.replace(first_char, function(m) { return m.toUpperCase(); });
	}

	// LA

	function get_word (str, n) {
    	  var words = str.split(" ");
    	  if (n > 0)
      	    return words[n-1];
    	  else
      	    return words;
	}

	function remove_first_char (str) {
    	  return str.substr(1, str.length-1);
	}

	function remove_first_word (str) {
    	  var words = str.split(" ");
    	  return str.substr(words[0].length+1, str.length-words[0].length);
	}

	function remove_two_words (str) {
    	  str = remove_first_word(str);
    	  return (" " + remove_first_word(str));
	}

	function get_request_type (str) {

    	  // send a message
    	  if ((str.indexOf("send") !== -1) && (str.indexOf("message") !== -1)) {
            return 1;
    	  }
    	  // send_to attribute
    	  else if ( ((slate_req === 'send_message_req') && (str.indexOf("to") !== -1)) || 
    	       	    ((slate_req === 'send_message_to') && (str.indexOf("to") !== -1)) || 
	      	    ((slate_req === 'send_message_compose') && (str.indexOf("to") !== -1)) ) {
            return 101;
    	  }
    	  // send_compose attribute
    	  else if ( ((slate_req === 'send_message_req') && (str.indexOf("compose") !== -1)) || 
    	       	    ((slate_req === 'send_message_compose') && (str.indexOf("compose") !== -1)) || 
	      	    ((slate_req === 'send_message_to') && (str.indexOf("composing") !== -1)) ||
	      	    ((slate_req === 'send_message_to') && (str.indexOf("compose") !== -1)) ) {
            return 102;
    	  }
    	  // send_compose to send_to attribute shift
    	  else if ( ((slate_req === 'send_message_compose') && 
    	       	    ((str.indexOf("end") !== -1) || (str.indexOf("done") !== -1) ||
	       	    (str.indexOf("over") !== -1) )) ) {
            return 103;
    	  }
    	  // Write a timeline post
    	  if ( ((str.indexOf("write") !== -1) && ((str.indexOf("time line") !== -1) || (str.indexOf("timeline") !== -1))) || 
       	       ((str.indexOf("post") !== -1) && ((str.indexOf("time line") !== -1) || (str.indexOf("timeline") !== -1))) ||
	       ((str.indexOf("right") !== -1) && ((str.indexOf("time line") !== -1) || (str.indexOf("timeline") !== -1))) ){
            return 2;
    	  }
    	  // timeline_to attribute
    	  else if ( ((slate_req === 'timeline_message_req') && (str.indexOf("to") !== -1)) || 
    	       	    ((slate_req === 'timeline_message_to') && (str.indexOf("to") !== -1)) || 
	      	    ((slate_req === 'timeline_message_compose') && (str.indexOf("to") !== -1)) ) {
            return 201;
    	  }
    	  // timeline_compose attribute
    	  else if ( ((slate_req === 'timeline_message_req') && (str.indexOf("compose") !== -1)) || 
    	       	    ((slate_req === 'timeline_message_compose') && (str.indexOf("compose") !== -1)) || 
	      	    ((slate_req === 'timeline_message_to') && (str.indexOf("composing") !== -1)) ||
	      	    ((slate_req === 'timeline_message_to') && (str.indexOf("compose") !== -1)) ) {
            return 202;
    	  }
    	  // timeline_compose to timeline_to attribute shift
    	  else if ( ((slate_req === 'timeline_message_compose') && 
    	       	    ((str.indexOf("end") !== -1) || (str.indexOf("done") !== -1) ||
	       	    (str.indexOf("over") !== -1) )) ) {
            return 203;
    	  }
    	  // Write a post on my wall
    	  if ( ((str.indexOf("write") !== -1) && ((str.indexOf("time line") !== -1) || (str.indexOf("timeline") !== -1)
       	 			    	      	  || (str.indexOf("wall") !== -1))) || 
       	       ((str.indexOf("post") !== -1) && ((str.indexOf("time line") !== -1) || (str.indexOf("timeline") !== -1)
	 		       	       	         || (str.indexOf("wall") !== -1))) ||
	       ((str.indexOf("right") !== -1) && ((str.indexOf("time line") !== -1) || (str.indexOf("timeline") !== -1)
	 			    	         || (str.indexOf("wall") !== -1))) ) {
            return 3;
          }
    	  // postonwall_to attribute
    	  else if ( ((slate_req === 'postonwall_message_req') && (str.indexOf("to") !== -1)) || 
    	       	    ((slate_req === 'postonwall_message_to') && (str.indexOf("to") !== -1)) || 
	      	    ((slate_req === 'postonwall_message_compose') && (str.indexOf("to") !== -1)) ) {
            return 301;
    	  }
    	  // postonwall_compose attribute
    	  else if ( ((slate_req === 'postonwall_message_req') && (str.indexOf("compose") !== -1)) || 
    	       	    ((slate_req === 'postonwall_message_compose') && (str.indexOf("compose") !== -1)) || 
	      	    ((slate_req === 'postonwall_message_to') && (str.indexOf("composing") !== -1)) ||
	      	    ((slate_req === 'postonwall_message_to') && (str.indexOf("compose") !== -1)) ) {
            return 302;
    	  }
    	  // postonwall_compose to postonwall_to attribute shift
    	  else if ( ((slate_req === 'postonwall_message_compose') && 
    	       	    ((str.indexOf(" end") !== -1) || (str.indexOf("done") !== -1) ||
	       	    (str.indexOf("over") !== -1) || (str.indexOf("stop") !== -1) )) ) {
            return 303;
    	  }
    	  // postonwall_compose to 'send' message
    	  else if ( ((slate_req === 'postonwall_message_compose') &&
    	       	    ((str.indexOf("send") !== -1) || (str.indexOf("post") !== -1) )) ) {
	    return 304;
    	  }
    	  // postonwall_compose to 'cancel' message
    	  else if ( ((slate_req === 'postonwall_message_compose') &&
    	       	    ((str.indexOf("cancel") !== -1) )) ) {
	    return 305;
    	  }
    	  // Start chat
    	  if ( ((str.indexOf("chat") !== -1) || ((str.indexOf("buddy") !== -1))) &&
       	       (str.indexOf("cancel") === -1) ) {
            return 4;
    	  }
    	  // End chat
    	  else if ( (( $('#connect').get(0).value === 'disconnect' ) &&
    	       	    ((str.indexOf("end") !== -1) || (str.indexOf("done") !== -1) ||
	       	    (str.indexOf("cancel") !== -1) || (str.indexOf("stop") !== -1) ) ) ) {
	    return 401;
    	  }
    	  else { return 0; }

	}

	// send button in send_message
	function send_message_send() {
  	  slate_req = '';
  	  var composed_msg = $('textarea#send_compose').val(); //document.getElementById("send_compose").value;
  	  //alert(composed_msg);
  	  $('#slate').html('');
  	  interim_span.innerHTML = '';

  	  // FB.api
  	  /*
  	  alert('Entering FB . api');
  	  FB.api('/me', function(response) {
    	    alert("Your friend's name is " + response.name);
  	  });
  	  alert('Out of FB . api');
  	  */
  	  /*
  	  alert('Posting on my wall');
  	  FB.api('/me/feed', 'post', { body: 'hello', message: 'My message is ...' }, function(response) {
            if (!response || response.error) {
              alert('Error occured');
            } else {
              alert('Post ID: ' + response);
            }
          });
 	  alert('Posted on my wall');
  	  */


	}

	// cancel button in send_message
	function send_message_cancel() {
  	  slate_req = '';
  	  $('#slate').html('');
  	  interim_span.innerHTML = '';
	}

	// send button in timeline_message
	function timeline_message_send() {
  	  slate_req = '';
  	  var composed_msg = $('textarea#timeline_compose').val(); //document.getElementById("timeline_compose").value;
  	  var composed_to = $('textarea#timeline_to').val();
  	  //alert('composed message: ' + composed_msg + ';\n composed to: ' + composed_to);

  	  $('#slate').html('');
  	  interim_span.innerHTML = '';
	}

	// cancel button in timeline_message
	function timeline_message_cancel() {
  	  slate_req = '';
  	  $('#slate').html('');
  	  interim_span.innerHTML = '';
	}

	// send button in postonwall_message
	function postonwall_message_send() {
 	   slate_req = '';
  	   var composed_msg = $('textarea#postonwall_compose').val(); //document.getElementById("send_compose").value;
  	   //alert(composed_msg);
  	   $('#slate').html('');
  	   interim_span.innerHTML = '';

  	   //alert('Posting on my wall');
  	   FB.api('/me/feed', 'post', { body: '', message: composed_msg }, function(response) {
             if (!response || response.error) {
               alert('Error occured');
             } else {
               //alert('Post ID: ' + response);
             }
           });
	   interim_span.innerHTML = 'Successfully posted on your wall';
  	   //alert('Posted on my wall');

	 }

	 // cancel button in postonwall_message
	 function postonwall_message_cancel() {
  	   slate_req = '';
  	   $('#slate').html('');
  	   interim_span.innerHTML = '';
	 }

	 // find children in parent, return true if exists
	 function is_children_exists(par, chil) {
  	   var child_var = $(par).attr("id");
  	   return (child_var === chil);
	 }

	 // --LA

	 function createEmail() {
  	   var n = final_transcript.indexOf('\n');
  	   if (n < 0 || n >= 80) {
    	     n = 40 + final_transcript.substring(40).indexOf(' ');
  	   }
  	   var subject = encodeURI(final_transcript.substring(0, n));
  	   var body = encodeURI(final_transcript.substring(n + 1));
  	   window.location.href = 'mailto:?subject=' + subject + '&body=' + body;
	 }

	 function copyButton() {
  	   if (recognizing) {
    	     recognizing = false;
    	     recognition.stop();
  	   }
  	   //copy_button.style.display = 'none';
  	   //copy_info.style.display = 'inline-block';
  	   showInfo('');
	 }

	 function emailButton() {
  	   if (recognizing) {
    	     create_email = true;
    	     recognizing = false;
    	     recognition.stop();
  	   } else {
    	     createEmail();
  	   }
  	   //email_button.style.display = 'none';
  	   //email_info.style.display = 'inline-block';
  	   showInfo('');
	 }

	 function startButton(event) {
  	   if (recognizing) {
    	     recognition.stop();
    	     return;
  	   }
  	   final_transcript = '';
  	   recognition.lang = 'en-US'; //select_dialect.value;
  	   recognition.start();
  	   ignore_onend = false;
  	   final_span.innerHTML = '';
  	   interim_span.innerHTML = '';
  	   start_img.src = '//www.google.com/intl/en/chrome/assets/common/images/content/mic-slash.gif';
  	   showInfo('info_allow');
  	   showButtons('none');
  	   start_timestamp = event.timeStamp;
	 }

	 function showInfo(s) {
  	   if (s) {
    	     for (var child = info.firstChild; child; child = child.nextSibling) {
      	       if (child.style) {
                 child.style.display = child.id == s ? 'inline' : 'none';
      	       }
             }
    	     info.style.visibility = 'visible';
  	   } else {
    	     info.style.visibility = 'hidden';
  	   }
	 }

	 var current_style;
	 function showButtons(style) {
  	   if (style == current_style) {
    	     return;
  	   }
  	   current_style = style;
  	   //copy_button.style.display = style;
  	   //email_button.style.display = style;
  	   //copy_info.style.display = 'none';
  	   //email_info.style.display = 'none';
	 }

	// --speech
	</script>
	<!-- //Speech -->
	
      </div>

    <?php } else { ?>
      <div>
        <!--
	<h1>You are not logged in... please login...</h1>
	-->
	<!--
        <div class="fb-login-button" data-scope="user_likes,user_photos,user_actions.news,read_stream,publish_stream"></div>
	-->
	<fb:login-button show-faces="true" width="200" max-rows="1" scope='user_likes,user_photos,user_actions.news,read_stream,publish_stream,publish_actions,user_location,xmpp_login,user_online_presence' perms='publish_stream'></fb:login-button>
      </div>
    <?php } ?>

    <!-- End Add here -->

  </body>
</html>
