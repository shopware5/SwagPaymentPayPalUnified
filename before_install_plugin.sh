#!/usr/bin/env bash

PLUGIN_DIR=$1
ENV=$2

if [[ ! -d $PLUGIN_DIR/../../../engine/Shopware/Bundle/CookieBundle ]]; then
    echo 'Installing SwagCookieConsentManager.'

    $PLUGIN_DIR/../../../bin/console --env="$ENV" sw:cache:clear
    $PLUGIN_DIR/../../../bin/console --env="$ENV" sw:plugin:refresh
    $PLUGIN_DIR/../../../bin/console --env="$ENV" sw:plugin:install --activate SwagCookieConsentManager
    $PLUGIN_DIR/../../../bin/console --env="$ENV" sw:cache:clear
else
    echo 'SwagCookieConsentManager already present in Core.'
fi
    
