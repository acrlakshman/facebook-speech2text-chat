facebook-speech2text-chat
=========================
A facebook app created during MidWest Hackathon 2013 at UIUC by facebook, using which you can chat as you speak.
It can post on your wall whatever you tell it to post.

How to use
==========
Trigger word: 'lucky' (you can change it in the source code, line 780 of index.php)

Customize dialect: <a href="https://github.com/acrlakshman/facebook-speech2text-chat/blob/master/index.php#L1387">line 1387</a> of index.php (currently set to en-US {American english})

1) Whenever you want it to do, start that sentence with 'lucky'. For instance, if you want it to connect to chat
server, just say 'lucky connect me to chat' or something similar... Then select any of your online friend and keep
speaking, it will chat for you.

2) 'lucky post on my wall' or something similar... will enable you to type something that you want it to post on your
wall... when you are done typing just say 'lucky done' or 'lucky post', it will post for you on your facebook wall.

Acknowledgements
================
Source code at https://github.com/javierfigueroa/turedsocial was a great help in knowing how to connect to facebook chat server using Strophe.

Source of http://www.google.com/intl/en/chrome/demos/speech.html is the basis for speech2text code used in this hack.
