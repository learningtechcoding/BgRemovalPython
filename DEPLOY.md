# Free deployment: Oracle Cloud "Always Free" VM

Needs a credit card for signup identity checks. Oracle states Always Free resources aren't charged; check their current terms. Ask for **Ampere A1 (ARM)**: up to 4 cores / 24 GB RAM is the free allowance (verify in the console). The model needs at least 2-4 GB.
These steps are untested on a real server; fix as needed.

## 1. Create the VM
1. Sign up at cloud.oracle.com. Pick a home region (cannot change later).
2. Compute -> Instances -> Create. Image: **Ubuntu 22.04**. Shape: **VM.Standard.A1.Flex**, 2-4 OCPU, 12-24 GB RAM.
   If it says "out of capacity", retry later or try another availability domain.
3. Download the SSH key it offers. Note the **public IP**.
4. Networking -> your VCN -> Security List -> add **Ingress rules** for TCP ports **80** and **443** (source 0.0.0.0/0).

## 2. Free domain (needed for HTTPS)
1. Sign in at duckdns.org, create a name like `my-bgremover`, set its IP to the VM's public IP.
2. Your site will be `https://my-bgremover.duckdns.org`.

## 3. Prepare the server
```bash
ssh -i <key> ubuntu@<public-ip>

# Ubuntu images on Oracle block ports with iptables: open 80/443
sudo iptables -I INPUT 6 -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -p tcp --dport 443 -j ACCEPT
sudo netfilter-persistent save

# Install Docker
curl -fsSL https://get.docker.com | sudo sh
sudo usermod -aG docker $USER && exit   # then ssh in again
```

## 4. Deploy
```bash
git clone https://github.com/learningtechcoding/BgRemovalPython.git app && cd app
cp .env.deploy.example .env.deploy
nano .env.deploy        # set DOMAIN, BG_PYTHON_TOKEN (random), BG_CONTACT_EMAIL
docker compose --env-file .env.deploy run --rm --no-deps app php artisan key:generate --show
# paste the printed key into APP_KEY= in .env.deploy
docker compose --env-file .env.deploy up -d --build
docker compose logs -f app     # first build takes ~10-20 min (downloads the model)
```
Open `https://<your-domain>`. Caddy gets the HTTPS certificate automatically on first visit.

## Update later
```bash
cd app && git pull && docker compose --env-file .env.deploy up -d --build
```

## Notes
- Uploaded images and the database live inside the container and reset on rebuild; images are deleted after 1 hour anyway.
- Oracle may reclaim idle Always Free instances; keep occasional traffic or check their policy.
