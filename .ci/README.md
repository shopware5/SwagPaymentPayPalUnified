# .ci

> CI recipes for `SwagPaymentPayPalUnified`

## `compose.sh`

A wrapper script for `docker compose`. Using this, business logic can more
easily be kept out of platform-specific code, like the `.gitlab-ci.yml`.

### Usage in Gitlab-CI

As Gitlab-CI provides the current stage and job via the Environment, it's enough
to just call the script:

`.ci/compose.sh run some-service`

### Local usage / usage in other CI-Systems

You may either imitate the Gitlab-CI environment, or select the job to run using
the arguments:

`.ci/compose.sh e2e shopware-current run some-service`

Under the hood, a `.env`-file will be created, which is used to provide general
information about the test environment, like host names, user accounts and so
on.

## `compose.[stage].yml`

These files will be used as a base `compose.yml` when executing:

`.ci/compose.sh some-stage [job-name] ...`

## `compose.[stage].[job].yml`

These files will be used to extend the `compose.[stage].yml`, when executing:

`.ci/compose.sh some-stage some-job ...`

---

## Executing the End-To-End-Testsuite locally

### Preparation

Make sure that docker [`compose`](https://docs.docker.com/compose/) is installed on
your system.

### Usage

1. Make sure that at least the following variables (found in`.env.dist`) are set
in your environment, OR fill them in after running `make .env` manually:

```dotenv
PAYPAL_CUSTOMER_EMAIL="${PAYPAL_CUSTOMER_EMAIL}"
PAYPAL_CUSTOMER_PASSWORD="${PAYPAL_CUSTOMER_PASSWORD}"
PAYPAL_CREDIT_CARD="${PAYPAL_CREDIT_CARD}"
PAYPAL_SANDBOX_CLIENT_ID="${PAYPAL_SANDBOX_CLIENT_ID}"
PAYPAL_SANDBOX_CLIENT_SECRET="${PAYPAL_SANDBOX_CLIENT_SECRET}"
PAYPAL_SANDBOX_MERCHANT_ID="${PAYPAL_SANDBOX_MERCHANT_ID}"
PAYPAL_SEPA_IBAN="${PAYPAL_SEPA_IBAN}"
PAYPAL_SEPA_PHONE="${PAYPAL_SEPA_PHONE}"
PAYPAL_SEPA_BIRTHDAY="${PAYPAL_SEPA_BIRTHDAY}"
```

2. Execute `.ci/compose.sh e2e shopware-current run playwright` or
`.ci/compose.sh e2e shopware-legacy run playwright`
