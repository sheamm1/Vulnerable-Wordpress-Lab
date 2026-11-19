# Vulnerable WordPress Lab

Playground for WordPress hacking and [wpscan](https://github.com/wpscanteam/wpscan) testing.

Source: **https://github.com/sheamm1/Vulnerable-Wordpress-Lab** (fork of
[vavkamil/dvwp](https://github.com/vavkamil/dvwp))

**DO NOT EXPOSE THIS TO THE INTERNET!**
This stack is intentionally vulnerable. It is for training/CTF purposes only.

## What's Inside

| Component  | Version              | Notes                                       |
|------------|----------------------|---------------------------------------------|
| WordPress  | 7.0.1                | Intentionally vulnerable core               |
| PHP        | 8.3                  | `display_errors = On`                       |
| MySQL      | 8.0                  | Health-checked                              |
| phpMyAdmin | 5.1.1                | Known-vulnerable version                    |
| WP-CLI     | 2.x                  | Runs setup automatically on `up`            |

The vulnerable plugins and themes are **vendored in this repository** (see
`plugins/` and `themes/`). `bin/download-plugins.sh` is only needed if you
want to (re)fetch them from wordpress.org at their pinned vulnerable versions.

## Installation

```bash
git clone https://github.com/sheamm1/Vulnerable-Wordpress-Lab.git
cd Vulnerable-Wordpress-Lab/
docker-compose up -d
```

> First pull of the images can take a while. If upgrading from an older
> Dockerfile-based setup, run `docker-compose down -v` once to wipe old
> named volumes.

The `wp-cli` service runs `bin/install-wp.sh` automatically: installs
WordPress, imports demo data, and activates the vulnerable plugins/themes.
The container exits when done — it is safe to re-run `docker-compose up -d`.

## Usage

```bash
docker-compose up -d       # start
docker-compose down        # stop
docker-compose logs -f     # logs
docker-compose exec wordpress bash  # shell
```

## Vulnerabilities

The lab ships with a collection of intentionally vulnerable plugins, themes,
and core versions for training purposes.

### WordPress Core

* **CVE-2026-60137** — SQL injection in `WP_Query` (`author__not_in`), CWE-89.
* **CVE-2026-63030** — REST API batch endpoint route confusion, CWE-436,
  **CISA KEV**, actively exploited.

Chained they give **unauthenticated RCE** on default installations.

### Plugins

| Plugin | Version | CVE | Impact |
|--------|---------|-----|--------|
| InfiniteWP Client | 1.9.4.4 | CVE-2020-8772 | Auth bypass → admin |
| WordPress File Upload | 4.12.2 | CVE-2020-10564 | Dir traversal → RCE |
| WP Advanced Search | 3.3.3 | — | Unauth SQLi → RCE |
| Social Warfare | 3.5.2 | CVE-2019-9978 | Unauth option update → RCE |
| Backup and Staging | 1.21.15 | CVE-2020-8771 | Auth bypass (currently disabled) |
| File Manager | 6.0 | CVE-2020-25213 | Unauth file upload → RCE |
| File Manager | 6.0 | CVE-2020-24312 | Exposed backup files |
| Duplicator | 1.3.26 | CVE-2020-11738 | Dir traversal → file read |
| Custom Content Type Manager | 0.9.8.1 | CVE-2015-3173 | Auth RCE via `eval()` |
| wpDiscuz | 7.0.4 | CVE-2020-24186 | Unauth file upload → RCE |
| Simple File List | 4.2.2 | CVE-2020-36847 | Unauth file upload → RCE |
| Email Subscribers | 4.2.2 | CVE-2019-19985 | Unauth data download |

### Themes

| Theme | Version | CVE | Impact |
|-------|---------|-----|--------|
| Shapely | 1.2.7 | CVE-2020-36708 | Unauth function injection → RCE |
| Sparkling | 2.4.8 | CVE-2020-36708 | Unauth function injection → RCE |

### phpMyAdmin 5.1.1

* **CVE-2022-0813** — Pre-auth authentication bypass
* **CVE-2022-23807, CVE-2022-23808** — Stored/reflected XSS

## Logs

```bash
bin/pull-logs.sh  # copies access.log + error.log from the WP container into ./logs/
```

## Publishing to Docker Hub

```bash
DOCKER_USER=<your-dockerhub-username> bin/publish.sh
```

Builds and pushes a ready-to-use image with plugins/themes baked in.

---

This lab is for educational and authorized training purposes only.
Do not use it against production systems without explicit permission.
