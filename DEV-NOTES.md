# DONE
* Created data model
  * CRM_Mailing_Recur_DAO_Recurrence <--> civicrm_mailing_recurrence
  * CRM_Mailing_Recur_DAO_RecurRule <--> civicrm_mailing_recur_rule
* Creates a maximum of 50 recurrences for each recurring email
* Logic to create a recurring email CRM_Mailing_Recur_BAO_MailingRecur::create()
* Append a new option to BlockSchedule that adds a recurring schedule form when selected
* UI to create a valid recur rule
* Prevent cancelling and editing recurrences (UI)
* Ensure that when a recurring email is deleted, all the recurrences are also deleted
* Add an edit master link if a mailing is recurring
* Redirect to scheduled mailings screen when you hit schedule
* Clone google repeat date UI with minor tweaks (see UI for recurrence below)
* Remember if a mail is recuring in BlockSchedule and set the initial form state
* count should be positive
* Allow people to switch a mailing between recurring and one-off (UI)
* Allow people to switch a mailing between recurring and one-off (code)

* Create a cron job that cycles through each recurring emails, creates new ones if necessary, and deletes any that should not be there, ensuring that the 50 limit does not include dates in the past.


# TODO

# KNOWN ISSUES
* Status should be recurring mailing or recurrence but for some reason
# WISH LIST
* Allow people to preview the list of scheduled emails before they are created (UI)
* Create advanced mode which allows editing the recurrence schedule

## Data model

`RecurRule` specifies that a mailing is recurring. One should not be able to send a recurring mailing. The key field is rule, which corresponds to the Recurance Rule as specified in https://tools.ietf.org/html/rfc2445#section-4.3.10 and https://tools.ietf.org/html/rfc5545#section-3.3.10.

`Recurrence` identifies all instances of recurring mailings specified with a RecurRule.

## Business logic

Recurrence emails should always be scheduled.

If a recurring email is deleted, all the recurrences should be deleted (this includes the emails as well as the `Recurrence` entities).

We should not generate any emails that are scheduled for less than right now +1 hour since that will give people time to realise if they have made a mistake.

Calculating all recurring emails and creating any missing ones.

Note: we should not delete existing recurrences if they are in range. We should just delete out of range emails and create new ones as necesary.

Cap scheduled emails at X days into the future? heck to see if any new ones should be created each time we run the job.

Only delete mailings if they have not been sent.

## UI additions and alterations

We should be able to turn any email into a recurring email.

Ask people to confirm the list of scheduled emails before saving.

Cron job that cycles all recurring mails and adds new ones if necessary (daily)

### UI for creating recurrence

* Repeats:
* Daily
* Weekly
* Monthly
* Yearly
* Starts: (date+time)
* Repeat every (integer)

if Repeats:Weekly:{
  * Repeat on: (list of week days)
}
if Repeats:Monthly:{
  * Repeat by:
  * day of month
  * day of week (2nd Tuesday if this is easy)
}
* Until date (date, optional)

Note: the RRULE format allows for more options that we expose on the form. We could always add an advanced mode which allows you to specify more formats (potentially including exposing the RRULE field)

If the start date is out of range, we will ignore / include it [need to decide]

## Notes on file structure of this extension

I'm trying out a new naming convention for the files, and functions in this extension (in essence, moving from io.3sd.civicrmmailingrecur to civicrm-mailing-recur).

Has basically been a find and place on the extension.php and extension.civix.php files and some updates to the info.xml file. Nothing has broken so far!

We've exercised angular
