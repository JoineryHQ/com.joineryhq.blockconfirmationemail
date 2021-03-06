# CiviCRM: Block Confirmation Emails
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

However, some organizations are bothered by another feature of CiviCMR which
goes along with these links: whenever a recipient uses one of these links,
CiviCRM automatically sends an email notification to that recipient confirming
their action, i.e., "Dear John. Just in case you're wondering, we unsubscribed
you from this list. Click the link below to re-subscribe."

Why is this a bother? Here's one example:

* An organization has some users who cannot legitimately opt-out -- for example,
  they are employees, or students of the university. And of course they have users
  who can opt-out because they're not in those exempt categories. They send a
  monthly mailing to a combination of those users, so they have to include the
  opt-out links by law, for the sake of the non-exempt recipients.
* When an exempt recipient clicks an unsubscribe link, CiviCRM will unsubscribe
  them; organization staff will later simply remove that distinction for
  non-exempt recipients.
* Nonetheless, this exempt recipient automatically receives an email notification
  confirming that they're unsubscribed. While this may be temporarily true, it is,
  in essence, false. Organization staff now have more explaining to do. It's a
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
![screenshot](/images/joinery-logo.png)

Joinery provides services for CiviCRM including custom extension development,
training, data migrations, and more. We aim to keep this extension in good
working order, and will do our best to respond appropriately to issues reported
on its [github issue queue](https://github.com/JoineryHQ/com.joineryhq.blockconfirmationemail/issues).
However, we place a priority on the needs of our clients, and we make no
guarantees of support here. If you require urgent or highly customized
improvements to this extension, we may suggest conducting a fee-based project
under our standard commercial terms, based on availability.  In any case, the
place to start is the [github issue queue](https://github.com/JoineryHQ/com.joineryhq.blockconfirmationemail/issues) --
let us hear what you need and we'll be glad to help where we can.

And, if you need help with any other aspect of CiviCRM -- from hosting to custom
development to strategic consultation and more -- please contact us directly via
https://joineryhq.com