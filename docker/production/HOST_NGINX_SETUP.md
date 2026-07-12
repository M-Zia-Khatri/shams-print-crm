# Host Nginx Setup for `crm.ziakhatri.site`

> [!IMPORTANT]
> **These are manual VPS steps.** Nothing in this repository executes these commands. Run them on the VPS after the Docker stack is up and `APP_PORT=8080` is confirmed working.

---

## Overview

The CRM's Docker stack exposes its Nginx container on the host at `127.0.0.1:8080`. A separate host-level Nginx instance (already running and managing port 80/443 for other projects) acts as a reverse proxy, terminating SSL and forwarding traffic to the Docker container.

```
Internet
  └── Host Nginx :443 (SSL, crm.ziakhatri.site)
        └── proxy_pass → 127.0.0.1:8080
              └── Docker Nginx :80
                    └── PHP-FPM :9000
```

---

## Step 1 — DNS A Record

In your DNS provider, create an **A record** pointing `crm.ziakhatri.site` to the VPS public IP address. Allow up to a few minutes for propagation before proceeding.

---

## Step 2 — Create the Host Nginx Server Block

Create a new Nginx site configuration file on the VPS:

```bash
sudo nano /etc/nginx/sites-available/crm.ziakhatri.site
```

Paste the following content (HTTP only to start — Certbot will upgrade it to HTTPS in Step 4):

```nginx
server {
    listen 80;
    server_name crm.ziakhatri.site;

    location / {
        proxy_pass         http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header   Host              $host;
        proxy_set_header   X-Real-IP         $remote_addr;
        proxy_set_header   X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
    }
}
```

---

## Step 3 — Enable the Site and Reload Nginx

```bash
# Create symlink to enable the site
sudo ln -s /etc/nginx/sites-available/crm.ziakhatri.site \
           /etc/nginx/sites-enabled/crm.ziakhatri.site

# Test the configuration
sudo nginx -t

# Reload Nginx (zero-downtime)
sudo systemctl reload nginx
```

Verify that `http://crm.ziakhatri.site` reaches the CRM before proceeding to SSL.

---

## Step 4 — Obtain an SSL Certificate with Certbot

```bash
sudo certbot --nginx -d crm.ziakhatri.site
```

Certbot will:
1. Verify the domain via HTTP-01 challenge through the block created in Step 2.
2. Obtain a Let's Encrypt certificate.
3. Automatically rewrite the server block to redirect port 80 → 443 and configure HTTPS.

After Certbot completes, the site configuration will look roughly like:

```nginx
server {
    listen 443 ssl;
    server_name crm.ziakhatri.site;

    ssl_certificate     /etc/letsencrypt/live/crm.ziakhatri.site/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/crm.ziakhatri.site/privkey.pem;
    include             /etc/letsencrypt/options-ssl-nginx.conf;
    ssl_dhparam         /etc/letsencrypt/ssl-dhparams.pem;

    location / {
        proxy_pass         http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header   Host              $host;
        proxy_set_header   X-Real-IP         $remote_addr;
        proxy_set_header   X-Forwarded-For   $proxy_add_x_forwarded_for;
        proxy_set_header   X-Forwarded-Proto $scheme;
        proxy_read_timeout 60s;
    }
}

server {
    listen 80;
    server_name crm.ziakhatri.site;
    return 301 https://$host$request_uri;
}
```

---

## Step 5 — Final Verification

```bash
# Check certificate status
sudo certbot certificates

# Confirm Nginx is happy
sudo nginx -t && sudo systemctl reload nginx
```

Visit `https://crm.ziakhatri.site` — you should see the CRM login page over HTTPS.

---

## Notes

- Certbot auto-renewal is typically configured by the `certbot` package installer via a systemd timer or cron job. Confirm with `sudo systemctl status certbot.timer`.
- The Docker CRM stack's internal port (`80`) is never exposed directly to the internet — only `127.0.0.1:8080` is bound on the host.
- Do **not** change `DB_PORT`, `REDIS_PORT`, or any container-internal port. Only `APP_PORT` controls the host-binding.
