=== WPCal.io - Easy Meeting Scheduler ===
Contributors: wpcal, amritanandh, midhubala, dark-prince, yuvarajsenthil
Tags: meeting, appointment, scheduling, booking, interview, calendly
Requires at least: 4.7
Tested up to: 5.4
Stable tag: 0.9.2.0
Requires PHP: 7.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Your clients can quickly view your real-time availability and self-book their own slots, and eliminate all back-and-forth emailing.

== Description ==

Schedule Meetings in under 30 seconds without searching through your calendar and all the back-and-forth emails.

Check the website - [https://wpcal.io/](https://wpcal.io/)

== HOW IT WORKS: == 
1. <strong>Set your availability (One-time setup)</strong> -<br>Let us know your availability by either setting it up yourself or by connecting your calendars.<br><br>
1. <strong>Send your clients a link to your booking page</strong> -<br>To schedule a meeting with someone, share the link to your personalized booking page via email.<br><br>
1. <strong>They choose a convenient slot</strong> -<br>Your clients can choose an available slot by selecting a preferred date and time.<br><br>
1. <strong>Voila! Your meeting is scheduled!</strong> - <br>Your meeting is scheduled in just a few clicks. No checking calendars or sending emails back and forth.

<strong><em>Never ask “what time works for you?” again.</em></strong><br>Your clients can quickly view your real-time availability and self-book their own appointments—reschedule with a click, and eliminate all back-and-forth emailing.

== WHAT YOU CAN USE IT FOR? ==
* Consultation
* Interviewing
* Customer Engagement
* Sales & Marketing

== YOUR TIME. YOUR RULES. ==
* Control the duration of meetings
* Add multiple types of locations like in-person meeting, over the phone, web conferencing apps or even ask the invitee to enter a location etc from which invitees can choose one
* Cap the number of bookings per day
* Completely flexible availability - Choose particular days of the week, hours of the day etc. to be available/unavailable
* Prevent last-minute bookings
* Set aside time before or after events
* Let invitees answer a question while booking an event

== CALENDAR APPS INTEGRATIONS ==
2-way sync for Calendars - New meetings booked via WPCal will be added to your Calendar app and when an event is directly added to your Calendar app, that timeslot will be blocked from your WPCal availability.

* Google Calendar
* Outlook Calendar (coming soon)
* Office 365 (coming soon)
* iCloud Calendar (coming soon)

== WEB CONFERENCING APPS INTEGRATIONS ==
* Google Meet/Hangouts
* GoToMeeting
* Zoom

== >> ALL PREMIUM FEATURES ARE 100% FREE DURING THIS TIME OF CRISIS ==
Install this plugin and we'll onboard you to use the Premium features for free.

== PREMIUM FEATURES (RELEASED) ==
* Unlimited admin users per site
* Unlimited Event types
* Unlimited calendar accounts per admin user

== PREMIUM FEATURES (COMING SOON) ==
* Recurring events - Invitees can book an event that recurs periodically.
* Group events - Host multiple invitees at the same event for tours, webinars, trainings and more.
* Team events - Pooled availability options for teams (round robin, collective scheduling, multiple team members on one page).
* Make me look busy - If you have a lot of availability, you can appear a bit more booked up or busy to your clients.
* Avoid meetings scattered throughout your day - If you offer slots throughout the day, you can avoid meetings scattered through your day.
* Custom multi-type questions for invitees while booking.
* Customizable email notifications and reminders.
* Stripe and PayPal integrations - Connect your payment accounts so invitees can submit credit card payments securely upon scheduling a meeting with you.
* Custom integrations with webhooks - Build your own integrations using the plugin's webhooks.
* Brand customization of booking page - Customize the fonts and accent colors of the booking widget to match your brand’s look and feel.

<strong><em>Take back control of your time!</em></strong><br>If you regularly schedule meetings with others, you should really check out the plugin.

A simple and more native alternative to Calendly for WordPress.

== Installation ==

This section describes how to install the plugin and get it working.

e.g.

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Use the Settings->Plugin Name screen to configure the plugin
1. (Make your instructions match the desired user flow for activating and installing your plugin. Include any steps that might be needed for explanatory purposes)

== Frequently Asked Questions ==

= Can my invitees book an event with me from their mobile devices? =

Yes, of course. Please check out the mobile mockup in the screenshots section.

= How can I avoid last-minute booking of events? =

You can set a Minimum Scheduling Notice when creating an event type. For eg. if you set the notice as 24 hours, invitees will only be able to book slots that are 24 hours or more into the future. You can also avoid new bookings after, say, 11pm of yesterday/2 days ago.

= How quickly can I expect replies to my support requests? =

We reply within 1-2 business days and always strive for first contact resolution. Send your support requests to <a href="mailto:support@wpcal.io">support@wpcal.io</a>.

== Screenshots ==

1. The booking widget in the booking page.
2. The booking widget in the mobile view.
3. Event Type setup page in admin end.
4. Event types location options.
5. The booking widget step 2 - collecting invitee details.
6. The booking widget confirmation page.
7. The booking widget confirmation page with reschedule and cancel options.
8. Reschedule booking widget with old booking details.

== Changelog ==

= v0.9.2.0 - Jul 24th 2020 =
Feature: Zoom is integrated and can be used as Event type location (new meeting URL will be generated for each new booking).
Improvement: Number of calendar per admin user restriction is removed.
Improvement: Minor code improvements.
Fix: If a booking is rescheduled in admin end, new reschedule link in the email not redirecting.

= v0.9.1.5 - Jul 20th 2020 =
* Improvement: Premium plans related content changes and more features included in Free. See <a href="https://wpcal.io/#pricing" target="_blank">pricing</a>.
* Fix: 3 sample Event types which supposed to be created during first activation was stopped creating as of v0.9.1.0.
* Fix: Booking widget UI theme conflicts.

= v0.9.1.4 - Jul 17th 2020 =
* Fix: Minor code changes to avoid Vue library conflicts with other plugins.

= v0.9.1.3 - Jul 14th 2020 =
* Fix: Page unresponsive error (Freezing) in Chrome in WPCal admin pages(Settings, Add event type, etc) for users west of UTC.
* Fix: Page unresponsive while choosing availability date range in Event type settings for users west of UTC.

= v0.9.1.2 - Jul 6th 2020 =
* Fix: Time picker was not working in Safari browser.
* Fix: JS Date class related issue in Safari browser which was affecting date validations etc.
* Fix: Minor bugs.

= v0.9.1.1 - Jul 1st 2020 =
* Fix: Repo old and new files mixed up fixed - version bump.

= v0.9.1.0 - Jul 1st 2020 =
* Feature: GotoMeeting and Google Meet/Hangout integrated and can be used as Event type location (new meeting URL will be generated for each new booking) (Zoom integration is coming soon).
* Feature: Event type location now has options for phone, ask invitee, in-person, custom locations in addition to third-party web conference apps integration.
* Feature: In the event type location you can add more than one location, and the invitee can choose one from them.
* Improvement: Security enhancements.
* Improvement: Event type add/edit page - Will show alert when user tries to leave page without saving changes (We will bring auto-save in due course)
* Improvement: On Event type name change, admin is alerted if they want to make changes to WP Page title as well.
* Improvement: Booking Step 2 and Confirmation page load faster now.
* Improvement: If any API integration disconnection is not successful, now it will ask for force disconnect.
* Improvement: Title bar content changes while navigation in Admin end of WPCal.
* Improvement: Link to Support email added.
* Improvement: Loading indicator added for initial loading and also for component loading.
* Improvement: UI/UX improvements.
* Fix: Booking slots not regenerated for other event type of same admin while booking and rescheduling.
* Fix: Max booking per day for the event type was considering all bookings across admins and event types for the day issue fixed.
* Fix: If event type user end booking page's permalink changes not update issue.
* Fix: Unable to save event type with purple color selected.
* Fix: Error message not showing for certain error types.
* Fix: Font rendering in Windows and Linux system is improved.
* Fix: Overflow menu in Event Type occasionally not working.

= v0.9.0.1 - May 29th 2020 =
* Fix: Not able to book Today's slots.

= v0.9.0.0 - May 9th 2020 =
* Intial public beta launch.

== Upgrade Notice ==
