=== ConveYour for WordPress ===
Contributors: srhyne
Tags: analytics, gravity-forms, coursepress, syncing
Requires at least: 3.0.1
Tested up to: 4.3.1
Stable tag: 1.3.7
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Integrate ConveYour into your Wordpress site. Works great with CoursePress and Gravity Forms.

== Description ==

ConveYour is the ultimate marketing platform for speakers, thought leaders, authors, bloggers, and infopreneurs. 

= Why ConveYour? =

- Use our light-weight LMS (learning management system) to turn you offline content into a gamified mobile engagement tool.
- Use your new mobile learning course to attract prospects, capture attendee info, or sell review courses to your existing customer base
- Put your contacts on the marketing conveyor belt so to speak through automated email & SMS messaging.
- Boost customer success in your information products through ConveYour Analytics. Track the behavior of your Wordpress users through your 
e-learning course (on CoursePress).

= ConveYour for Wordpress = 

Let's face it, user management in Wordpress doesn't have a lot to offer when it comes to organizing, segmenting, or messaging users. It's not meant to be used as a "user management system" or CRM really. 

With ConveYour for Wordpress, your Wordpress users will automatically be mirrored in ConveYour. You can start taking advantage of ConveYour's contact management & messaging features immediately without manual imports from Wordpress. Your ConveYour contact list will always be up to date. 

- Adds a user to your ConveYour account when a WordPress user signs up!
- Keeps your Wordpress user info up to date in ConveYour
- Tracks when WP users log in

= Setup = 

Setting up the ConveYour for Wordpress plugin is a super easy. 

**1. Get your ConveYour API credentials**
**2. Save your credentials into the plugin's settings page**
**3. That's it!**

That's all you have to do to start seeing Wordpress users show up in your ConveYour. If you have CoursePress Pro installed in your Wordpress, we will start tracking CoursePress events for each contact! Check out [ConveYour Analytics](http://conveyour.com/analytics) for more information

= Gravity Forms Integration =
_"Advanced Forms for WordPress Websites Just Don't Get Any Easier than [Gravity Forms](http://www.gravityforms.com/)"_
**Create or update a ConveYour contact through your Gravity Forms.**

Integrating with Gravity Forms is so easy. ConveYour for Wordpress works with all Gravity Forms license levels.

**How does it work?**
On any Gravity form, simply create a hidden field called `conveyour`. This will tell the plugin that you want to send the contents of the Gravity Form to ConveYour as contact submission! 

**Gravity Forms for ConveYour campaign registration**
Use your Gravity Form as a [ConveYour Campaign](http://conveyour.com/help?category=campaigns) registration form! Simply add a hidden field to your Gravity Form called `conveyour_campaign` with the value being the ConveYour campaign ID. 

You can attain your ConveYour Campaign ID simply by grabbing it from the URL when editing your campaign in ConveYour. 

= CoursePress Pro Integration = 
Coursepress Pro allows you to quickly create beautiful courses all within WordPress – whether you’re selling or sharing your knowledge, this plugin will save you time and make your work stand out.

ConveYour for Wordpress integrates with CoursePress to take course interaction and engagement to a new level! Just install this plugin and start taking advantage of [ConveYour's Analytics system](http://conveyour.com/analytics)

- Track the progress of a user as they interact with your CoursePress course!
- Track things like "finished_unit", "finished_module", etc.
- **Use these events to setup automated messaging to keep contacts engaged even when they aren't interacting with your course**.

Integrating ConveYour into your CoursePress course means you will have the power to.. 

- Dramatically increase your course completion rate
- Quickly diagnose where your course clients are struggling
- Make the course feel more personal through automated, personalized email & sms messaging

Example.. "Send an automated email to reengage users who have completed unit 1 over 3 days ago but they haven't started unit 2 yet. "

![ConveYour CoursePress Pro funnel](http://conveyour.com/img/site/analytics/elearning-course-funnel.png)

= Shortcodes = 

Add more data to an existing wordpress user's profile with the identify shortcode.

`[conveyour_identify company=acme title=CEO]`

You can also identify someone other than the current logged in user. 

`[conveyour_identify id=email@example.com first_name=John last_name=Doe]`

place `[conveyour_track id="user@example.com" event="success" property="value" property2="value2"]` in your post or page, it will send a tracking request when a user visits this page.

- id is optional, if it is not specified, email of current user will be used.
- feel free to set as many as 15 properties
- you can do something like `[is_post_request] [conveyour_track] [/is_post_request]` to send tracking request when user submitting a form.



== Screenshots ==

1. Get API credentials from [ConveYour](http://conveyour.com/)
2. Save API credentials into the plugin's settings page
3. Setup Gravity Form as a [ConveYour Campaign](http://conveyour.com/help?category=campaigns) registration form!
4. Track Coursepress Pro events and view activity on [ConveYour](http://conveyour.com/)

== Changelog ==

= 1.3.7 =

Added better support for Coursepress Pro completed unit/course hooks.

= 1.3.6 = 

* wp-async-task.php bundled without identifier for better management of plugin 

= 1.3.5 = 

* fixed bug with https sites
* add conveyour_identify shortcode 

`[conveyour_identify id=email@example.com first_name=John last_name=Doe]`

= 1.3.4 =

* added more fields to identify

* fixed timestamp issue on identify

= 1.3.3 =

* fixed the issue that login event can not be tracked correctly

= 1.3.2 =

* added a way to add or remove value from list field like tags

= 1.3.1 =

* fixed bug of conveyour_domain_sanitize function

= 1.3.0 =

* added a shortcode `conveyour_track` to send tracking request from a post or a page
* added two shortcode helpers `is_get_request` and `is_post_request`, it helps user to send tracking request from a form of a page

= 1.2.0 =
* fixed issue that multiple questions can not be tracked

= 1.1.0 =
* add unit_id & course_id to event properties