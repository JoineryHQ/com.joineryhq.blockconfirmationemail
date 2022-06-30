{*
  This template includes the template it replaces (CRM/Mailing/Page/Component.tpl)
  and prepends some help text at the top expaining the BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER
  in subject lines.
*}

<div class="help">
    Automated Message templates of types OptOut, Unsubscribe and Resubscribe have 
    the marker <span style="white-space: nowrap">"{$BLOCKCONFIRMATIONEMAIL_SUBJECT_MARKER}"</span>
    appended to their 
    Subject line by the <em>{$blockconfirmation_ext.label}</em> extension. The
    extension will prevent sending of emails with this marker in the Subject line.
</div>
{include file="CRM/Mailing/Page/Component.tpl"}

