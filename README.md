# CiviCRM: Block Subscriber Confirmation Emails
## com.joineryhq.blockconfirmationemail

CiviCRM extension to prevent sending of automatic notification emails for

* Mailing group unsubscribe
* Mailing group re-subscribe
* Email opt-out

The extension is licensed under [GPL-3.0](LICENSE.txt).

## Rationale

Almost certainly, your organization is legally required to provide links in all
bulk email allowing the recipient to easily opt-out of all mailings and/or
unsubscribe from specific mailing lists.

By default, CiviCRM requires this for all outbound mass email.

However, some organizations are bothered by another feature of CiviCRM which
goes along with these links: whenever a recipient uses one of these links,
CiviCRM automatically sends an email notification to that recipient confirming
their action, i.e., "Dear Allen: Just in case you're wondering, we unsubscribed
you from this list. Click the link below to re-subscribe."

Why is this a bother? Here's one example:

* An organization has some "exempt" users who cannot legitimately opt-out (for
  example, they are employees, or students of the university). And of course they
  also have "non-exempt" users who _must_ be allowed to opt-out, because they're
  not in one of those exempt categories.
* This organization send a monthly mailing to a combination of those recipients,
  so they have to include the opt-out links by law, for the sake of the non-exempt
  recipients.
* When an exempt recipient clicks an unsubscribe link, CiviCRM will unsubscribe
  them (as it should); organization staff will later simply re-subscribe them
  (under their own internal policy which requires that these recipients must
  remain subscribed).
* Nonetheless, this exempt recipient will (per CiviCRM's standard behavior) automatically
  receive an email notification confirming that they're unsubscribed. While this may
  be temporarily true, it is, in essence, false (because this recipient is not allowed
  to unsubscribe). Organization staff now have more explaining to do. It's a
  headache.

This extension removes that headache simply by preventing such email notifications
from being sent -- to anyone, whether they're exempt or not.

## Alternatives and caveats

Read the above carefully. This may not be for you. You might want to handle your
unique situation a in different way.


## Usage

If enabled, this extension will prevent sending the relevant email notifications.
No configuration is needed.


## Support
Support for this extension is handled under Joinery's ["Limited Support" policy](https://joineryhq.com/software-support-levels#limited-support).

Public issue queue for this extension: [https://github.com/JoineryHQ/com.joineryhq.blockconfirmationemail/issues](https://github.com/JoineryHQ/com.joineryhq.blockconfirmationemail/issues)
