# signalwire
<h2>Overview</h2>
This module integrates Drupal 8 with Signalwire an advanced communications platform. Signalwire supports advanced communications capabilities like programmable voice, messaging, and video. For more information visit <a href="https://signalwire.com"> Signalwire</a>.
<h2>How it works</h2>
Signalwire module simplifies the process of creating and sending SMS text messages over Signalwire communications platform. Sending sms text involves three simple steps:
<ol>
    <li>Start by creating an <a href="https://www.drupal.org/project/encrypt">encryption profile</a> using the Encrypt and Key modules.</li>
    <li>Configure drupal with the right Signalwire settings such as (Signalwire Space URL, Signalwire api key, Signalwire project key and sender number).</li>
    <li>Configure a content type by determining which field(s) of the content type will make up the message body. This can be done from the <i>manage display form</i> of each content type. </li>
    <li>Creating content of the configured type in two above to initiate the process of sending the message.</li>
</ol>
<h2>Features</h2>
<ul>
    <li>supports scheduled (daily, weekly, and monthly) sending of text messages </li>
    <li>supports content type message settings</li>
    <li>supports sending text messages to multiple telephone numbers</li>
    <li>supports the use of drupal's queue system in sending text messages</li>
    <li>provides support for encryption of token, base-url and project ID</li>
    <li>provides option to turn off out-going messages</li>
</ul>
<h2>Requirements & Installation</h2>
This module depends on <a href="https://www.drupal.org/project/encrypt">Encrypt</a> module for encryption/decryption of key information.
Using composer is the preferred way of installing signalwire module. The module also makes use of a Client library for connecting to SignalWire platform which is available on <a href="https://packagist.org/packages/signalwire/signalwire">packagist</a>. <br>
<ul>
    <li><b>Download module from drupal:</b> composer require drupal/signalwire</li>
    <li><b>Enable module using drush: </b>drush en signalwire -y</li>
</ul>
