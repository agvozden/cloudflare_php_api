# cloudflare_php_api
cloudflare api sync with local zone, etc...

based on vexxhost/CloudFlare-API
download https://github.com/vexxhost/CloudFlare-API/blob/master/class_cloudflare.php

Read cloudflare zone first, then read local zone.
Diferent record will be updated, local record which not exist on cloudflare will be created.
None be deleted.

Limits:
> only full name in local zone (sumdomain,domain.tld)
> only one per subdomain (no multi subdomains)

update: multidomain support 
