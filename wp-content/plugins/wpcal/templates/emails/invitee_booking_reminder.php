<?php
/**
 * WPCal.io
 * Copyright (c) 2020 Revmakx LLC
 * revmakx.com
 */

if(!defined( 'ABSPATH' )){ exit;}

$mail_subject = "Reminder: Event with {$mail_data['booking_admin_display_name']} in 24 hours.";

$hi_name = $mail_data['hi_invitee_name'] ? ' '.$mail_data['hi_invitee_name'] : '';


$wpcal_site_url = WPCAL_SITE_URL;

$mail_body = <<<EOD

	<div
      style="
        background-color: #eff3fc;
        padding: 50px 0;
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto,
          Oxygen, Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
        font-size: 15px;
      "
    >
      <div
        style="
          width: 360px;
          margin: auto;
          padding: 20px;
          background-color: #fff;
          box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
          color: #131336;
          line-height: 1.2em;
          box-sizing: border-box;
        "
      >
        <div
          style="
            border-bottom: 1px solid #d8dbe7;
            padding-bottom: 10px;
            margin-bottom: 10px;
          "
        >
          <img alt="WPCal.io" src="{$wpcal_site_url}/emails/images/wpcal_logo.png" />
        </div>
        <table>
          <tr>
            <td style="padding: 10px 0;">
              <span style="color: #7c7d9c;"
                >Hi{$hi_name}, <br />You have an event with {$mail_data['booking_admin_display_name']} in
                24 hours.</span
              >
            </td>
          </tr>
          <tr>
            <td style="padding: 10px 0;">
              <strong style="font-size: 11px; text-transform: uppercase;"
                >Event Date & Time</strong
              ><br />
              <span style="color: #7c7d9c;"
                >{$mail_data['booking_from_to_time_str_with_tz']}</span
              >
            </td>
		  </tr>
		  {$mail_data['location_html']}
        </table>
      </div>
      <div
        style="
          color: #7c7d9c;
          font-size: 11px;
          text-align: center;
          padding: 10px 0 50px;
        "
      >
        <a
          style="color: #7c7d9c; text-decoration: underline;"
          href="{$wpcal_site_url}?utm_source=invitee_mail&utm_medium=event"
          >Create your own booking page</a
        >
      </div>
    </div>
EOD;
