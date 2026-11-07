# ADR-0007: Transactional email provider

Status: Proposed. Blocked on verification.

## Context
Enquiry notifications must reach the owner reliably. Sending mail directly from a VPS is often restricted and has poor deliverability.

## Decision
Use a transactional email provider, preferably over an HTTPS API. The final choice waits for two verifications:
1. Contabo's **current** outbound email and SMTP port policy.
2. Each candidate provider's **current** free tier, limits, terms and Laravel support.

Candidates to evaluate: Resend, Postmark, Amazon SES, Mailgun, Brevo. No selection is made here.

## Requirements
Domain authentication with SPF, DKIM and DMARC, bounce handling, an API or SMTP that Laravel supports, and a low cost for low volume.

## Interim
Local development uses Mailpit. Until a domain exists, no production email is sent. Stored enquiries are visible in the admin regardless of email status.

## Consequences
Email stays queued and failure-tolerant: a failed email never loses an enquiry.
